<?php

if (!defined('OK_LOADME')) {
    die("<title>Error!</title><body>No such file or directory.</body>");
}
include 'sys.class.php';

function read_file_size($size) {
    if (intval($size) == 0) {
        return("0 Bytes");
    }
    $filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
    return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i];
}

function dborder_arr($tblarr, $tblsel, $tblsrt) {
    $curqryurl = $_SERVER['REQUEST_URI'];
    if ((strpos($curqryurl, "_stbel=") !== false)) {
        $rtblsrt = ($tblsrt == 'up') ? "down" : "up";
        $curqryurl = str_replace("_stbel={$tblsel}", "_stbel=^", $curqryurl);
    } else {
        $curqryx = (false !== strpos($_SERVER['REQUEST_URI'], '?')) ? "&" : "?";
        $curqryurl .= $curqryx . "_stbel=^&_stype=down";
    }

    $tblarrlink = array();
    foreach ($tblarr as $key => $value) {
        if ($tblsel == $value) {
            $curqryurlgo = str_replace("_stype={$tblsrt}", "_stype={$rtblsrt}", $curqryurl);
            $curqryurlgo = str_replace("_stbel=^", "_stbel={$value}", $curqryurlgo);
            $curfontaw = ($tblsrt != 'up') ? "fa fa-fw fa-long-arrow-alt-down" : "fa fa-fw fa-long-arrow-alt-up";
        } else {
            $curqryurlgo = str_replace("_stbel=^", "_stbel={$value}", $curqryurl);
            $curfontaw = "fa fa-fw fa-arrows-alt-v";
        }
        $tblarrlink[$value] = "<a href='{$curqryurlgo}'><i class='{$curfontaw}'></i></a>";
    }
    return $tblarrlink;
}

function select_opt($valarr, $valsel = '', $tostr = 0) {
    if ($tostr != 0) {
        $selopt = $valarr[$valsel];
    } else {
        $selopt = ($valsel == '') ? "<option selected>-</option>" : "<option disabled>-</option>";
        foreach ($valarr as $key => $value) {
            if ($value == '') {
                continue;
            }
            $selopt .= ($key == $valsel) ? "<option value='{$key}' selected>{$value}</option>" : "<option value='{$key}'>{$value}</option>";
        }
    }
    return $selopt;
}

function checkbox_opt($value, $targetval = 1, $tostr = 0) {
    if ($tostr != 0) {
        $cekopt = ($value == $targetval) ? "Yes" : "No";
    } else {
        $cekopt = ($value == $targetval) ? " checked" : "";
    }
    return $cekopt;
}

function radiobox_opt($valuearr, $targetval = 1) {
    $cekopt = array();
    foreach ($valuearr as $key => $value) {
        $cekopt[$key] = ($value == $targetval) ? ' checked="checked"' : '';
    }
    return $cekopt;
}

function redir_to($redir = '') {
    $refredir = $_SERVER["HTTP_REFERER"];
    $redirto = ($redir == '') ? $refredir : "index.php?hal=" . $redir;
    return $redirto;
}

function myvalidate($myodata) {
    return $myodata;
}

function mystriptag($mysdata, $filter = 'string') {
    $mysdata = trim($mysdata);
    if ($filter == 'email') {
        $mysdata = filter_var($mysdata, FILTER_SANITIZE_EMAIL);
        $mysdata = strtolower($mysdata);
    } elseif ($filter == 'url') {
        $mysdata = filter_var($mysdata, FILTER_SANITIZE_URL);
    } else {
        $mysdata = filter_var($mysdata, FILTER_SANITIZE_STRING);
    }
    if ($filter == 'user') {
        $mysdata = preg_replace("/[^A-Za-z0-9]/", '', $mysdata);
        $mysdata = strtolower($mysdata);
    }
    return strip_tags($mysdata);
}

function imageupload($outfname, $fileimg, $oldimg = '') {
    $valid_extensions = array('jpeg', 'jpg', 'png', 'gif');

    $newimg = $oldimg;
    $path = '../assets/imagextra/';
    if ($fileimg) {
        $img = $fileimg['name'];
        $tmp = $fileimg['tmp_name'];
        $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
        $final_image = $outfname . '.' . $ext;
        // check's valid format
        if (in_array($ext, $valid_extensions)) {
            if ($oldimg != '' && file_exists($oldimg) && strpos($oldimg, '/imagextra/') !== false) {
                unlink($oldimg);
            }
            $path = $path . strtolower($final_image);
            if (move_uploaded_file($tmp, $path)) {
                $newimg = $path;
            }
        }
    }
    return $newimg;
}

function readfile_chunked($filename, $retbytes = true) {
    $chunksize = 2 * (1024 * 1024);
    $buffer = '';
    $cnt = 0;

    $handle = fopen($filename, 'rb');
    if ($handle === false) {
        return false;
    }
    while (!feof($handle)) {
        $buffer = fread($handle, $chunksize);
        echo myvalidate($buffer);
        ob_flush();
        flush();
        if ($retbytes) {
            $cnt += strlen($buffer);
        }
    }
    $status = fclose($handle);
    if ($retbytes && $status) {
        return $cnt;
    }
    return $status;
}

function dodlfile($file_path, $file_name, $mtype) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Type: $mtype");
    header("Content-Disposition: attachment; filename=\"$file_name\"");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: " . filesize($file_path));

    //@readfile($file_path);
    readfile_chunked($file_path);
}

function badgembrplanstatus($statusid, $mpstatus = 0) {
    global $LANG;
    $statusbadge = '';
    switch ($statusid) {
        case "1":
            $statusbadge .= "<span class='badge badge-success'>{$LANG['g_active']}</span>";
            break;
        case "2":
            $statusbadge .= "<span class='badge badge-warning'>{$LANG['g_limited']}</span>";
            break;
        case "3":
            $statusbadge .= "<span class='badge badge-danger'>{$LANG['g_pending']}</span>";
            break;
        default:
            $statusbadge .= "<span class='badge badge-light'>{$LANG['g_inactive']}</span>";
    }
    switch ($mpstatus) {
        case "0":
            $statusbadge .= "<span class='badge badge-light' data-toggle='tooltip' title='Registered only'><i class='fa fa-fw fa-user'></i></span>";
            break;
        case "1":
            $statusbadge .= "<span class='badge badge-success' data-toggle='tooltip' title='Active'><i class='fa fa-fw fa-check'></i></span>";
            break;
        case "2":
            $statusbadge .= "<span class='badge badge-warning' data-toggle='tooltip' title='Expired'><i class='fa fa-fw fa-exclamation'></i></span>";
            break;
        case "3":
            $statusbadge .= "<span class='badge badge-danger' data-toggle='tooltip' title='Pending'><i class='fa fa-fw fa-times'></i></span>";
            break;
        default:
            $statusbadge .= "<span class='badge badge-light' data-toggle='tooltip' title='Unregistered'><i class='fa fa-fw fa-question'></i></span>";
    }
    return $statusbadge;
}

// function to get ip address
function get_userip() {
    $ip = false;
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
}

function redirpageto($destinationurl, $delay = 0) {
    $delay = intval($delay);
    echo "<meta http-equiv='refresh' content='{$delay};url={$destinationurl}'>";
    exit;
}

function formatdate($datetimestr, $type = 'd') {
    global $cfgrow;

    $dtformat = ($type == 'd') ? $cfgrow['sodatef'] : $cfgrow['lodatef'];
    return date($dtformat, strtotime($datetimestr));
}

function addlog_sess($username, $type = 'system', $rememberme = '') {
    global $db, $cfgrow;

    dellog_sess('member');

    $userip = get_userip();
    $mbrstr = getmbrinfo($username, 'username');
    $sesdata = put_optionvals('', 'un', $username);
    $sesdata = put_optionvals($sesdata, 'ip', $userip);

    $sestime = time() + (3600 * $cfgrow['time_offset']);
    $seskey = getpasshash($username . '|' . $userip);

    $data = array(
        'sestype' => $type,
        'sesidmbr' => intval($mbrstr['id']),
        'sesdata' => $sesdata,
        'sestime' => intval($sestime),
        'seskey' => $seskey,
    );

    $sesRow = getlog_sess($seskey);
    if ($sesRow['sesid'] < 1) {
        $db->insert(DB_TBLPREFIX . '_sessions', $data);
    } else {
        $db->update(DB_TBLPREFIX . '_sessions', $data, array('sesid' => $sesRow['sesid']));
    }

    $_SESSION[$cfgrow['md5sess'] . $type] = $seskey;
    if ($rememberme == 1) {
        setcookie($cfgrow['md5sess'] . $type, $seskey, time() + (3600 * 72) + (3600 * $cfgrow['time_offset']), "/");
    } else {
        setcookie($cfgrow['md5sess'] . $type, $seskey, time() + (3600 * 1) + (3600 * $cfgrow['time_offset']), "/");
    }
    return $seskey;
}

function getlog_sess($seskey, $isupdate = '') {
    global $db, $cfgrow;

    $condition = ' AND seskey = "' . $seskey . '" ';
    $row = $db->getRecFrmQry("SELECT * FROM " . DB_TBLPREFIX . "_sessions WHERE 1 " . $condition . "");
    $sesRow = array();
    foreach ($row as $value) {
        $sesRow = array_merge($sesRow, $value);
    }

    // update time
    if ($sesRow['sesid'] > 0 && $isupdate == 1) {
        $sestime = time() + (3600 * $cfgrow['time_offset']);
        $data = array(
            'sestime' => intval($sestime),
        );
        $db->update(DB_TBLPREFIX . '_sessions', $data, array('sesid' => $sesRow['sesid']));
    }
    return $sesRow;
}

function dellog_sess($type = '') {
    global $db, $cfgrow;

    if ($type != '') {
        // delete type session
        $_SESSION['filteruid'] = $_SESSION['clisti'] = $_SESSION['clistview'] = $_SESSION['dotoaster'] = $_SESSION['show_msg'] = '';
        $seskey = ($_SESSION[$cfgrow['md5sess'] . $type] ? $_SESSION[$cfgrow['md5sess'] . $type] : $_COOKIE[$cfgrow['md5sess'] . $type]);
        if ($seskey != '') {
            $db->delete(DB_TBLPREFIX . '_sessions', array('seskey' => $seskey));

            $_SESSION[$cfgrow['md5sess'] . $type] = '';
            setcookie($cfgrow['md5sess'] . $type, '', time() - (3600 * $cfgrow['time_offset']), "/");
        }
    } else {
        // delete old sessions
        $sqlarr = array();
        $tmintvarr = array("system" => (3600 * 6), "admin" => (3600 * 12), "member" => (3600 * 72));
        foreach ($tmintvarr as $key => $value) {
            $sestime = time() - $value;
            $sqlarr[] = "(sestype = '{$key}' AND sestime < {$sestime})";
        }
        $sqladd = implode(' OR ', $sqlarr);
        $condition = "AND ({$sqladd})";
        $db->doQueryStr("DELETE FROM " . DB_TBLPREFIX . "_sessions WHERE 1 " . $condition);
    }
}

function verifylog_sess($type = 'system', $isupdate = '') {
    global $cfgrow;

    $hasil = '';
    $seskey = ($_SESSION[$cfgrow['md5sess'] . $type] ? $_SESSION[$cfgrow['md5sess'] . $type] : $_COOKIE[$cfgrow['md5sess'] . $type]);

    $userip = get_userip();
    $sesRow = getlog_sess($seskey, $isupdate);
    $username = get_optionvals($sesRow['sesdata'], 'un');

    if (password_verify(md5($username . '|' . $userip), $seskey)) {
        $hasil = $seskey;
    } else {
        dellog_sess($seskey);
    }
    return $hasil;
}

function time_since($sestime) {
    global $cfgrow;

    $since = time() + (3600 * $cfgrow['time_offset']) - $sestime;
    $chunks = array(
        array(60 * 60 * 24 * 365, 'year'),
        array(60 * 60 * 24 * 30, 'month'),
        array(60 * 60 * 24 * 7, 'week'),
        array(60 * 60 * 24, 'day'),
        array(60 * 60, 'hour'),
        array(60, 'minute'),
        array(1, 'second')
    );

    for ($i = 0, $j = count($chunks); $i < $j; $i++) {
        $seconds = $chunks[$i][0];
        $name = $chunks[$i][1];
        if (($count = floor($since / $seconds)) != 0) {
            break;
        }
    }

    $print = ($count == 1) ? '1 ' . $name : "$count {$name}s";
    return $print;
}

function showalert($type, $title, $message) {

    $faiconarr = array("info" => "lightbulb", "success" => "check-circle", "warning" => "question-circle", "danger" => "times-circle", "secondary" => "bell", "light" => "bell", "dark" => "bell", "primary" => "bell");
    $faicon = $faiconarr[$type];

    $alert_content = <<<INI_HTML
                <div class="alert alert-{$type} alert-dismissible alert-has-icon show fade">
                    <div class="alert-icon"><i class="far fa-{$faicon} fa-fw"></i></div>
                    <div class="alert-body">
                        <button class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                        <div class="alert-title">{$title}</div>
                        {$message}
                    </div>
                </div>
INI_HTML;

    return $alert_content;
}

function getmbrinfo($id, $bfield = '', $mpid = 0) {
    global $db;

    $userRow = array();
    $bfield = ($bfield == '') ? 'id' : $bfield;

    if ($id != '') {
        $row = $db->getAllRecords(DB_TBLPREFIX . '_mbrs', '*', " AND {$bfield} = '{$id}'");
        foreach ($row as $value) {
            $userRow = array_merge($userRow, $value);
        }
        $row = $db->getAllRecords(DB_TBLPREFIX . '_mbrplans', '*', " AND idmbr = '{$userRow['id']}' ORDER BY cyclingbyid ASC, mpid DESC LIMIT 1");
        foreach ($row as $value) {
            $userRow = array_merge($userRow, $value);
        }
    }

    // plan member
    if ($mpid > 0) {
        $row = $db->getAllRecords(DB_TBLPREFIX . '_mbrplans', '*', " AND mpid = '{$mpid}'");
        foreach ($row as $value) {
            $userRow = array_merge($userRow, $value);
        }
        if ($id == '') {
            $row = $db->getAllRecords(DB_TBLPREFIX . '_mbrs', '*', " AND id = '{$userRow['idmbr']}'");
            foreach ($row as $value) {
                $userRow = array_merge($userRow, $value);
            }
        }
    }

    // payment options
    if ($userRow['id'] > 0) {
        $row = $db->getAllRecords(DB_TBLPREFIX . '_paygates', '*', " AND pgidmbr = '{$userRow['id']}'");
        foreach ($row as $value) {
            $userRow = array_merge($userRow, $value);
        }
    }

    $userRow['username'] = ($userRow['username'] == '') ? 'Administrator' : $userRow['username'];

    return $userRow;
}

function getusernameid($srcval, $targetstr = 'id') {
    global $db;

    if ($srcval < 1) {
        $userRow[$targetstr] = 'Administrator';
    } else {
        if ($targetstr == 'id') {
            $sqlwhere = "username LIKE '{$srcval}'";
        } else {
            $sqlwhere = "id = '{$srcval}'";
        }

        $userRow = array();
        $row = $db->getAllRecords(DB_TBLPREFIX . '_mbrs', '*', ' AND ' . $sqlwhere);
        foreach ($row as $value) {
            $userRow = array_merge($userRow, $value);
        }
    }

    return $userRow[$targetstr];
}

function parsenotify($cntarr, $msg) {
    foreach ((array) $cntarr as $key => $value) {
        $msg = str_replace("[[{$key}]]", $value, $msg);
    }

    // add custom parse
    $msg = str_replace("[[fullname]]", $cntarr['firtname'] . ' ' . $cntarr['lastname'], $msg);

    return $msg;
}

function printlog($idstr = '', $err = '') {
    global $cfgrow;

    if (defined('ISPRINTLOG')) {
        $datetm = date('Y-m-d H:i:s', time() + (3600 * $cfgrow['time_offset']));
        $myfile = file_put_contents('printlog.log', "[{$datetm}][{$idstr}] {$err}" . PHP_EOL, FILE_APPEND | LOCK_EX);
        return $myfile;
    }
}

function passmeter($password) {
    global $payrow, $LANG;

    if ($payrow['testpayon'] == 1) {
        return 1;
    }

    $uppercase = preg_match('#[A-Z]#', $password);
    $lowercase = preg_match('#[a-z]#', $password);
    $number = preg_match('#[0-9]#', $password);
    $specialChars = preg_match('#[^\w]#', $password);

    if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
        return $LANG['g_passmeter'];
    } else {
        return 1;
    }
}

function dosprlist($mpid, $sprlist, $mpdepth) {
    $sprlist = str_replace(' ', '', $sprlist);
    $sprlistarr = explode(',', $sprlist);
    $pos = 2;
    $mpid = intval($mpid);
    $newsprlist = array("|1:{$mpid}|");
    foreach ($sprlistarr as $key => $value) {
        $valarr = explode(':', $value);
        $sprval = intval(str_replace('|', '', $valarr[1]));
        $newsprlist[] = "|{$pos}:{$sprval}|";
        $pos++;
    }
    if ($mpdepth > 0) {
        $newsprlist = array_slice($newsprlist, 0, $mpdepth);
    }

    $newsprout = implode(', ', $newsprlist);
    return $newsprout;
}

function getsprlistid($tier, $sprlist) {
    $mpid = 0;
    $sprlist = str_replace(array(' ', '|'), '', $sprlist);
    $sprlistarr = explode(',', $sprlist);
    foreach ($sprlistarr as $key => $value) {
        $valarr = explode(':', $value);
        if (intval($valarr[0]) == $tier) {
            $mpid = intval($valarr[1]);
            break;
        }
    }
    return $mpid;
}

function getamount($xcm, $regfee, $mrank = 0) {
    $cm = str_replace(' ', '', $xcm);
    if (floatval($regfee) <= 0) {
        $resamount = (strpos($cm, '%') !== false) ? 0 : $cm;
    } else {
        $resamount = (strpos($cm, '%') !== false) ? $cm * $regfee / 100 : $cm;
    }
    return (float) $resamount;
}

function getcmlist($mpid, $sprlist, $cmlist, $mbrstr = array()) {
    $sprcmlist = array();

    $reg_fee = $mbrstr['reg_fee'];
    $mpdepth = $mbrstr['mpdepth'];

    $sprlistarr = explode(',', str_replace(array(' ', '|'), '', $sprlist));
    $cmlistarr = explode(',', str_replace(' ', '', $cmlist));
    for ($i = 0; $i < $mpdepth; $i++) {
        $valarr = explode(':', $sprlistarr[$i]);
        $sprval = intval($valarr[1]);
        if ($sprval < 1) {
            break;
        }
        $sprcm = getamount($cmlistarr[$i], $reg_fee);
        $sprcmlist[$sprval] = $sprcm;
    }

    return $sprcmlist;
}

function addcmlist($memo, $tokencode, $getcmlist = array(), $mbrstr = array(), $trxstr = array()) {
    global $db, $cfgrow, $bpprow;

    if (!function_exists('delivermail')) {
        require_once(INSTALL_PATH . '/common/mailer.do.php');
    }
    $reg_utctime = date('Y-m-d H:i:s', time() + (3600 * $cfgrow['time_offset']));

    $cmcount = 1;
    foreach ((array) $getcmlist as $key => $value) {
        if ($key > 0 && $value > 0) {
            $cmcountstr = (strpos($tokencode, 'TIER') !== false) ? " [{$cmcount}]" : '';
            $sprstr = getmbrinfo('', '', $key);
            $data = array(
                'txdatetm' => $reg_utctime,
                'txtoid' => $sprstr['id'],
                'txamount' => (float) $value,
                'txmemo' => $memo . $cmcountstr,
                'txppid' => $mbrstr['mppid'],
                'txtoken' => "|SRCTXID:{$trxstr['txid']}|, |SRCIDMBR:{$mbrstr['id']}|, |SRCLVPOS:{$cmcount}|, |LCM:{$tokencode}|",
            );
            $insert = $db->insert(DB_TBLPREFIX . '_transactions', $data);

            if ($sprstr['id'] > 0) {
                $cntaddarr['ncm_memo'] = $memo . $cmcountstr;
                $cntaddarr['ncm_amount'] = $bpprow['currencysym'] . $value . ' ' . $bpprow['currencycode'];
                $cntaddarr['dln_username'] = $mbrstr['username'];
                delivermail('mbr_newcm', $sprstr['id'], $cntaddarr);
            }
        }
        $cmcount++;
    }
}

function dolvldone($mbrstr, $trxstr, $mppid = 1) {
    global $db, $cfgrow, $bpprow;

    for ($i = 1; $i <= $mbrstr['mpdepth']; $i++) {
        $mpid = getsprlistid($i, $mbrstr['sprlist']);
        if ($mpid < 1) {
            break;
        } else {
            $sprtag = "|{$i}:{$mpid}|";
            $condition = " AND sprlist LIKE '%{$sprtag}%' AND mpstatus != '0'";
            $row = $db->getAllRecords(DB_TBLPREFIX . '_mbrplans', 'COUNT(*) as totref', $condition);
            $myreftotal = $row[0]['totref'];

            $ix = $i;
            if (pow($mbrstr['mpwidth'], $ix) == $myreftotal) {
                $sprstr = getmbrinfo('', '', $mpid);
                $condition = ' AND txtoid = "' . $sprstr['id'] . '" AND txppid = "' . $mppid . '" AND txtoken LIKE "' . "%|LCM:FRWD{$ix}|%" . '" ';
                $sql = $db->getRecFrmQry("SELECT * FROM " . DB_TBLPREFIX . "_transactions WHERE 1 " . $condition . "");
                if (count($sql) < 1) {
                    $iy = $ix - 1;
                    $rwlistarr = explode(',', str_replace(' ', '', $bpprow['rwlist']));
                    $getcmlist = array($sprstr['mpid'] => $rwlistarr[$iy]);
                    addcmlist("Level Reward", "FRWD{$ix}", $getcmlist, $mbrstr, $trxstr);
                }

                if ($mbrstr['mpdepth'] == $i) {
                    if ($bpprow['isrecycling'] > 0) {
                        $mbrcyc = getmbrinfo('', '', $mpid);
                        if ($bpprow['isrecycling'] == 1) {
                            $sprstr = getmbrinfo($mbrcyc['idspr']);
                            $entrytompid = $sprstr['mpid'];
                        } else {
                            $refstr = getmbrinfo($mbrcyc['idref']);
                            $entrytompid = $refstr['mpid'];
                        }
                        do_autoregplan($mbrcyc, $mbrstr['mpid'], $entrytompid, $mbrcyc['mppid']);
                    }
                }
            }
        }
    }
}

function regmbrplans($mbrstr = array(), $refmpid = 0, $ppid = 1) {
    global $db, $cfgrow, $bpprow, $LANG;

    $resultarr = array();
    $refstr = getmbrinfo('', '', $refmpid);

    $mppid = intval($ppid);
    $idref = intval($refstr['id']);
    $idmbr = $mbrstr['id'];

    $condition = " AND idmbr = '{$idmbr}' AND cyclingbyid = '0'";
    $sql = $db->getRecFrmQry("SELECT * FROM " . DB_TBLPREFIX . "_mbrplans WHERE 1 " . $condition . "");
    if ($bpprow['planstatus'] == 1 && count($sql) < 1) {
        $reg_date = date('Y-m-d', time() + (3600 * $cfgrow['time_offset']));
        $reg_utctime = date('Y-m-d H:i:s', time() + (3600 * $cfgrow['time_offset']));
        $reg_ip = get_userip();

        $mpstatus = ($bpprow['regfee'] <= 0) ? 1 : 0;
        $reg_expd = $reg_date;

        $expday = floatval($bpprow['expday']);
        if ($expday > 0) {
            $expdarr = get_actdate($bpprow['expday']);
            $reg_expd = $expdarr['next'];
        }

        $rprmpid = getmpidflow($refmpid);
        $sprstr = getmbrinfo('', '', $rprmpid);
        $idspr = intval($sprstr['id']);

        $sprlist = dosprlist($sprstr['mpid'], $sprstr['sprlist'], $sprstr['mpdepth']);

        $hostspr = 0;
        $idhostmbr = 0;

        $data = array(
            'idhostmbr' => $idhostmbr,
            'idmbr' => $idmbr,
            'mppid' => $mppid,
            'isdefault' => 1,
            'reg_date' => $reg_date,
            'reg_expd' => $reg_expd,
            'reg_utctime' => $reg_utctime,
            'reg_ip' => $reg_ip,
            'reg_fee' => $bpprow['regfee'],
            'mpstatus' => $mpstatus,
            'hostspr' => $hostspr,
            'idref' => $idref,
            'idspr' => $idspr,
            'sprlist' => $sprlist,
            'mpwidth' => $bpprow['maxwidth'],
            'mpdepth' => $bpprow['maxdepth'],
        );
        $insert = $db->insert(DB_TBLPREFIX . '_mbrplans', $data);
        $newmbrplanid = $db->lastInsertId();
        $resultarr['mpid'] = $newmbrplanid;

        if ($insert) {
            $_SESSION['dotoaster'] = "toastr.success({$LANG['g_toastsuccessinfo']}, {$LANG['g_toastsuccess']});";

            // add transaction records
            if ($bpprow['regfee'] > 0) {
                $data = array(
                    'txdatetm' => $reg_utctime,
                    'txfromid' => $idmbr,
                    'txamount' => (float) $bpprow['regfee'],
                    'txmemo' => 'Registration fee',
                    'txppid' => $mppid,
                    'txtoken' => "|REG:$newmbrplanid|",
                );
                $insert = $db->insert(DB_TBLPREFIX . '_transactions', $data);
                $newtrxid = $db->lastInsertId();
                $resultarr['txid'] = $newtrxid;
            }

            // send new referral signup
            if ($idspr > 0) {
                if (!function_exists('delivermail')) {
                    require_once(INSTALL_PATH . '/common/mailer.do.php');
                }
                $cntaddarr['ppname'] = $bpprow['ppname'];
                $cntaddarr['dln_fullname'] = $mbrstr['firstname'] . " " . $mbrstr['lastname'];
                $cntaddarr['dln_username'] = $mbrstr['username'];
                delivermail('mbr_newdl', $idspr, $cntaddarr);
            }
        } else {
            $_SESSION['dotoaster'] = "toastr.error({$LANG['g_toastfailinfo']}, {$LANG['g_toastfail']});";
        }

        return $resultarr;
    }
}

function iscontentmbr($options, $mbrstr) {
    $hasil = true;
    $avalfor = get_optionvals($options);

    if ($avalfor['mbr'] == 1) {
        if ($avalfor['mbpp1'] != 1 && $mbrstr['mpstatus'] == 1) {
            $hasil = false;
        }
        if ($avalfor['mbpp0'] != 1 && $mbrstr['mpstatus'] != 1) {
            $hasil = false;
        }
    }
    return $hasil;
}

function dotrxwallet($txtoid = 0, $limit = 25) {
    global $db, $cfgrow, $bpprow;

    $sqltoid = ($txtoid == 0) ? "txtoid > '0'" : "txtoid = '{$txtoid}'";
    $ListData = $db->getRecFrmQry("SELECT * FROM " . DB_TBLPREFIX . "_transactions WHERE 1 AND txfromid = '0' AND " . $sqltoid . " AND txstatus = '0' AND txtoken NOT LIKE '%|WIDR:%' LIMIT {$limit}");
    if (count($ListData) > 0) {
        $numcount = $ewallet = 0;
        $txtmstamp = date('Y-m-d H:i:s', time() + (3600 * $cfgrow['time_offset']));
        foreach ($ListData as $val) {

            $txbatch = 'WLN' . date("mdH-is") . $val['txid'];
            $txtoken = $val['txtoken'] . ', |WALT:IN|';

            $data = array(
                'txpaytype' => 'system',
                'txbatch' => $txbatch,
                'txtmstamp' => $txtmstamp,
                'txtoken' => $txtoken,
                'txstatus' => 1,
            );
            $update = $db->update(DB_TBLPREFIX . '_transactions', $data, array('txid' => $val['txid']));

            $mbrstr = getmbrinfo($val['txtoid']);
            $ewallet = $mbrstr['ewallet'] + $val['txamount'];
            $update = $db->update(DB_TBLPREFIX . '_mbrs', array('ewallet' => $ewallet), array('id' => $mbrstr['id']));

            $numcount++;
            if ($numcount < 1) {
                break;
            }
        }
    }
}

function adjusttrxwallet($oldamount, $newamount, $idmbr, $txtokenstr = '', $txadminfo = '', $isminval = 0) {
    global $db, $cfgrow;

    if ($oldamount != $newamount && ($newamount > 0 || $isminval == 1)) {
        $txbatch = date("mdH-is-{$idmbr}");
        if ($oldamount < $newamount) {
            // add
            $txfromid = 0;
            $txtoid = $idmbr;
            $txamount = $newamount - $oldamount;
            $txmemo = "Wallet Credit Correction";
            $txbatch = 'WLN' . $txbatch;
            $txtoken = '|WALT:IN|';
        } else {
            // deduct
            $txfromid = $idmbr;
            $txtoid = 0;
            $txamount = $oldamount - $newamount;
            $txmemo = "Wallet Debit Correction";
            $txbatch = 'WLT' . $txbatch;
            $txtoken = '|WALT:OUT|';
        }

        $txtoken64 = base64_encode($txtokenstr);
        $txtoken = $txtoken . ", |NOTE:{$txtoken64}|";

        $txdatetm = date('Y-m-d H:i:s', time() + (3600 * $cfgrow['time_offset']));
        $data = array(
            'txdatetm' => $txdatetm,
            'txfromid' => $txfromid,
            'txtoid' => $txtoid,
            'txpaytype' => 'system',
            'txamount' => (float) $txamount,
            'txmemo' => $txmemo,
            'txbatch' => $txbatch,
            'txtmstamp' => $txdatetm,
            'txppid' => $mppid,
            'txstatus' => 1,
            'txtoken' => $txtoken,
            'txadminfo' => $txadminfo,
        );
        $insert = $db->insert(DB_TBLPREFIX . '_transactions', $data);
    }
}

function getwebssdata($mbrstr, $url) {
    $mbrid = $mbrstr['id'];
    if (function_exists('curl_init') && intval($mbrid) > 0 && filter_var($url, FILTER_VALIDATE_URL) !== FALSE && $_SESSION['getwebssdata' . $mbrid] == '') {
        $ch = curl_init("https://www.googleapis.com/pagespeedonline/v2/runPagespeed?url={$url}&screenshot=true");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $googlepsdata = json_decode($response, true);
        $snap = $googlepsdata['screenshot']['data'];
        $snap = str_replace(['_', '-'], ['/', '+'], $snap);

        if ($snap) {
            $imgtofile = "/assets/imagextra/mbr_imgsrc_{$mbrid}.dat";
            $datfile = INSTALL_PATH . $imgtofile;
            file_put_contents($datfile, $snap, LOCK_EX);
            $_SESSION['getwebssdata' . $mbrid] = 1;
            return $imgtofile;
        }
    }
}

function getdocurl($initurl, $arrdata) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $initurl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arrdata, '', '&'));
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $arrResponse['err'] = curl_error($ch);
    }
    curl_close($ch);
    $arrResponse['data'] = json_decode($response, true);
    return $arrResponse;
}

function do_imgresize($targetFile, $originalFile, $newWidth, $newHeight = 0, $ext = '') {

    $info = getimagesize($originalFile);
    $mime = ($ext == '') ? $info['mime'] : "image/{$ext}";

    switch ($mime) {
        case 'image/jpeg':
            $image_save_func = 'imagejpeg';
            $new_image_ext = 'jpg';
            break;

        case 'image/png':
            $image_save_func = 'imagepng';
            $new_image_ext = 'png';
            break;

        case 'image/gif':
            $image_save_func = 'imagegif';
            $new_image_ext = 'gif';
            break;

        default:
            exit();
    }

    $img = imagecreatefromstring(file_get_contents($originalFile));
    list($width, $height) = getimagesize($originalFile);

    $propHeight = ($height / $width) * $newWidth;
    $newHeight = ($newHeight > 0) ? $newHeight : $propHeight;
    $tmp = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    $targetFile = '../assets/imagextra/' . $targetFile;

    if (file_exists($targetFile)) {
        unlink($targetFile);
    }
    $newimg = "$targetFile.$new_image_ext";
    $image_save_func($tmp, $newimg);
    return $newimg;
}

/* usage example:
  $resultdate = get_actdate($intvdatetime, $basedate);
  $resultdate['var'] = $intvdatetime type ('H', 'D', 'W', 'M', 'Y', ''=in days)
  $resultdate['var_str'] = $intvdatetime type ('Hour', 'Day', 'Week', 'Month', 'Year', ''=in days)
  $resultdate['val'] = value from the $intvdatetime, example 10 -> 10, 12w -> 12, 4m -> 4, etc;
  $resultdate['val_str'] = value from the $intvdatetime in days, example 10 -> 10, 23h -> 0, 5d -> 5, 2w -> 14, 1m -> 30, etc;
  $resultdate['next'] = $basedate + $intvdatetime;
  $resultdate['prev'] = $basedate - $intvdatetime;
  $resultdate['now'] = $basedate;
  $resultdate['diffdays'] = different (in days) between $basedate and $resultdate['next'];
 */

function get_actdate($intvdatetime, $basedate = '') {
    global $cfgrow;

    $basedate = ($basedate == '') ? date('Y-m-d H:i:s', time() + (3600 * $cfgrow['time_offset'])) : $basedate;
    $arrdate = getdate(strtotime($basedate));
    $istime = (strlen($basedate) > 12 && $arrdate['hours'] != '') ? 'y' : 'n';

    $result = array();
    $intvdatetime = str_replace(" ", "", strtoupper($intvdatetime));
    if (!is_numeric($intvdatetime)) {
        $result['var'] = substr($intvdatetime, -1);
        $result['val'] = str_replace($result['var'], "", $intvdatetime);
        $result['val'] = intval($result['val']);

        switch ($result['var']) {
            case "H":
                $result['var_str'] = 'Hour';
                $result['val_str'] = $result['val'] * 0;
                $strjng = 'hour';
                break;
            case "W":
                $result['var_str'] = 'Week';
                $result['val_str'] = $result['val'] * 7;
                $strjng = 'week';
                break;
            case "M":
                $result['var_str'] = 'Month';
                $result['val_str'] = $result['val'] * 30;
                $strjng = 'month';
                break;
            case "Y":
                $result['var_str'] = 'Year';
                $result['val_str'] = $result['val'] * 365;
                $strjng = 'year';
                break;
            default:
                $result['var_str'] = 'Day';
                $result['val_str'] = $result['val'];
                $strjng = 'day';
        }

        if ($result['val'] > 1)
            $strjng .= 's';
    } else {
        $result['var'] = 'D';
        $result['var_str'] = 'Day';
        $strjng = 'day';
        $result['val'] = $result['val_str'] = intval($intvdatetime);
        if ($result['val'] > 1)
            $strjng .= 's';
    }

    $str_basedate = strtotime($basedate);
    $str_diffdate = $result['val'] . ' ' . $strjng;
    $str_basedate_add = strtotime("+" . $str_diffdate, $str_basedate);
    $str_basedate_les = strtotime("-" . $str_diffdate, $str_basedate);

    if ($istime == 'y') {
        $result['next'] = date("Y-m-d H:i:s", $str_basedate_add);
        $result['prev'] = date("Y-m-d H:i:s", $str_basedate_les);
    } else {
        $result['next'] = date("Y-m-d", $str_basedate_add);
        $result['prev'] = date("Y-m-d", $str_basedate_les);
    }

    $result['now'] = $basedate;
    $dateTimeEnd = $result['next'];
    $dateTimeBegin = $result['now'];

    $timedifference = strtotime($dateTimeEnd) - strtotime($dateTimeBegin);
    $result['diffdays'] = floor($timedifference / 86400);

    return $result;
}

function get_unpaidtxid($mbrstr) {
    global $db;

    $txunpaidrow = $db->getRecFrmQry("SELECT txid FROM " . DB_TBLPREFIX . "_transactions WHERE txfromid = '{$mbrstr['id']}' AND txppid = '{$mbrstr['mppid']}' AND txtoken LIKE '%|RENEW:%' AND txamount > 0 AND txstatus = '0'");
    return $txunpaidrow[0]['txid'];
}

function do_expmbr($limitcheck = 48) {
    global $db, $cfgrow, $bptoken, $bpprow, $plantokenarr;

    $reg_utctime = date('Y-m-d H:i:s', time() + (3600 * $cfgrow['time_offset']));
    $now_date = date('Y-m-d', time() + (3600 * $cfgrow['time_offset']));
    $expday = floatval($bpprow['expday']);
    $graceday = floatval($bpprow['graceday']);

    if ($expday > 0) {

        //reminder
        $remindreg = $bptoken['remindreg'];
        if (intval($remindreg) > 0) {
            $expdarr = get_actdate($remindreg, $now_date);
            $remindate = $expdarr['next'];
            $condition = " AND mpstatus = '1' AND mppid = '{$bpprow['ppid']}' AND reg_expd <= '{$remindate}' AND rmdexp = '0' ORDER BY RAND() LIMIT {$limitcheck}";
            $userData = $db->getRecFrmQry("SELECT * FROM " . DB_TBLPREFIX . "_mbrs LEFT JOIN " . DB_TBLPREFIX . "_mbrplans ON id = idmbr WHERE 1 " . $condition . "");

            if (count($userData) > 0) {
                foreach ($userData as $val) {
                    // send message here
                    require_once('mailer.do.php');
                    $cntaddarr['ppname'] = $bpprow['ppname'];
                    $cntaddarr['fullname'] = $val['firstname'] . ' ' . $val['lastname'];
                    $cntaddarr['login_url'] = $cfgrow['site_url'] . "/" . MBRFOLDER_NAME;
                    delivermail('mbr_rereg', $val['id'], $cntaddarr);

                    $db->update(DB_TBLPREFIX . '_mbrplans', array('rmdexp' => '1'), array('mpid' => $val['mpid']));

                    do_renewtx($reg_utctime, $val);
                }
            }
        }

        //expired
        $grace_prev = date('Y-m-d', strtotime('-' . $graceday . ' day', strtotime($reg_utctime)));

        $condition = " AND mpstatus = '1' AND reg_expd < '{$reg_utctime}' ORDER BY RAND() LIMIT {$limitcheck}";
        $userData = $db->getRecFrmQry("SELECT * FROM " . DB_TBLPREFIX . "_mbrs LEFT JOIN " . DB_TBLPREFIX . "_mbrplans ON id = idmbr WHERE 1 " . $condition . "");
        if (count($userData) > 0) {
            foreach ($userData as $val) {
                do_renewtx($reg_utctime, $val);

                if ($graceday > 0 && $val['reg_expd'] < $grace_prev && $val['reg_date'] < $val['reg_expd'] && $val['reg_fee'] > 0) {
                    $db->update(DB_TBLPREFIX . '_mbrplans', array('mpstatus' => 2), array('mpid' => $val['mpid']));
                }
            }
        }

        // auto-renewal using available ewallet balance
        if ($plantokenarr['isrenewbywallet'] == '1') {
            $condition = " AND mpstatus = '2' ORDER BY RAND() LIMIT {$limitcheck}";
            $userData = $db->getRecFrmQry("SELECT * FROM " . DB_TBLPREFIX . "_mbrs LEFT JOIN " . DB_TBLPREFIX . "_mbrplans ON id = idmbr WHERE 1 " . $condition . "");
            if (count($userData) > 0) {
                foreach ($userData as $val) {
                    $mbrstr = getmbrinfo('', '', $val['mpid']);
                    if ($mbrstr['ewallet'] >= $val['reg_fee']) {
                        do_walletplanpay($mbrstr);
                    }
                }
            }
        }
    }
}

function do_prerenewtx($txmpid, $mpstatus) {
    global $cfgrow;

    if ($mpstatus == 2) {
        // if expiry check transaction history and generate it if not exist
        $sb_txmpidarr = explode('-', $txmpid);
        $mpid = $sb_txmpidarr[1];
        $reg_utctime = date('Y-m-d H:i:s', time() + (3600 * $cfgrow['time_offset']));
        $mbrstr = getmbrinfo('', '', $mpid);
        $txid = do_renewtx($reg_utctime, $mbrstr);
        $newtxmpid = $txid . '-' . $mpid;
    } else {
        $newtxmpid = $txmpid;
    }
    return $newtxmpid;
}

function do_renewtx($utctime, $mbrevalarr) {
    global $db;

    $sql = $db->getRecFrmQry("SELECT * FROM " . DB_TBLPREFIX . "_transactions WHERE txfromid = '{$mbrevalarr['id']}' AND txppid = '{$mbrevalarr['mppid']}' AND txtoken LIKE '%|PREVEXP:{$mbrevalarr['reg_expd']}|%'");
    if ($mbrevalarr['reg_fee'] > 0 && count($sql) < 1) {
        $data = array(
            'txdatetm' => $utctime,
            'txfromid' => $mbrevalarr['id'],
            'txamount' => (float) $mbrevalarr['reg_fee'],
            'txmemo' => 'Renewal fee',
            'txppid' => $mbrevalarr['mppid'],
            'txtoken' => "|RENEW:{$mbrevalarr['mpid']}|, |PREVEXP:{$mbrevalarr['reg_expd']}|",
        );
        $db->insert(DB_TBLPREFIX . '_transactions', $data);
        $txid = $db->lastInsertId();
    } else {
        $txid = $sql[0]['txid'];
    }
    return $txid;
}

function maskmail($email) {
    if (!defined('ISDEMOMODE')) {
        return $email;
    } else {
        $em = explode("@", $email);
        $name = implode('@', array_slice($em, 0, count($em) - 1));
        $len = floor(strlen($name) / 2);
        return substr($name, 0, $len) . str_repeat('*', $len) . "*@" . end($em);
    }
}

function get_countrycode($log_ip) {
    global $country_array;

    require_once('geoip.class.php');
    $geoplugin = new geoPlugin();
    $geoplugin->locate($log_ip);

    $countryc = $geoplugin->countryCode;
    $countryc = strtoupper($countryc);
    if (array_key_exists($countryc, $country_array)) {
        return $countryc;
    } else {
        return '';
    }
}

function ppdbarr() {
    global $db;

    $result = array();
    $condition = " AND ppname != ''";
    $userData = $db->getRecFrmQry("SELECT * FROM " . DB_TBLPREFIX . "_payplans WHERE 1" . $condition . " ORDER BY ppid LIMIT 6");
    if (count($userData) > 0) {
        foreach ($userData as $val) {
            foreach ($val as $key => $value) {
                $result[$val['ppid']][$key] = $value;
            }
        }
    }
    return $result;
}

function ppdbplan($mppid = 1) {
    global $bpprowbase, $bpparr, $planlogo;
    $bpprowplan = $bpparr[$mppid];
    $result = array_merge($bpprowbase, $bpprowplan);
    $planlogo = ($result['planlogo']) ? $result['planlogo'] : DEFIMG_PLAN;
    $result['planlogo'] = $planlogo;

    return $result;
}

function do_autoregplan($mbrstr, $cyclingbyid, $refmpid, $newmppid = 1) {
    global $db, $bpparr, $FORM, $plantokenarr;

    $data = array(
        'isdefault' => '0',
        'cyclingbyid' => $cyclingbyid,
    );
    $update = $db->update(DB_TBLPREFIX . '_mbrplans', $data, array('mpid' => $mbrstr['mpid']));

    dotrxwallet($mbrstr['id']);
    $mbrstr = getmbrinfo($mbrstr['id']);

    $resultarr = regmbrplans($mbrstr, $refmpid, $newmppid);
    $txid = $resultarr['txid'];
    $mpid = $resultarr['mpid'];

    if ($plantokenarr['doreactive'] == '1') {
        include_once('sandbox.php');
        $FORM['sb_type'] = 'payreg';
        $txmpid = $txid . '-' . $mpid;
        $payamount = $bpparr[$newmppid]['regfee'];
        $paygate = 'Wallet';
        doipnbox($txmpid, $payamount, $paygate, '', '', 'OK', 0, '');

        $condition = " AND txid = '{$txid}'";
        $txrow = $db->getAllRecords(DB_TBLPREFIX . '_transactions', '*', $condition);
        $txrowbatch = $txrow[0]['txbatch'];

        $newamount = $mbrstr['ewallet'] - $payamount;
        $txtokenstr = "Register to " . $bpparr[$newmppid]['ppname'] . " ($txrowbatch)";
        adjusttrxwallet($mbrstr['ewallet'], $newamount, $mbrstr['id'], $txtokenstr, '', 1);
        $data = array(
            'ewallet' => $newamount,
        );
        $db->update(DB_TBLPREFIX . '_mbrs', $data, array('id' => $mbrstr['id']));

        $mpstatus = ($newamount >= 0) ? 1 : 3;
        $data = array(
            'mpstatus' => $mpstatus,
        );
        $update = $db->update(DB_TBLPREFIX . '_mbrplans', $data, array('mpid' => $resultarr['mpid']));
    }
}

function add_sprlist($mpid, $sprlist) {
    $sprlistx = "|1:{$mpid}|";
    $dlist = explode(',', str_replace(' ', '', $sprlist));
    for ($i = 1; $i <= count($dlist); $i++) {
        $node = explode(':', str_replace('|', '', $dlist[$i]));
        $pos = $node[0];
        $val = $node[1];
        if (intval($pos) < 1) {
            break 1;
        }
        if (intval($val) > 0) {
            $pos++;
        }
        $listx = ", |" . $pos . ":" . $val . "|";
        $sprlistx = $sprlistx . $listx;
    }
    return $sprlistx;
}

function do_movembr($mbrstr, $newunspr) {
    global $db;

    $newsprstr = getmbrinfo($newunspr, 'username');
    if ($newsprstr['id'] > 0) {
        $newsprlist = add_sprlist($newsprstr['mpid'], $newsprstr['sprlist']);
        $data = array(
            'idspr' => $newsprstr['id'],
            'sprlist' => $newsprlist,
        );
        $update = $db->update(DB_TBLPREFIX . '_mbrplans', $data, array('mpid' => $mbrstr['mpid']));

        $xdlist = ":" . $mbrstr['mpid'] . "|";
        $condition = " AND sprlist LIKE '%{$xdlist}%'";
        $userData = $db->getRecFrmQry("SELECT * FROM " . DB_TBLPREFIX . "_mbrplans WHERE 1 " . $condition . "");
        foreach ($userData as $val) {
            $mysprstr = getmbrinfo($val['idspr']);
            $sprlist = add_sprlist($mysprstr['mpid'], $mysprstr['sprlist']);
            $data = array(
                'sprlist' => $sprlist,
            );
            $db->update(DB_TBLPREFIX . '_mbrplans', $data, array('mpid' => $val['mpid']));
        }
        return $update;
    }
}

function do_dbbakup() {
    global $db, $cfgrow, $cfgtoken, $umbasever;

    $dbaknow = date('Y-m-d H:i:s', time() + (3600 * $cfgrow['time_offset']));
    $dbakint = $cfgtoken['dbakint'];
    $dbakeml = base64_decode($cfgtoken['dbakeml']);
    $dbakdate = base64_decode($cfgtoken['dbakdate']);

    $nextbak = get_actdate($dbakint, $dbakdate);
    $datenextbak = $nextbak['next'];

    if (($dbakdate == null || $datenextbak <= $dbaknow) && $dbakint != '0' && $dbakeml != '') {
        $dat = date('Ymd_His');
        if (function_exists('gzencode')) {
            $cmp = "gz";
            $backup_filename = "" . DB_NAME . "_$dat.sql.$cmp";
        } else {
            $cmp = "";
            $backup_filename = "" . DB_NAME . "_$dat.sql";
        }

        include_once('../common/umver.php');
        require_once('../common/mailer.do.php');
        $bakdbcnt = gobackup($cmp);

        //Set the subject line
        $msgsubject = "Database backup " . $backup_filename;

        // HTML body
        $fmessagehtml = "<font size=3><b>UniMatrix v{$umbasever} - Database Backup</b></font><br /><br />";
        $fmessagehtml .= "{$cfgtoken['site_subname']}<br />";
        $fmessagehtml .= "Creation date: <b>" . date("Y-m-d H:i:s", time()) . "</b><br />";
        $fmessagehtml .= "Database: " . DB_NAME . "<br />";

        // Plain text body (for mail clients that cannot read HTML)
        $fmessage = "UniMatrix v{$umbasever} - Database Backup\n";
        $fmessage .= "{$cfgtoken['site_subname']}\n";
        $fmessage .= "Creation date: " . date("Y-m-d H:i:s", time()) . "\n";
        $fmessage .= "Database: " . DB_NAME . "\n";

        $isdomailer = domailer($cfgtoken['site_subname'], $dbakeml, $msgsubject, $fmessagehtml, $fmessage, $bakdbcnt, $backup_filename);

        if ($isdomailer) {
            $newcfgtoken = $cfgrow['cfgtoken'];
            $newcfgtoken = put_optionvals($newcfgtoken, 'dbakdate', base64_encode($dbaknow));
            $data = array(
                'cfgtoken' => $newcfgtoken,
            );
            $update = $db->update(DB_TBLPREFIX . '_configs', $data, array('cfgid' => '1'));
        }
    }
}

function do_mbrdel($delId, $istx = '') {
    global $db, $cfgtoken;

    $db->delete(DB_TBLPREFIX . '_mbrs', array('id' => $delId));
    $db->delete(DB_TBLPREFIX . '_mbrplans', array('idmbr' => $delId));

    // remove transaction history
    if ($cfgtoken['mbrdelopt'] == '1' && $istx != '') {
        $condition = " AND (txtoken LIKE '%|SRCIDMBR:{$delId}|%' OR txfromid = '{$delId}')";
        $deltxrow = $db->getAllRecords(DB_TBLPREFIX . '_transactions', '*', $condition);
        foreach ($deltxrow as $key => $txval) {
            $deltxid = $txval['txid'];
            $db->delete(DB_TBLPREFIX . '_transactions', array('txid' => $deltxid));

            // adjust member ewallet
            if ($txval['txtoid'] > 0 && $txval['txstatus'] == '1') {
                $mbrtostr = getmbrinfo($txval['txtoid']);
                $newamount = $mbrtostr['ewallet'] - $txval['txamount'];
                adjusttrxwallet($mbrtostr['ewallet'], $newamount, $txval['txtoid'], "Amount Reversal ID{$delId}", "Amount adjustment from the member removal", 1);
                $data = array(
                    'ewallet' => $newamount,
                );
                $db->update(DB_TBLPREFIX . '_mbrs', $data, array('id' => $mbrtostr['id']));
            }
        }
    }
}

function get_withdrawfee() {
    global $cfgrow;

    $wdrwfeearr = array();
    $wdvarval = $cfgrow['wdrawfee'];
    $wdvarvalarr = explode('|', $wdvarval);
    $fval = (strpos($wdvarvalarr[0], '%') !== false) ? $wdvarvalarr[0] / 100 : $wdvarvalarr[0];
    $wdrwfeearr['fee'] = (float) $fval;
    $wdrwfeearr['cap'] = (float) $wdvarvalarr[1];
    return $wdrwfeearr;
}

function get_pgmbrtoken($mbrstr) {
    global $db, $mbrpaystr;

    $pgdatatoken = $mbrstr['pgdatatoken'];
    $pgmbrtokenarr = get_optionvals($pgdatatoken);

    $mbrperfectmoneycfg = get_optarr($pgmbrtokenarr['perfectmoneycfg']);
    $mbrpayfastcfg = get_optarr($pgmbrtokenarr['payfastcfg']);
    $mbrpaystackcfg = get_optarr($pgmbrtokenarr['paystackcfg']);

    $mbrpaystr = array();
    $mbrpayrow = $db->getAllRecords(DB_TBLPREFIX . '_paygates', '*', ' AND pgidmbr = "' . $mbrstr['id'] . '"');
    $mbrpaystr['manualpayipn'] = base64_decode($mbrpayrow[0]['manualpayipn']);
    $mbrpaystr['coinpaymentsmercid'] = base64_decode($mbrpayrow[0]['coinpaymentsmercid']);
    $mbrpaystr['paypalacc'] = base64_decode($mbrpayrow[0]['paypalacc']);

    $mbrpaystr['perfectmoneyacc'] = $mbrperfectmoneycfg['perfectmoneyacc'];
    $mbrpaystr['payfastmercid'] = $mbrpayfastcfg['payfastmercid'];
    $mbrpaystr['paystackpub'] = $mbrpaystackcfg['paystackpub'];

    return $mbrpaystr;
}

function do_withdrawreq($mbrstr, $txamount, $txpaytype) {
    global $db, $cfgrow, $LANG, $avalwithdrawgate_array;

    if ($txamount <= 0) {
        return false;
    }

    $wdrwfeearr = get_withdrawfee();
    $fval = $wdrwfeearr['fee'];
    $fcapval = $wdrwfeearr['cap'];

    $mbrpaystr = get_pgmbrtoken($mbrstr);

    $txamountval = $txamount;
    $txwdrfee = $txamountfee = 0;
    if ($fval > 0) {
        $txwdrfee = $txamount * $fval;
        $txamountfee = ($fcapval <= $txwdrfee) ? $fcapval : $txwdrfee;
        $txamountval = $txamount - (float) $txamountfee;
    }

    // deduct wallet
    $ewallet = $mbrstr['ewallet'] - $txamount;
    $data = array(
        'ewallet' => $ewallet,
    );
    $update = $db->update(DB_TBLPREFIX . '_mbrs', $data, array('id' => $mbrstr['id']));

    // add withdraw request
    $paybyopt = $avalwithdrawgate_array[$txpaytype];
    $txadminfo = "Payout To [{$paybyopt}]: ";
    $txadminfo .= $mbrpaystr[$txpaytype];
    $txdatetm = date('Y-m-d H:i:s', time() + (3600 * $cfgrow['time_offset']));
    $data = array(
        'txdatetm' => $txdatetm,
        'txpaytype' => $txpaytype,
        'txfromid' => $mbrstr['id'],
        'txtoid' => 0,
        'txamount' => $txamountval,
        'txmemo' => $LANG['g_withdrawstr'],
        'txppid' => $mbrstr['mppid'],
        'txtoken' => "|WIDR:OUT|, |WDRTXFEE:{$txamountfee}|",
        'txstatus' => 0,
        'txadminfo' => $txadminfo,
    );
    $insert = $db->insert(DB_TBLPREFIX . '_transactions', $data);

    if ($insert) {
        $newtrxid = $db->lastInsertId();
        if ($txamountfee > 0) {
            $txdatetm = date('Y-m-d H:i:s', time() + (3600 * $cfgrow['time_offset']));
            $txlogtime = date('mdH-is-' . $newtrxid, time() + (3600 * $cfgrow['time_offset']));
            $data = array(
                'txdatetm' => $txdatetm,
                'txpaytype' => $txpaytype,
                'txfromid' => $mbrstr['id'],
                'txtoid' => 0,
                'txamount' => $txamountfee,
                'txmemo' => $LANG['g_withdrawfee'],
                'txppid' => $mbrstr['mppid'],
                'txtoken' => "|WDRTXID:{$newtrxid}|, |NOTE:" . base64_encode("WDRID-{$txlogtime}") . "|",
                'txstatus' => 1,
            );
            $insertrx = $db->insert(DB_TBLPREFIX . '_transactions', $data);
        }
    }

    return $insert;
}

function do_walletplanpay($mbrstr) {
    global $db, $cfgrow, $bpparr;

    $utctime = date('Y-m-d H:i:s', time() + (3600 * $cfgrow['time_offset']));
    $txid = do_renewtx($utctime, $mbrstr);
    $mpid = $mbrstr['mpid'];
    $newmppid = $mbrstr['mppid'];

    $condition = " AND txid = '{$txid}'";
    $txrow = $db->getAllRecords(DB_TBLPREFIX . '_transactions', '*', $condition);
    $txrowamount = $txrow[0]['txamount'];

    if ($mbrstr['ewallet'] >= $txrowamount) {
        $txbatch = "R" . date("md") . "-" . date("H") . "{$mpid}";
        $newamount = $mbrstr['ewallet'] - $txrowamount;
        $txtokenstr = "Renewal " . $bpparr[$newmppid]['ppname'] . " ({$txbatch})";
        adjusttrxwallet($mbrstr['ewallet'], $newamount, $mbrstr['id'], $txtokenstr, '', 1);
        $data = array(
            'ewallet' => $newamount,
        );
        $db->update(DB_TBLPREFIX . '_mbrs', $data, array('id' => $mbrstr['id']));

        include_once('sandbox.php');
        $FORM['sb_type'] = 'payreg';
        $txmpid = $txid . '-' . $mpid;
        $paygate = 'Wallet';
        doipnbox($txmpid, $txrowamount, $paygate, $txbatch, '-HTTPREF-', '');

        $mpstatus = ($newamount >= 0) ? 1 : 3;
        $data = array(
            'mpstatus' => $mpstatus,
        );
        $update = $db->update(DB_TBLPREFIX . '_mbrplans', $data, array('mpid' => $mpid));

        $data = array(
            'txbatch' => $txbatch,
        );
        $update = $db->update(DB_TBLPREFIX . '_transactions', $data, array('txid' => $txid));
    }
}
