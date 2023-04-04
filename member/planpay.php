<?php
if (!defined('OK_LOADME')) {
    die('o o p s !');
}

// check if already registered to the payplan
if ($mbrstr['idmbr'] != $mbrstr['id']) {
    // not registered
    redirpageto('index.php?hal=planreg');
    exit;
}

$bpprow = ppdbplan($mbrstr['mppid']);
$pgdatatokenarr = get_optionvals($payrow['pgdatatoken']);

$pgdatatoken = $mbrstr['pgdatatoken'];
$pgmbrtokenarr = get_optionvals($pgdatatoken);

$perfectmoneycfg = get_optarr($pgdatatokenarr['perfectmoneycfg']);
$mbrperfectmoneycfg = get_optarr($pgmbrtokenarr['perfectmoneycfg']);

$paystackcfg = get_optarr($pgdatatokenarr['paystackcfg']);
$mbrpaystackcfg = get_optarr($pgmbrtokenarr['paystackcfg']);

// get transaction details
$unpaidtxid = get_unpaidtxid($mbrstr);
if ($unpaidtxid > 0) {
    $txidstr = $unpaidtxid;
    $payforstr = 'RENEWAL';
} else {
    $condition = ' AND txtoken LIKE "%|REG:' . $mbrstr['mpid'] . '|%" ';
    $row = $db->getAllRecords(DB_TBLPREFIX . '_transactions', '*', $condition);
    $trxstr = array();
    foreach ($row as $value) {
        $trxstr = array_merge($trxstr, $value);
    }
    $txidstr = $trxstr['txid'];
    $payforstr = 'REGISTERED';

    if ($trxstr['txstatus'] == 1) {
        redirpageto('index.php?hal=dashboard');
        exit;
    }
}
// -----

$txmpid = $txidstr . '-' . $mbrstr['mpid'];
$regfee = $totcoinpayments = $totperfectmoney = $totpaypal = $totpaystack = $totmanualpay = $tottestpay = $mbrstr['reg_fee'];

$paytoken = $payrow['paytoken'];
$isppsandbox = get_optionvals($paytoken, 'paypalsbox');

$ispayg = 0;
$paygatearr = array('coinpayments', 'perfectmoney', 'paypal', 'paystack', 'manualpay', 'testpay');
foreach ($paygatearr as $key => $value) {
    if ($payrow[$value . 'on'] == 1) {
        if ($payrow[$value . 'fee'] > 0) {
            ${'fee' . $value} = getamount($payrow[$value . 'fee'], $regfee);
            ${'tot' . $value} = $regfee + ${'fee' . $value};
        } else {
            ${'fee' . $value} = 0;
        }
        $ispayg++;
    }
    if ($pgdatatokenarr[$value . 'on'] == 1) {
        $valdatatoken = get_optarr($pgdatatokenarr[$value . 'cfg']);
        if ($valdatatoken[$value . 'fee'] > 0) {
            ${'fee' . $value} = getamount($valdatatoken[$value . 'fee'], $regfee);
            ${'tot' . $value} = $regfee + ${'fee' . $value};
        } else {
            ${'fee' . $value} = 0;
        }
        $ispayg++;
    }
}

if ($ispayg <= 1) {
    $colmdclass = "col-md-12";
} elseif ($ispayg <= 2) {
    $colmdclass = "col-md-6";
} else {
    $colmdclass = "col-md-4";
}

$tagsarr = array("[[currencysym]]" => $bpprow['currencysym'], "[[currencycode]]" => $bpprow['currencycode'], "[[feeamount]]" => $feemanualpay, "[[amount]]" => $regfee, "[[totamount]]" => $totmanualpay, "[[payplan]]" => $bpprow['ppname']);
$manualpayipn = base64_decode($payrow['manualpayipn']);
$manualpayipn = strtr($manualpayipn, $tagsarr);
$manualpayipn64 = base64_encode($manualpayipn . '<button type="button" class="btn btn-warning btn-lg mt-4" onclick="location.href = \'index.php?hal=feedback&isconfirm=' . base64_encode($txmpid) . '\'">Confirm Payment</button>');
?>

<div class="section-header">
    <h1><i class="fa fa-fw fa-money-check"></i> <?php echo myvalidate($LANG['m_planpay']); ?></h1>
</div>

<div class="section-body">
    <div class="row">
        <div class="col-md-12">
            <article class="article article-style-b">
                <div class="article-header">
                    <div class="article-image" data-background="<?php echo myvalidate($planlogo); ?>">
                    </div>
                    <div class="article-badge">
                        <span class="article-badge-item bg-danger">
                            <?php echo myvalidate($bpprow['currencysym'] . $regfee . ' ' . $bpprow['currencycode']); ?>
                        </span>
                        <?php
                        if ($sprstr['mpstatus'] == 1) {
                            ?>
                            <span class="article-badge-item bg-warning">
                                Sponsored by <?php echo myvalidate($sprstr['username']); ?>
                            </span>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <div class="article-details">
                    <div class="article-title">
                        <h4><?php echo myvalidate($bpprow['ppname']); ?></h4>
                    </div>
                    <p><?php echo myvalidate($bpprow['planinfo']); ?></p>
                    <div class="article-cta">
                        <span class="badge badge-secondary">
                            <?php echo myvalidate($payforstr); ?>
                        </span>
                        <span class="badge badge-danger">
                            UNPAID
                        </span>
                    </div>
                </div>
            </article>

        </div>
    </div>

    <h2 class="section-title"><?php echo myvalidate($LANG['m_payoption']); ?></h2>
    <p class="section-lead"><?php echo myvalidate($LANG['m_payinfo']); ?></p>

    <div class="row">
        <?php
        if ($payrow['coinpaymentson'] == 1) {
            ?>
            <div class="<?php echo myvalidate($colmdclass); ?>">
                <div class="card card-primary">
                    <div class="card-body text-center">
                        <?php echo myvalidate($avalpaygateicon_array['coinpaymentsmercid']); ?>
                        <h4>Coinpayments</h4>
                        <div class="mt-4">Amount: <?php echo myvalidate($bpprow['currencysym'] . $regfee); ?></div>
                        <div><code>Service Fee: <?php echo myvalidate($bpprow['currencysym'] . $feecoinpayments); ?></code></div>
                        <h6>Total: <?php echo myvalidate($bpprow['currencysym'] . $totcoinpayments . ' ' . $bpprow['currencycode']); ?></h6>
                        <form method="post" action="https://www.coinpayments.net/index.php" id="dopayform">
                            <input type="hidden" name="cmd" value="_pay_simple"> <!-- or _pay -->
                            <input type="hidden" name="reset" value="1">
                            <input type="hidden" name="merchant" value="<?php echo myvalidate(base64_decode($payrow['coinpaymentsmercid'])); ?>">
                            <input type="hidden" name="item_name" value="<?php echo myvalidate($bpprow['ppname']); ?>">
                            <input type="hidden" name="item_number" value="<?php echo myvalidate($mbrstr['username']); ?>">
                            <input type="hidden" name="invoice" value="<?php echo myvalidate($txmpid); ?>">
                            <input type="hidden" name="currency" value="<?php echo myvalidate($bpprow['currencycode']); ?>">
                            <input type="hidden" name="amountf" value="<?php echo myvalidate($totcoinpayments); ?>">
                            <input type="hidden" name="quantity" value="1">
                            <input type="hidden" name="allow_quantity" value="1">
                            <input type="hidden" name="want_shipping" value="0">
                            <input type="hidden" name="success_url" value="<?php echo myvalidate($cfgrow['site_url']) . '/' . MBRFOLDER_NAME . '/ipnhub.php?hal=dashboard'; ?>">
                            <input type="hidden" name="cancel_url" value="<?php echo myvalidate($cfgrow['site_url']) . '/' . MBRFOLDER_NAME . '/index.php?hal=planpay&act=cancelpay'; ?>">
                            <input type="hidden" name="ipn_url" value="<?php echo myvalidate($cfgrow['site_url']) . '/common/sandbox.php'; ?>">
                            <input type="hidden" name="allow_extra" value="1">

                            <button type="submit" name="dopay" value="1" id="dopay" class="btn btn-primary btn-lg mt-4">
                                Make Payment
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php
        }
        if ($pgdatatokenarr['perfectmoneyon'] == 1) {
            ?>
            <div class="<?php echo myvalidate($colmdclass); ?>">
                <div class="card card-primary">
                    <div class="card-body text-center">
                        <?php echo myvalidate($avalpaygateicon_array['perfectmoneyacc']); ?>
                        <h4>Perfectmoney</h4>
                        <div class="mt-4">Amount: <?php echo myvalidate($bpprow['currencysym'] . $regfee); ?></div>
                        <div><code>Service Fee: <?php echo myvalidate($bpprow['currencysym'] . $feeperfectmoney); ?></code></div>
                        <h6>Total: <?php echo myvalidate($bpprow['currencysym'] . $totperfectmoney . ' ' . $bpprow['currencycode']); ?></h6>

                        <form method="post" action="https://perfectmoney.is/api/step1.asp" id="dopayform">
                            <p>
                                <input type="hidden" name="PAYEE_ACCOUNT" value="<?php echo myvalidate($perfectmoneycfg['perfectmoneyacc']); ?>">
                                <input type="hidden" name="PAYEE_NAME" value="<?php echo myvalidate($perfectmoneycfg['perfectmoneyname']); ?>">
                                <input type="hidden" name="PAYMENT_AMOUNT" value="<?php echo myvalidate($totperfectmoney); ?>">
                                <input type="hidden" name="PAYMENT_UNITS" value="<?php echo myvalidate($bpprow['currencycode']); ?>">
                                <input type="hidden" name="STATUS_URL"
                                       value="<?php echo myvalidate($cfgrow['site_url']) . '/common/perfectmoney.ipv.php'; ?>">
                                <input type="hidden" name="PAYMENT_URL"
                                       value="<?php echo myvalidate($cfgrow['site_url']) . '/' . MBRFOLDER_NAME . '/ipnhub.php?hal=dashboard'; ?>">
                                <input type="hidden" name="NOPAYMENT_URL"
                                       value="<?php echo myvalidate($cfgrow['site_url']) . '/' . MBRFOLDER_NAME . '/index.php?hal=planpay&act=cancelpay'; ?>">
                                <input type="hidden" name="PAYMENT_ID" value="<?php echo myvalidate($txmpid); ?>">
                                <!--input type="hidden" name="BAGGAGE_FIELDS" value="PAYMENT_ID"-->
                                <button type="submit" name="dopay" value="1" id="dopay" class="btn btn-primary btn-lg mt-4">
                                    Make Payment
                                </button>
                            </p>
                        </form>

                    </div>
                </div>
            </div>
            <?php
        }
        if ($payrow['paypalon'] == 1) {

            $posturl = ($isppsandbox == 1) ? "https://ipnpb.sandbox.paypal.com/cgi-bin/webscr" : "https://ipnpb.paypal.com/cgi-bin/webscr";
            ?>
            <div class="<?php echo myvalidate($colmdclass); ?>">
                <div class="card card-primary">
                    <div class="card-body text-center">
                        <?php echo myvalidate($avalpaygateicon_array['paypalacc']); ?>
                        <h4>Paypal</h4>
                        <div class="mt-4">Amount: <?php echo myvalidate($bpprow['currencysym'] . $regfee); ?></div>
                        <div><code>Service Fee: <?php echo myvalidate($bpprow['currencysym'] . $feepaypal); ?></code></div>
                        <h6>Total: <?php echo myvalidate($bpprow['currencysym'] . $totpaypal . ' ' . $bpprow['currencycode']); ?></h6>
                        <form method="post" action="<?php echo myvalidate($posturl); ?>" id="dopayform">
                            <input type="hidden" name="cmd" value="_xclick">
                            <input type="hidden" name="amount" value="<?php echo myvalidate($totpaypal); ?>">
                            <input type="hidden" name="business" value="<?php echo myvalidate(base64_decode($payrow['paypalacc'])); ?>">
                            <input type="hidden" name="notify_url" value="<?php echo myvalidate($cfgrow['site_url']) . '/common/sandbox.php'; ?>">
                            <input type="hidden" name="return" value="<?php echo myvalidate($cfgrow['site_url']) . '/' . MBRFOLDER_NAME . '/ipnhub.php?hal=dashboard'; ?>">
                            <input type="hidden" name="cancel_return" value="<?php echo myvalidate($cfgrow['site_url']) . '/' . MBRFOLDER_NAME . '/index.php?hal=planpay&act=cancelpay'; ?>">
                            <input type="hidden" name="currency_code" value="<?php echo myvalidate($bpprow['currencycode']); ?>">
                            <input type="hidden" name="item_name" value="<?php echo myvalidate($bpprow['ppname']); ?>">
                            <input type="hidden" name="quantity" value="1">
                            <input type="hidden" name="rm" value="2">
                            <input type="hidden" name="no_shipping" value="1">
                            <input type="hidden" name="no_note" value="1">
                            <input type="hidden" name="custom" value="<?php echo myvalidate($txmpid); ?>">

                            <button type="submit" name="dopay" value="1" id="dopay" class="btn btn-primary btn-lg mt-4">
                                Make Payment
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php
        }
        if ($pgdatatokenarr['paystackon'] == 1) {
            $paystackpin64 = base64_encode($paystackcfg['paystackpin']);
            $paystackhash = md5($paystackpin64 . $txmpid . $mbrstr['reg_fee']);
            ?>
            <div class="<?php echo myvalidate($colmdclass); ?>">
                <div class="card card-primary">
                    <div class="card-body text-center">
                        <?php echo myvalidate($avalpaygateicon_array['paystackpub']); ?>
                        <h4>Paystack</h4>
                        <div class="mt-4">Amount: <?php echo myvalidate($bpprow['currencysym'] . $regfee); ?></div>
                        <div><code>Service Fee: <?php echo myvalidate($bpprow['currencysym'] . $feepaystack); ?></code></div>
                        <h6>Total: <?php echo myvalidate($bpprow['currencysym'] . $totpaystack . ' ' . $bpprow['currencycode']); ?></h6>
                        <button type="button" class="btn btn-primary btn-lg mt-4" onclick="payWithPaystack()">
                            Make Payment
                        </button>
                    </div>
                </div>
            </div>
            <script src="https://js.paystack.co/v1/inline.js"></script>
            <script>
                            function payWithPaystack() {

                                var handler = PaystackPop.setup({
                                    key: '<?php echo myvalidate($paystackcfg['paystackpub']); ?>', //put your public key here
                                    email: '<?php echo myvalidate($mbrstr['email']); ?>', //put your customer's email here
                                    amount: <?php echo myvalidate(100 * $totpaystack); ?>, //amount the customer is supposed to pay
                                    currency: '<?php echo myvalidate($bpprow['currencycode']); ?>', //Use GHS for Ghana Cedis or USD for US Dollars
                                    metadata: {
                                        custom_fields: [
                                            {
                                                display_name: "Mobile Number",
                                                variable_name: "mobile_number",
                                                value: "<?php echo myvalidate($mbrpaystackcfg['paystackpub']); ?>" //customer's mobile number
                                            }
                                        ]
                                    },
                                    callback: function (response) {
                                        //after the transaction have been completed
                                        //make post call  to the server with to verify payment
                                        //using transaction reference as post data
                                        $.post("../common/paystack.ipv.php", {reference: response.reference, refpin: '<?php echo myvalidate($paystackpin64); ?>', txmpid: '<?php echo myvalidate($txmpid); ?>', reg_fee: '<?php echo myvalidate($mbrstr['reg_fee']); ?>', reg_hash: '<?php echo myvalidate($paystackhash); ?>'}, function (status) {
                                            if (status == "success")
                                                //successful transaction
                                                alert('Transaction was successful');
                                            window.location.href = 'index.php?hal=dashboard';
                                            else
                                                    //transaction failed
                                                    alert(response);
                                        });
                                    },
                                    onClose: function () {
                                        //when the user close the payment modal
                                        alert('Transaction interrupted and cancelled!');
                                    }
                                });
                                handler.openIframe(); //open the paystack's payment modal
                            }
            </script>
            <?php
        }
        if ($payrow['manualpayon'] == 1) {
            ?>
            <div class="<?php echo myvalidate($colmdclass); ?>">
                <div class="card card-primary">
                    <div class="card-body text-center">
                        <?php echo myvalidate($avalpaygateicon_array['manualpayipn']); ?>
                        <h4><?php echo myvalidate($payrow['manualpayname']); ?></h4>
                        <div class="mt-4">Amount: <?php echo myvalidate($bpprow['currencysym'] . $regfee); ?></div>
                        <div><code>Service Fee: <?php echo myvalidate($bpprow['currencysym'] . $feemanualpay); ?></code></div>
                        <h6>Total: <?php echo myvalidate($bpprow['currencysym'] . $totmanualpay . ' ' . $bpprow['currencycode']); ?></h6>
                        <button type="button" class="openPopup btn btn-primary btn-lg mt-4" data-encbase64="<?php echo myvalidate($manualpayipn64); ?>" data-poptitle="<i class='fa fa-fw fa-handshake'></i> <?php echo myvalidate($payrow['manualpayname']); ?>">
                            Make Payment
                        </button>
                    </div>
                </div>
            </div>
            <?php
        }
        if ($payrow['testpayon'] == 1) {
            $paybatch = strtoupper(date("DmdH-is")) . $mbrstr['mpid'];
            ?>
            <div class="<?php echo myvalidate($colmdclass); ?>">
                <div class="card card-danger">
                    <div class="card-body text-center">
                        <i class="fa fa-cog fa-fw"></i>
                        <h4><?php echo myvalidate($payrow['testpaylabel']); ?></h4>
                        <div class="mt-4">Amount: <?php echo myvalidate($bpprow['currencysym'] . $regfee); ?></div>
                        <div><code>Service Fee: <?php echo myvalidate($bpprow['currencysym'] . $feetestpay); ?></code></div>
                        <h6>Total: <?php echo myvalidate($bpprow['currencysym'] . $tottestpay . ' ' . $bpprow['currencycode']); ?></h6>
                        <div class="mt-4"><?php echo myvalidate($LANG['m_testpayinfo']); ?></div>
                        <form method="post" action="../common/sandbox.php" id="dopayform">
                            <input type="hidden" name="sb_type" value="payreg">
                            <input type="hidden" name="sb_txmpid" value="<?php echo myvalidate($txmpid); ?>">
                            <input type="hidden" name="sb_amount" value="<?php echo myvalidate($tottestpay); ?>">
                            <input type="hidden" name="sb_batch" value="<?php echo myvalidate($paybatch); ?>">
                            <input type="hidden" name="sb_label" value="<?php echo myvalidate($payrow['testpaylabel']); ?>">
                            <input type="hidden" name="sb_success" value="<?php echo myvalidate($cfgrow['site_url']) . '/' . MBRFOLDER_NAME . '/ipnhub.php?hal=dashboard'; ?>">
                            <button type="submit" name="dopay" value="1" id="dopay" class="btn btn-danger btn-lg mt-4">
                                Make Payment
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>
