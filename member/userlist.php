<?php
if (!defined('OK_LOADME')) {
    die('o o p s !');
}

if ($FORM['dohal'] == 'clear') {
    $_SESSION['filteruid'] = '';
    redirpageto('index.php?hal=userlist');
    exit;
}
if ($FORM['dohal'] == 'filter' && $FORM['dompid']) {
    $_SESSION['filteruid'] = $FORM['dompid'];
}

$clistnow = ($_SESSION['clisti'] > 0) ? $_SESSION['clisti'] : 1;
$clistid = ($_SESSION['clistview'] > 0) ? $_SESSION['clistview'] : $mbrstr['mpid'];

if ($_SESSION['filteruid']) {
    $filteruid = intval($_SESSION['filteruid']);
    $filterusrstr = getmbrinfo('', '', $filteruid);
    $btnclorclear = 'btn-danger';
    $clearfilterusrstr = " filter for member ({$filterusrstr['username']})";
} else {
    $filteruid = intval($clistid);
    $btnclorclear = 'btn-warning';
    $clearfilterusrstr = "";
}

if ($_SESSION['filteruid'] && strpos($filterusrstr['sprlist'], ":{$clistid}|") === false) {
    $_SESSION['filteruid'] = "";
    redirpageto('index.php?hal=userlist');
    exit;
} else {
    $condition = " AND sprlist LIKE '%:{$filteruid}|%' ";
}

if (isset($FORM['name']) and $FORM['name'] != "") {
    $condition .= ' AND (firstname LIKE "%' . $FORM['name'] . '%" OR lastname LIKE "%' . $FORM['name'] . '%") ';
}
if (isset($FORM['username']) and $FORM['username'] != "") {
    $condition .= ' AND username LIKE "%' . $FORM['username'] . '%" ';
}
if (isset($FORM['email']) and $FORM['email'] != "") {
    $condition .= ' AND email LIKE "%' . $FORM['email'] . '%" ';
}

if (isset($FORM['clist']) && intval($FORM['clist']) > 0) {
    $_SESSION['clisti'] = intval($FORM['clisti']);
    $_SESSION['clistview'] = intval($FORM['clist']);
    redirpageto('index.php?hal=userlist');
    exit;
}

if (isset($FORM['vlist']) && $FORM['vlist'] != '') {
    $_SESSION['vlistview'] = ($FORM['vlist'] == 'all') ? '' : $FORM['vlist'];
    redirpageto('index.php?hal=userlist');
    exit;
}

$optviewlevel = $optviewcycle = $viewlnow = $addviewsql = '';
if ($_SESSION['vlistview'] != '') {
    if ($_SESSION['vlistview'] == 'ref') {
        $addviewsql = " AND idref = '{$filteruid}'";
        $viewlnow = 'Personal';
    } else {
        for ($i = 1; $i <= $bpprow['maxdepth']; $i++) {
            if ($_SESSION['vlistview'] == 'l' . $i) {
                $addviewsql = " AND sprlist LIKE '%|{$i}:{$filteruid}|%'";
                $viewlnow = 'Level ' . $i;
                break;
            }
        }
    }
}

$icyc = 0;
$userData = $db->getRecFrmQry("SELECT * FROM " . DB_TBLPREFIX . "_mbrplans WHERE 1 AND idmbr = '{$mbrstr['id']}' ORDER BY mpid");
if (count($userData) > 0) {
    foreach ($userData as $val) {
        $icyc++;
        $ismarked = ($_SESSION['clisti'] == $icyc) ? ' &#10003;' : '';
        $optviewcycle .= '<a class="dropdown-item" href="index.php?hal=userlist&clist=' . $val['mpid'] . '&clisti=' . $icyc . '">' . strtoupper($mbrstr['username']) . ' Cycle ' . $icyc . $ismarked . '</a>';
    }
}

for ($i = 1; $i <= $bpprow['maxdepth']; $i++) {
    $ismarked = ($_SESSION['vlistview'] == 'l' . $i) ? ' &#10003;' : '';
    $optviewlevel .= '<a class="dropdown-item" href="index.php?hal=userlist&vlist=l' . $i . '">Referral on Level ' . $i . $ismarked . '</a>';
}

$tblshort_arr = array("in_date", "username", "email");
$tblshort = dborder_arr($tblshort_arr, $FORM['_stbel'], $FORM['_stype']);
if ($FORM['_stbel'] != '' && (in_array($FORM['_stbel'], $tblshort_arr))) {
    $sqlshort = ($FORM['_stype'] == 'up') ? " ORDER BY {$FORM['_stbel']} DESC " : " ORDER BY {$FORM['_stbel']} ASC ";
} else {
    $sqlshort = " ORDER BY id DESC ";
}

//Main queries
$sql = $db->getRecFrmQry("SELECT * FROM " . DB_TBLPREFIX . "_mbrs LEFT JOIN " . DB_TBLPREFIX . "_mbrplans ON id = idmbr WHERE 1 " . $condition . $addviewsql . "");
$pages->items_total = count($sql);
$pages->mid_range = 3;
$pages->paginate();

$userData = $db->getRecFrmQry("SELECT * FROM " . DB_TBLPREFIX . "_mbrs AS mbr LEFT JOIN " . DB_TBLPREFIX . "_mbrplans AS plan ON id = idmbr LEFT JOIN " . DB_TBLPREFIX . "_sessions AS ses ON mbr.id = ses.sesidmbr WHERE 1 " . $condition . $addviewsql . ' GROUP BY id ' . $sqlshort . $pages->limit . "");
?>

<div class="section-header">
    <h1><i class="fa fa-fw fa-users"></i> <?php echo myvalidate($LANG['g_referrallist']); ?></h1>
</div>

<div class="section-body">

    <form method="get">
        <div class="card card-primary">
            <div class="card-header">
                <h4><i class="fa fa-fw fa-search"></i> <?php echo myvalidate($LANG['g_findreferral']); ?></h4>
                <div class="card-header-action">
                    <?php
                    if ($icyc > 1) {
                        ?>
                        <div class="dropdown">
                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                                Cycle <span class="badge badge-light"><?php echo myvalidate($clistnow); ?></span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <?php echo myvalidate($optviewcycle); ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="dropdown">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                            View List <span class="badge badge-light"><?php echo myvalidate($viewlnow); ?></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="index.php?hal=userlist&vlist=all">All Referral</a>
                            <a class="dropdown-item" href="index.php?hal=userlist&vlist=ref">Personal Referral</a>
                            <div class="dropdown-divider"></div>
                            <?php echo myvalidate($optviewlevel); ?>
                        </div>
                    </div>
                </div>

            </div>
            <div class="card-body">
                <div class="row">

                    <div class="col-sm-4">
                        <div class="form-group">
                            <label><?php echo myvalidate($LANG['g_firstname']); ?></label>
                            <input type="text" name="name" id="name" class="form-control" value="<?php echo isset($FORM['name']) ? $FORM['name'] : '' ?>" placeholder="Enter referral name">
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
                            <label><?php echo myvalidate($LANG['g_username']); ?></label>
                            <input type="text" name="username" id="username" class="form-control" value="<?php echo isset($FORM['username']) ? $FORM['username'] : '' ?>" placeholder="Enter referral username">
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
                            <label><?php echo myvalidate($LANG['g_email']); ?></label>
                            <input type="email" name="email" id="useremail" class="form-control" value="<?php echo isset($FORM['email']) ? $FORM['email'] : '' ?>" placeholder="Enter referral email">
                        </div>
                    </div>

                </div>
            </div>
            <div class="card-footer bg-whitesmoke">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="float-md-right">
                            <a href="index.php?hal=userlist&dohal=clear" class="btn <?php echo myvalidate($btnclorclear); ?>"><i class="fa fa-fw fa-redo"></i> Clear<?php echo myvalidate($clearfilterusrstr); ?></a>
                            <button type="submit" name="submit" value="search" id="submit" class="btn btn-primary"><i class="fa fa-fw fa-search"></i> Search</button>
                        </div>
                        <div class="d-block d-sm-none">
                            &nbsp;
                        </div>
                        <div>
                            <?php
                            if ($cfgtoken['isregbymbr'] == '1') {
                                ?>
                                <a href="javascript:;" data-href="adduser.php?redir=userlist&addref=<?php echo myvalidate($mbrstr['username']); ?>" data-poptitle="<i class='fa fa-fw fa-plus-circle'></i> <?php echo myvalidate($LANG['g_addreferral']); ?>" class="openPopup btn btn-dark"><i class="fa fa-fw fa-user-plus"></i> <?php echo myvalidate($LANG['g_addreferral']); ?></a>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
        <input type="hidden" name="hal" value="userlist">
    </form>

    <hr>

    <div class="clearfix"></div>

    <div class="row marginTop">
        <div class="col-sm-12 paddingLeft pagerfwt">
            <?php if ($pages->items_total > 0) { ?>
                <div class="row">
                    <div class="col-md-7">
                        <?php echo myvalidate($pages->display_pages()); ?>
                    </div>
                    <div class="col-md-5 text-right">
                        <span class="d-none d-md-block">
                            <?php echo myvalidate($pages->display_items_per_page()); ?>
                            <?php echo myvalidate($pages->display_jump_menu()); ?>
                            <?php echo myvalidate($pages->items_total()); ?>
                        </span>
                    </div>
                <?php } ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="clearfix"></div>
    </div>

    <div class="clearfix"></div>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col" nowrap><?php echo myvalidate($tblshort['in_date']); ?>Date</th>
                    <th scope="col" nowrap><?php echo myvalidate($tblshort['username']); ?>Username</th>
                    <th scope="col" nowrap><?php echo myvalidate($tblshort['email']); ?>Email</th>
                    <th scope="col" class="text-center"></th>
                    <th scope="col" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (count($userData) > 0) {
                    $pgnow = ($FORM['page'] > 1) ? $FORM['page'] - 1 : 0;
                    $s = ($FORM['ipp'] > 0) ? $pgnow * $FORM['ipp'] : $pgnow * $cfgrow['maxpage'];

                    $lastses = time() - (5 * 3600 * ($cfgrow['time_offset'] + 1));
                    foreach ($userData as $val) {
                        $s++;
                        $usernamestr = ($val['idref'] == $filteruid) ? "<strong>{$val['username']}</strong>" : $val['username'];

                        $overview = "<label>Info</label><div>" . $val['adminfo'] . "</div>";
                        $mbrimgval = ($val['mbr_image']) ? $val['mbr_image'] : $cfgrow['mbr_defaultimage'];
                        $mbrimgvalstr = "<img alt='?' src='{$mbrimgval}'class='img-responsive float-left mr-3 rounded-circle' width='96'>";

                        $valmail = maskmail($val['email']);
                        $stremail = (strlen($valmail) > 24) ? substr($valmail, 0, 21) . '...' : $valmail;

                        $isuseron = ($val['sesid'] > 0 && $val['sestime'] > $lastses) ? "<span class='beep'></span>" : '';
                        ?>
                        <tr>

                            <th scope="row"><?php echo myvalidate($s); ?></th>
                            <td data-toggle="tooltip" title="<?php echo myvalidate($val['in_date']); ?>" nowrap><?php echo formatdate($val['in_date']); ?></td>
                            <td data-toggle='tooltip' title='<?php echo myvalidate($val['firstname']) . ' ' . myvalidate($val['lastname']); ?>'><?php echo myvalidate($isuseron . $usernamestr); ?></td>
                            <td data-toggle='tooltip' title='<?php echo myvalidate($valmail); ?>'><?php echo myvalidate($stremail); ?></td>
                            <td align="center" nowrap><?php echo badgembrplanstatus($val['mbrstatus'], $val['mpstatus']); ?></td>
                            <td align="center" nowrap>
                                <a href="javascript:;"
                                   class="btn btn-sm btn-secondary"
                                   data-html="true"
                                   data-toggle="popover"
                                   data-trigger="hover"
                                   data-placement="left" 
                                   title="<?php echo strtoupper($val['username']); ?>"
                                   data-content="<?php echo strtoupper($mbrimgvalstr); ?>
                                   ">
                                    <i class="far fa-fw fa-question-circle"></i>
                                </a>
                                <a href="index.php?hal=getuser&getId=<?php echo myvalidate($val['id']); ?>&getMpid=<?php echo myvalidate($val['mpid']); ?>" class="btn btn-sm btn-info" data-toggle="tooltip" title="View <?php echo myvalidate($val['username']); ?>"><i class="far fa-fw fa-id-badge"></i></a>

                            </td>

                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="6">
                            <div class="text-center mt-4 text-muted">
                                <div>
                                    <i class="fa fa-3x fa-question-circle"></i>
                                </div>
                                <div><?php echo myvalidate($LANG['g_norecordinfo']); ?></div>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="clearfix"></div>

    <div class="row marginTop">
        <div class="col-sm-12 paddingLeft pagerfwt">
            <?php if ($pages->items_total > 0) { ?>
                <div class="row">
                    <div class="col-md-7">
                        <?php echo myvalidate($pages->display_pages()); ?>
                    </div>
                    <div class="col-md-5 text-right">
                        <span class="d-none d-md-block">
                            <?php echo myvalidate($pages->display_items_per_page()); ?>
                            <?php echo myvalidate($pages->display_jump_menu()); ?>
                            <?php echo myvalidate($pages->items_total()); ?>
                        </span>
                    </div>
                <?php } ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="clearfix"></div>
    </div>

</div>
