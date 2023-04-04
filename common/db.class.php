<?php

if (!defined('OK_LOADME')) {
    die("<title>Error!</title><body>No such file or directory.</body>");
}

class Database {

    /**
     * database connection object
     * @var \PDO
     */
    protected $pdo;

    /**
     * Connect to the database
     */
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Return the pdo connection
     */
    public function getPdo() {
        return $this->pdo;
    }

    /**
     * Changes a camelCase table or field name to lowercase,
     * underscore spaced name
     *
     * @param  string $string camelCase string
     * @return string underscore_space string
     */
    protected function camelCaseToUnderscore($string) {
        $string = "`" . str_replace("`", "``", $string) . "`";
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
    }

    /**
     * Returns the ID of the last inserted row or sequence value
     *
     * @param  string $param Name of the sequence object from which the ID should be returned.
     * @return string representing the row ID of the last row that was inserted into the database.
     */
    public function lastInsertId($param = null) {
        return $this->pdo->lastInsertId($param);
    }

    /**
     * handler for dynamic CRUD methods
     *
     * Format for dynamic methods names -
     * Create:  insertTableName($arrData)
     * Retrieve: getTableNameByFieldName($value)
     * Update: updateTableNameByFieldName($value, $arrUpdate)
     * Delete: deleteTableNameByFieldName($value)
     *
     * @param  string     $function
     * @param  array      $arrParams
     * @return array|bool
     */
    public function __call($function, array $params = array()) {
        if (!preg_match('/^(get|update|insert|delete)(.*)$/', $function, $matches)) {
            throw new \BadMethodCallException($function . ' is an invalid method Call');
        }

        if ('insert' == $matches[1]) {
            if (!is_array($params[0]) || count($params[0]) < 1) {
                throw new \InvalidArgumentException('insert values must be an array');
            }
            return $this->insert($this->camelCaseToUnderscore($matches[2]), $params[0]);
        }

        list($tableName, $fieldName) = explode('By', $matches[2], 2);
        if (!isset($tableName, $fieldName)) {
            throw new \BadMethodCallException($function . ' is an invalid method Call');
        }

        if ('update' == $matches[1]) {
            if (!is_array($params[1]) || count($params[1]) < 1) {
                throw new \InvalidArgumentException('update fields must be an array');
            }
            return $this->update(
                            $this->camelCaseToUnderscore($tableName), $params[1], array($this->camelCaseToUnderscore($fieldName) => $params[0])
            );
        }

        //select and delete method
        return $this->{$matches[1]}(
                        $this->camelCaseToUnderscore($tableName), array($this->camelCaseToUnderscore($fieldName) => $params[0])
        );
    }

    /**
     * Record retrieval method
     *
     * @param  string     $tableName name of the table
     * @param  array      $where     (key is field name)
     * @return array|bool (associative array for single records, multidim array for multiple records)
     */
    public function get($tableName, $whereAnd = array(), $whereOr = array(), $whereLike = array()) {
        $cond = '';
        $s = 1;
        $params = array();
        foreach ($whereAnd as $key => $val) {
            $cond .= " And " . $key . " = :a" . $s;
            $params['a' . $s] = $val;
            $s++;
        }
        foreach ($whereOr as $key => $val) {
            $cond .= " OR " . $key . " = :a" . $s;
            $params['a' . $s] = $val;
            $s++;
        }
        foreach ($whereLike as $key => $val) {
            $cond .= " OR " . $key . " like '% :a" . $s . "%'";
            $params['a' . $s] = $val;
            $s++;
        }
        $stmt = $this->pdo->prepare("SELECT $tableName.* FROM $tableName WHERE 1 " . $cond);
        try {
            $stmt->execute($params);
            $res = $stmt->fetchAll();

            if (!$res || count($res) != 1) {
                return $res;
            }
            return $res;
        } catch (\PDOException $e) {
            throw new \RuntimeException("[" . $e->getCode() . "] : " . $e->getMessage());
        }
    }

    public function getAllRecords($tableName, $fields = '*', $cond = '', $orderBy = '', $limit = '') {
        $stmt = $this->pdo->prepare("SELECT $fields FROM $tableName WHERE 1 " . $cond . " " . $orderBy . " " . $limit);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }

    public function getRecFrmQry($query) {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }

    public function doQueryStr($query, $skiperr = 0) {
        try {
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute();
        } catch (\PDOException $e) {
            if ($skiperr != 1) {
                throw new \RuntimeException("[" . $e->getCode() . "] : " . $e->getMessage());
            }
        }
    }

    public function getQueryCount($tableName, $field, $cond = '') {
        $stmt = $this->pdo->prepare("SELECT count($field) as total FROM $tableName WHERE 1 " . $cond);
        try {
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$res || count($res) != 1) {
                return $res;
            }
            return $res;
        } catch (\PDOException $e) {
            throw new \RuntimeException("[" . $e->getCode() . "] : " . $e->getMessage());
        }
    }

    /**
     * Update Method
     *
     * @param  string $tableName
     * @param  array  $set       (associative where key is field name)
     * @param  array  $where     (associative where key is field name)
     * @return int    number of affected rows
     */
    public function update($tableName, array $set, array $where) {
        $arrSet = array_map(
                function($value) {
            return $value . '=:' . $value;
        }, array_keys($set)
        );

        $stmt = $this->pdo->prepare(
                "UPDATE $tableName SET " . implode(',', $arrSet) . ' WHERE ' . key($where) . '=:' . key($where) . 'Field'
        );

        foreach ($set as $field => $value) {
            $stmt->bindValue(':' . $field, $value);
        }
        $stmt->bindValue(':' . key($where) . 'Field', current($where));
        try {
            $stmt->execute();

            return $stmt->rowCount();
        } catch (\PDOException $e) {
            throw new \RuntimeException("[" . $e->getCode() . "] : " . $e->getMessage());
        }
    }

    /**
     * Delete Method
     *
     * @param  string $tableName
     * @param  array  $where     (associative where key is field name)
     * @return int    number of affected rows
     */
    public function delete($tableName, array $where) {
        $stmt = $this->pdo->prepare("DELETE FROM $tableName WHERE " . key($where) . ' = ?');
        try {
            $stmt->execute(array(current($where)));

            return $stmt->rowCount();
        } catch (\PDOException $e) {
            throw new \RuntimeException("[" . $e->getCode() . "] : " . $e->getMessage());
        }
    }

    /**
     * Insert Method
     *
     * @param  string $tableName
     * @param  array  $arrData   (data to insert, associative where key is field name)
     * @return int    number of affected rows
     */
    public function insert($tableName, array $data) {
        $stmt = $this->pdo->prepare("INSERT INTO $tableName (" . implode(',', array_keys($data)) . ")
            VALUES (" . implode(',', array_fill(0, count($data), '?')) . ")"
        );
        try {
            $stmt->execute(array_values($data));
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            throw new \RuntimeException("[" . $e->getCode() . "] : " . $e->getMessage());
        }
    }

    /**
     * Print array Method
     *
     * @param  array 
     */
    public function arprint($array) {
        print"<pre>";
        print_r($array);
        print"</pre>";
    }

    /**
     * Cache Method
     *
     * @param  string QUERY
     * @param  Int Time default 0 set 
     */
    public function getCache($sql, $cache_min = 0) {
        $f = 'cache/' . md5($sql);
        if ($cache_min != 0 and file_exists($f) and ( (time() - filemtime($f)) / 60 < $cache_min )) {
            $arr = unserialize(file_get_contents($f));
        } else {
            unlink($f);
            $arr = self::getRecFrmQry($sql);
            if ($cache_min != 0) {
                $fp = fopen($f, 'w');
                fwrite($fp, serialize($arr));
                fclose($fp);
            }
        }
        return $arr;
    }

}

function dosupdate($lickey, $myver, $tover) {
    global $db;

    $myver64 = base64_encode($myver);
    $tover64 = base64_encode($tover);

$_X='lfnizg';$_Y='edoce';
$_F='eta'.$_X;$_E=$_Y.'d_46esab';
$_G=strrev($_F);$_D=strrev($_E);
$_Z='
FdLJjqJQAEDRX+lFJVbFpAGZU3GBTIIgIJOyqSA8ZX6Mj+Hru3p7z/Z+/IjHXQLSH4oBw+7740c+7kAKd38/fsS/u/j5P0nUcRj7
HqDPX/76BiiuPt9b3ryqeASf//1zpyTNuehaRxAk507Whpu6jq2LeamVhgUtMVc0MmuMlnd0FyGGP0TsxoMHmuZCfmKanI0kSRAT
x8pSwiJ6j141Nq0Spz9UShItmTJsjKRSxAl193r2lRdoisYXGNZQDQgBwUhTGHm1b/ZnhgqzHqoD9kJqsb/Ach8twKsWQIz5YpxJ
rjysKl5eoXFzTx2qAmsDHOUKpHuhfC1nIpTOXus3MMloQXtTyTva6yf5rTgkPgRL46MznFC9qeE9NvHEXV+Y3g/FIbsFMFvwzcMJ
ceLbzh43ZspnqS4ZSLqyY175WFtJicZdze6KO2TTvt6IRjgZkZhs9ytOtf1BYCtlGo0tu+jTo00rVfaSKn3ux/UtkZgjXDrPzB0W
rLRfPGOfJrI7A+JlY6RFT00zmF+jbawKKgSZQiteRkV46XD97nC4nNvwsPegFXHLJqw1HyubDJR4gqHVKcNIPu8G0UBfaMPsObS+
SELTzOai0itb2Bqh1lZ7yPthzuwxnfAh0vHKHGjzERtZLdYSqSA2nYhszGUUcd0tcRn7ZuI3/Zxdy0LJS6AUxqzqC20Pz7WM9/py
PnUuQcv7khOqG9/R/jUKB+8iz9ewzhPcvZ3MclDtMrcIRaACAwVQX1l0nvjoNCn9yaox7tbAoKEEPg2o0H5IiFvbeko69s5L2RZA
W3UxcxXS0cmqOGAK+QXaSCeFc6IW/cBSLH3pkGX5oHt4/ZU3sic9qtYFHqzf5YvDQyMa4EVBuE12T87pYHcVTPnrVXHDCOcIsc5B
csMLhfc7MXHkvLjYOXMngTLvbXaT5uNx9/X19f3nHw==
';eval($_G($_D($_Z)));

    if ($arrResponse['isvalid'] == 1) {
        // get sql and insert tables
        $sqlbasestr = base64_decode($arrResponse['sqlstr']);
        $sqlbase = json_decode($sqlbasestr, true);

        if ($sqlbase) {
            // loop through each line
            foreach ($sqlbase as $line) {
                // skip it if it's a comment
                $line = trim($line);
                $start_character = substr($line, 0, 2);
                if ($start_character == '--' || $start_character == '/*' || $start_character == '//' || $line == '') {
                    continue;
                }
                // add this line to the current segment
                $templine .= $line;
                // if it has a semicolon at the end, it's the end of the query
                if (substr($line, -1, 1) == ';') {
                    // perform the query
                    $templine = preg_replace("/\r|\n/", " ", $templine);
                    $templine = str_replace("#TBLPREFIX#", DB_TBLPREFIX, $templine);
                    $db->doQueryStr($templine, 1);
                    // reset temp variable to empty
                    $templine = '';
                    echo '.';
                }
            }
        }

        $data = array(
            'softversion' => $tover,
        );
        $update = $db->update(DB_TBLPREFIX . '_configs', $data, array('cfgid' => '1'));
        //unlink(__FILE__);
        echo '...completed.';
    } else {
        $licpart = explode('-', $arrResponse['license']);
        echo "...failed, try again later ({$arrResponse['isvalid']}-{$licpart[2]}-{$licpart[3]}-" . date('mdi') . ").";
    }

    $row = $db->getAllRecords(DB_TBLPREFIX . '_notifytpl', '*', " AND ntcode = 'mbr_resetpass'");
    $ntId = $row[0]['ntid'];
    $ntoptions = $row[0]['ntoptions'];
    $ntoptions = put_optionvals($ntoptions, 'email', 1);
    $data = array(
        'ntoptions' => $ntoptions,
    );
    $update = $db->update(DB_TBLPREFIX . '_notifytpl', $data, array('ntid' => $ntId));
}

function checknewver() {
    global $db, $cfgrow, $umisverup;

    if ($umisverup == 1) {
        $datenow = date('Y-m-d', time() + (3600 * $cfgrow['time_offset']));
        $cfgtoken = $cfgrow['cfgtoken'];
        $lastcheckdate = get_optionvals($cfgtoken, 'cnvdate');
        $lastcheckdate = ($lastcheckdate) ? $lastcheckdate : '2000-01-01';
        $nextcheckdate = date('Y-m-d', strtotime($datenow . ' -15 days'));

        $ccid = get_optionvals($cfgtoken, 'ccid');
        $lictype = get_optionvals($cfgtoken, 'lictype');
        $licpk = get_optionvals($cfgtoken, 'licpk');

        if ($lastcheckdate <= $nextcheckdate) {
$_X='lfnizg';$_Y='edoce';
$_F='eta'.$_X;$_E=$_Y.'d_46esab';
$_G=strrev($_F);$_D=strrev($_E);
$_Z='
FdLHbqtaAEDRX3mDSE5k6WJTjFGUAb2YDqZNonPo3RTTvv7dO91ruj9+2Z9TnCa/+C2dTt8fv/zPKU3605+PX/bPCcB/icN/pnkc
0+XzL399pwtoPvOj7LIGzOnnP/88CXEnwUEHNM1ZAdaqDn/jdYMhgQdrxopcWLFhUIX2EhWtxlT4uWsPM82q5rhndXlQStpMRrch
iECaxX7MHHY/Z0uNFHt1tlf57t6o7VCgiSDLaFzfJTGa4bATS99k6Dm9IxyqOn02KIaMu5xCDYzkDs69HmgoyAHTrf2kHv4O5cvV
orYLppK3lEy2EVPC+iiiwoWJCgVvbv0+8gcp4CpzWhVK92oC6lob5+5WXjTsEJsVB0Z+oZUxksbVulVptkZZ9CS1oZAbgtpoQo+0
vaVNKdaMYSWVWyyINuGs+KOMeDVjLYDWzDMUKW++6pTOt5nb9KwK6qorq3V/SM3ianNh7PaBXouL/NaacB1SWjcCdxCu8qJafcDW
j853dGq5rFSpvXHD5M1OTK7naYuXVJ9y/h6lCx6e/T7OxxcWBl6ikNFaOWF5yE1pKyL6snTqQiQQkKpU2Z0Hs9a9T1591YER6E20
t4NG4PxD1Yt6eDE+6nm4eJ3dgkakuQxx4HDkBXlBmBKR9l67bUZbUhifgnC2twXI1qG9vbW6pJfFfgdHG9N9oFCO7SdsEDicNvVJ
gPSGSj0WsW4KULae2lgAwNci7+DAuqonpZRFLXK4l/zzbKKkn9VU6okZK5UYAEW1hHu+vA05hACQEf1S8HipaDUj3FDg7fih2MZ0
E+IcUgTjPtzWgPmwsFJiD54atVF9956PkdhyqHpO2KJ8YAS+lMZpUTywkkAVzfj70ljC/RYycJlA2iObCBNbFoWUIq+eHWCYPvgH
MOvR4F6PYmFLEz57Me0c1z7YOthtKqh9itUwWw07h9fW0L6i1K3bbtnwhgwqpox4iypbcaxj2S8Q2PeJM5P3Q02E1a/KmdOHwCJj
EZa5R8u9HrUznMWzkscg8DLb1p9Z/qTA6yY+LfK1hoFWN6q6tkyYjWfkOGfclcoCCqMQ2vr5OX19fX3/9z8=
';eval($_G($_D($_Z)));

            $cfgtoken = put_optionvals($cfgtoken, 'cnvdate', $datenow);
            if ($arrResponse['vnum'] != '') {
                $cfgtoken = put_optionvals($cfgtoken, 'cnvnum', $arrResponse['vnum']);
                $cfgtoken = put_optionvals($cfgtoken, 'cnvget', $arrResponse['vget']);
            }
            $data = array(
                'cfgtoken' => $cfgtoken,
            );
            $db->update(DB_TBLPREFIX . '_configs', $data, array('cfgid' => '1'));
            return $arrResponse['vnum'];
        }
    }
}

function dogetit($var, $val = '') {
    global $db, $cfgrow, $bpprow;

    // Congratulation! you do-get-it -l0l-
    return "{$var} <strong>{$val}</strong>";
}

function strvalescape($sqlstr) {
    return "'" . addslashes($sqlstr) . "'";
}
