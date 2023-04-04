<?php
if (!defined('OK_LOADME')) {
    die('o o p s !');
}

$clistnow = ($_SESSION['clisti'] > 0) ? $_SESSION['clisti'] : $mbrstr['mpid'];
$clistid = ($_SESSION['clistview'] > 0) ? $_SESSION['clistview'] : $clistnow;
$genmpid = ($FORM['loadId']) ? $FORM['loadId'] : $clistid;

if (isset($FORM['clist']) && intval($FORM['clist']) > 0) {
    $_SESSION['clisti'] = intval($FORM['clisti']);
    $_SESSION['clistview'] = intval($FORM['clist']);
    redirpageto('index.php?hal=genealogyview');
    exit;
}

$icyc = 0;
$userData = $db->getRecFrmQry("SELECT * FROM " . DB_TBLPREFIX . "_mbrplans WHERE 1 AND idmbr = '{$mbrstr['id']}' ORDER BY mpid");
if (count($userData) > 0) {
    foreach ($userData as $val) {
        $icyc++;
        $ismarked = ($_SESSION['clisti'] == $icyc) ? ' &#10003;' : '';
        $optviewcycle .= '<a class="dropdown-item" href="index.php?hal=genealogyview&clist=' . $val['mpid'] . '&clisti=' . $icyc . '">' . strtoupper($mbrstr['username']) . ' Cycle ' . $icyc . $ismarked . '</a>';
    }
}

$_SESSION['showFltr'] = $FORM['showFltr'] = ($FORM['showFltr'] != '') ? $FORM['showFltr'] : $_SESSION['showFltr'];
$statusmbrsopt = '';
$statusmbrsarr = array('0' => $LANG['g_all'], '1' => $LANG['g_activeonly']);
foreach ($statusmbrsarr as $key => $value) {
    $btnselcolor = ($FORM['showFltr'] == $key) ? 'success' : 'secondary';
    $statusmbrsopt .= "<a href='index.php?hal=genealogyview&showFltr={$key}' class='btn btn-{$btnselcolor}'>{$value}</a>";
}
?>

<link rel="stylesheet" href="../assets/fellow/treant/Treant.css">
<link rel="stylesheet" href="../assets/fellow/treant/simple-scrollbar.css">
<link rel="stylesheet" href="../assets/fellow/treant/perfect-scrollbar.css">

<div class="section-header">
    <h1><i class="fa fa-fw fa-sitemap"></i> <?php echo myvalidate($LANG['m_genealogyview']); ?></h1>
</div>

<div class="section-body">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4><?php echo myvalidate($LANG['m_membergenealogy']); ?></h4>
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
                        <div class="btn-group">
                            <?php echo myvalidate($statusmbrsopt); ?>
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    <div class="genchart" id="genviewer"></div>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/fellow/treant/raphael.js"></script>
<script src="../assets/fellow/treant/Treant.js"></script>
<script src="../assets/fellow/treant/jquery.mousewheel.js"></script>
<script src="../assets/fellow/treant/perfect-scrollbar.js"></script>
<script src="loadgenview.php?loadId=<?php echo myvalidate($genmpid); ?>&showFltr=<?php echo myvalidate($FORM['showFltr']); ?>"></script>

<script type="text/javascript">
    new Treant(chart_config);
</script>
