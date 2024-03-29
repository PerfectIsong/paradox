<?php
include_once('../common/init.loader.php');

if (verifylog_sess('admin') == '') {
    die('o o p s !');
}

$_SESSION['redirto'] = redir_to($FORM['redir']);

if (isset($FORM['delId']) and $FORM['delId'] != "") {
    $hasdel = md5($FORM['delId'] . date("dH"));
    if ($FORM['hash'] == $hasdel) {

        $row = $db->getAllRecords(DB_TBLPREFIX . '_transactions', '*', ' AND txid="' . $FORM['delId'] . '"');
        $rowstr = array();
        foreach ($row as $value) {
            $rowstr = array_merge($rowstr, $value);
        }

        $wdrfee = floatval(get_optionvals($rowstr['txtoken'], 'WDRTXFEE'));

        $mbrstr = getmbrinfo($rowstr['txfromid']);
        $newamount = $mbrstr['ewallet'] + $rowstr['txamount'] + $wdrfee;
        adjusttrxwallet($mbrstr['ewallet'], $newamount, $rowstr['txfromid'], 'Withdrawal Reversal');
        $data = array(
            'ewallet' => $newamount,
        );
        $update = $db->update(DB_TBLPREFIX . '_mbrs', $data, array('id' => $mbrstr['id']));

        $db->delete(DB_TBLPREFIX . '_transactions', array('txid' => $FORM['delId']));

        if ($wdrfee > 0) {
            $db->doQueryStr("DELETE FROM " . DB_TBLPREFIX . "_transactions WHERE txtoken LIKE '%|WDRTXID:{$FORM['delId']}|%'");
        }

        $_SESSION['dotoaster'] = "toastr.success('Record deleted successfully!', 'Success');";
    } else {
        $_SESSION['dotoaster'] = "toastr.error('Record deleted failed!', 'Error');";
    }

    header('location: ' . $_SESSION['redirto']);
    $_SESSION['redirto'] = '';
    exit;
}

$editId = intval($FORM['editId']);

$avalwithdrawgatearr = array();
foreach ($avalwithdrawgate_array as $key => $value) {
    if ($key == 'manualpayipn') {
        $value = $payrow[$value];
    }
    $avalwithdrawgatearr[$key] = $value;
}
$txpaytype_menu = select_opt($avalwithdrawgatearr);

if (isset($editId) and $editId != "") {
    $row = $db->getAllRecords(DB_TBLPREFIX . '_transactions', '*', ' AND txid="' . $editId . '"');
    $rowstr = array();
    foreach ($row as $value) {
        $rowstr = array_merge($rowstr, $value);
    }

    $_SESSION['redirto'] = redir_to($FORM['redir']);

    $txpaytype_menu = select_opt($avalwithdrawgatearr, $rowstr['txpaytype']);

    $txstatusarr = array(0, 1, 2);
    $txstatus_cek = radiobox_opt($txstatusarr, $rowstr['txstatus']);

    $payto = getusernameid($rowstr['txfromid'], 'username');
}

if (isset($FORM['dosubmit']) and $FORM['dosubmit'] == '1') {
    extract($FORM);
    $editId = intval($editId);

    $txdatetm = date('Y-m-d H:i:s', time() + (3600 * $cfgrow['time_offset']));
    $data = array(
        'txtmstamp' => $txdatetm,
        'txpaytype' => $txpaytype,
        'txfromid' => $txfromid,
        'txtoid' => 0,
        'txamount' => $txamount,
        'txmemo' => $LANG['g_withdrawstr'],
        'txbatch' => $txbatch,
        'txstatus' => $txstatus,
        'txadminfo' => mystriptag($txadminfo),
    );

    $redirto = $_SESSION['redirto'];
    $_SESSION['redirto'] = '';

    if (isset($editId) and $editId > 0) {
        // if update transaction history
        $condition = ' AND txid = "' . $editId . '" ';
    } else {
        // if new transaction history exist, keep using old txbatch
        $condition = ' AND txbatch LIKE "' . $txbatch . '" AND txbatch != "" ';
    }
    $sql = $db->getRecFrmQry("SELECT * FROM " . DB_TBLPREFIX . "_transactions WHERE 1 " . $condition . "");
    if (count($sql) > 0) {
        if ($editId > 0) {
            $update = $db->update(DB_TBLPREFIX . '_transactions', $data, array('txid' => $editId));
            if ($update) {
                $_SESSION['dotoaster'] = "toastr.success('Record updated successfully!', 'Success');";
            } else {
                $_SESSION['dotoaster'] = "toastr.warning('You did not change anything!', 'Info');";
            }
        } else {
            // do nothing
            $_SESSION['dotoaster'] = "toastr.warning('Record not added <strong>Withdrawal request exist!</strong>', 'Warning');";
        }
    } else {

        // add withdraw request
        $data_add = array(
            'txdatetm' => $txdatetm,
            'txtoken' => '|WIDR:OUT|',
        );
        $data = array_merge($data, $data_add);

        $insert = $db->insert(DB_TBLPREFIX . '_transactions', $data);

        // deduct wallet
        $mbrstr = getmbrinfo($txfromid);
        $ewallet = $mbrstr['ewallet'] - $txamount;
        $data = array(
            'ewallet' => $ewallet,
        );
        $update = $db->update(DB_TBLPREFIX . '_mbrs', $data, array('id' => $mbrstr['id']));

        if ($insert) {
            $_SESSION['dotoaster'] = "toastr.success('Record added successfully!', 'Success');";
        } else {
            $_SESSION['dotoaster'] = "toastr.error('Record not added <strong>Please try again!</strong>', 'Warning');";
        }
    }
    header('location: ' . $redirto);
    exit;
}

$withdrawstatusinfo = sprintf($LANG['g_withdrawstatusinfo'], $LANG['g_withdrawiswait'], $LANG['g_withdrawislook'], $LANG['g_withdrawispaid']);
?>

<div class="row">
    <div class="col-md-12">

        <p class="text-primary">Fields with <span class="text-danger">*</span> are mandatory!</p>

        <form method="post" action="dowithdraw.php">

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Send Payment To ID <span class="text-danger">*</span></label>
                    <input type="number" min="0" name="txfromid" id="txfromid" class="form-control" value="<?php echo isset($rowstr['txfromid']) ? $rowstr['txfromid'] : ''; ?>" placeholder="Enter payee ID" onBlur="checkMember('id2i', this.value, '2')" required>
                </div>
                <div class="form-group col-md-8">
                    <label>To Member</label>
                    <div id="resultGetMbr2"><?php echo isset($payto) ? "<span class='text-primary'><strong>{$payto}</strong></span>" : '?'; ?></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Amount <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text"><i class="fa fa-fw fa-money-bill-wave"></i></div>
                        </div>
                        <input type="number" min="0" step="0.01" name="txamount" id="txamount" class="form-control" value="<?php echo isset($rowstr['txamount']) ? $rowstr['txamount'] : ''; ?>" placeholder="Payment amount" required>
                    </div>
                </div>
                <div class="form-group col-md-8">
                    <label><?php echo myvalidate($LANG['g_transactionid']); ?></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text"><i class="fa fa-fw fa-receipt"></i></div>
                        </div>
                        <input type="text" name="txbatch" id="txbatch" class="form-control" value="<?php echo isset($rowstr['txbatch']) ? $rowstr['txbatch'] : ''; ?>" placeholder="Enter transaction id">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Payment Type</label>
                    <select name="txpaytype" id="txpaytype" class="form-control select1" required>
                        <?php echo myvalidate($txpaytype_menu); ?>
                    </select>
                </div>
                <div class="form-group col-md-8">
                    <label for="selectgroup-pills">Status <a class="helphint"
                                                             data-target="#helphint"
                                                             data-toggle="collapse">
                            <i class="far fa-fw fa-question-circle"></i>
                        </a></label>
                    <div id="helphint" class="helphint-content collapse">
                        <?php echo myvalidate($withdrawstatusinfo); ?>
                    </div>

                    <div class="selectgroup selectgroup-pills">
                        <label class="selectgroup-item">
                            <input type="radio" name="txstatus" value="0" class="selectgroup-input"<?php echo myvalidate($txstatus_cek[0]); ?>>
                            <span class="selectgroup-button selectgroup-button-icon"><i class="fas fa-fw fa-question-circle"></i> <?php echo myvalidate($LANG['g_withdrawiswait']); ?></span>
                        </label>
                        <label class="selectgroup-item">
                            <input type="radio" name="txstatus" value="2" class="selectgroup-input"<?php echo myvalidate($txstatus_cek[2]); ?>>
                            <span class="selectgroup-button selectgroup-button-icon"><i class="fas fa-fw fa-user-circle"></i> <?php echo myvalidate($LANG['g_withdrawislook']); ?></span>
                        </label>
                        <label class="selectgroup-item">
                            <input type="radio" name="txstatus" value="1" class="selectgroup-input"<?php echo myvalidate($txstatus_cek[1]); ?>>
                            <span class="selectgroup-button selectgroup-button-icon"><i class="fas fa-fw fa-check-circle"></i> <?php echo myvalidate($LANG['g_withdrawispaid']); ?></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label><?php echo myvalidate($LANG['g_description']); ?> <span class="text-danger">*</span></label>
                    <input type="text" name="txmemo" id="txmemo" class="form-control" value="<?php echo isset($rowstr['txmemo']) ? $rowstr['txmemo'] : ''; ?>" placeholder="Transaction details" required>
                </div>
                <div class="form-group col-md-8">
                    <label>Note (Admin Only)</label>
                    <textarea class="form-control" name="txadminfo" id="txadminfo" placeholder="Transaction note, available for Admin only"><?php echo isset($rowstr['txadminfo']) ? $rowstr['txadminfo'] : ''; ?></textarea>
                </div>
            </div>

            <div class="text-md-right">
                <a href="javascript:;" class="btn btn-secondary" data-dismiss="modal"><i class="far fa-fw fa-times-circle"></i> Cancel</a>
                <button type="submit" name="submit" value="submit" id="submit" class="btn btn-primary">
                    <i class="fa fa-fw fa-plus-circle"></i> Submit
                </button>
                <input type="hidden" name="editId" value="<?php echo myvalidate($editId); ?>">
                <input type="hidden" name="dosubmit" value="1">
            </div>

        </form>

    </div>

</div>