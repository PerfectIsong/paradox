<?php

include_once('../common/init.loader.php');
include_once('../common/umver.php');

$key = base64_encode($FORM['key']);
dosupdate($key, $cfgrow['softversion'], $umbasever);
redirpageto('index.php?hal=dashboard', 1);
@unlink("licupdate.php");
