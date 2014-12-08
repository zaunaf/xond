<?php

use Symfony\Component\Security\Acl\Exception\Exception;

/////////

ini_set('display_errors', 'On');
error_reporting(-1);

/*
function shutdown_handler() {
    if(@is_array($error = @error_get_last())) {   
        //die('error nying');
        //throw new Exception($error['message'], $error["type"]);
        //throw new Exception('Division by zero.');
    }
    return(true);
}

register_shutdown_function('shutdown_handler');
*/
function isFloat($val) {
    $pattern = '/^[+-]?(\d*\.\d+([eE]?[+-]?\d+)?|\d+[eE][+-]?\d+)$/';
    return preg_match($pattern, trim($val));
}

function roundUp($input, $nearest) {
    return ceil($input / $nearest) * $nearest;
}

/**
 * Get the base classname without namespaces
 * 
 * @param object $object
 * @return string
 */
function getBaseClassName($object){
    $reflect = new ReflectionClass($object);
    return (string) $reflect->getShortName();
}

/**
 * Return 1 on true otherwise 0
 * 
 * @param bool $bool
 * @return number
 */
function boolToNum($bool) {
    return $bool ? 1 : 0;
}

/**
 * Check if any of the contents of the array starts with the string
 *  
 * @param string $str
 * @param array $arr
 * @return boolean
 */
function containsStarts($str, array $arr) {
    
    foreach($arr as $a) {
        if (startsWith($str, $a) !== false) return true;
    }
    return false;
    
}

/**
 * Check if any of the contents of the array ends with the string
 *
 * @param string $str
 * @param array $arr
 * @return boolean
 */
function containsEnds($str, array $arr) {
    foreach($arr as $a) {
        if (endsWith($a, $str) !== false) return true;
    }
    return false;
}

/**
 * Check wherter the string $haystack starts with the string $needle
 * stolen from http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
 *
 * @param string $haystack
 * @param string $needle
 * @return boolean
 */
function startsWith($haystack, $needle)
{
    //echo "searching $needle in  $haystack<br>\r\n";
    return $needle === "" || strpos($haystack, $needle) === 0;
}

/**
 * Check wheter the string $haystack ends with the string $needle
 * stolen from http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
 * 
 * @param string $haystack
 * @param string $needle
 * @return boolean
 */
function endsWith($haystack, $needle)
{
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

/**
 * Check whether any of the items in the array contains any string $str
 * 
 * @param string $str
 * @param array $arr
 * @return boolean
 */
function contains($str, array $arr)
{
    foreach($arr as $a) {
        if (stripos($str,$a) !== false) return true;
    }
    return false;
}

/**
 * Get what OS the server currently using in human readable string
 * 
 * @return string
 */
function getOs() {

    $phpOs = strtoupper(substr(PHP_OS, 0, 3));

    switch ($phpOs) {
        case 'WIN':
            $os = "Windows";
            break;
        case 'LIN':
            $os = "Linux";
            break;
        case 'DAR' :
            $os = "Mac";
            break;
    }

    return $os;

}
    
/**
 * Recurse copy of anything inside path $source to the destination path $dest
 * 
 * Stolen from somewhere
 * 
 * @param string $source
 * @param string $dest
 * @throws Exception
 * @return boolean
 */
function recurse_copy($source, $dest)
{
    // Check for symlinks
    if (is_link($source)) {
        return symlink(readlink($source), $dest);
    }

    // Simple copy for a file
    if (is_file($source)) {
        return copy($source, $dest);
    }

    // Make destination directory
    if (!is_dir($dest)) {
        mkdir($dest);
    }

    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep copy directories
        if (!recurse_copy("$source/$entry", "$dest/$entry")) {
            throw new Exception("failed copying $entry");
        }
    }

    // Clean up
    $dir->close();
    return true;
}

/**
 * Synchronization shit
 * 
 * @param unknown $last_sync
 * @return unknown
 */
function getLastUpdate($last_sync) {
    
    $lastSync = ($last_sync) ? strtotime($last_sync) : strtotime('2013-01-02 00:00:00');
    $lastSyncPlus1Hour = strtotime("+1 hour", $lastSync);
    $lastUpdate = date('Y-m-d H:i:s', $lastSyncPlus1Hour);
    
    return $lastUpdate;
}

/**
 * Generate UUID v1. Calling the UUID::mint from the class below
 * 
 * @param number $returnInvalid
 * @return UUID
 */
function getUuid($returnInvalid=0) {
    return UUID::mint(1);
}

/**
 * Generates random string
 * 
 * @param number $length
 * @param number $returnInvalid
 * @return string
 */
function getRandomString($length=1, $returnInvalid=0) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * Generates random number
 * 
 * @param number $length
 * @param number $min
 * @param number $max
 * @param number $returnInvalid
 * @return unknown
 */
function getRandomNumber($length=0, $min=0, $max=9, $returnInvalid=0) {
    $number = mt_rand($min, $max);  
    $number = ($length > 0) ? substr(strval($number), 0, $length) : $number;
    return $number;
}

/**
 * Generates random date from given range
 * 
 * @param string $start_date
 * @param string $end_date
 * @param number $returnInvalid
 */
function getRandomDate($start_date='1980-01-01', $end_date='2019-12-31', $returnInvalid=0) {

    // Convert to timetamps
    $min = strtotime($start_date);
    $max = strtotime($end_date);

    // Generate random number using above bounds
    $val = rand($min, $max);

    // Convert back to desired date format
    return date('Y-m-d', $val);
}

/**
 * Generates random gender in single characters: L (Male) P (female)
 * 
 * @return string
 */
function getRandomJk() {
    $randomBoolean = .01 * rand(0, 100) >= .5;
    return $randomBoolean ? "L" : "P";
}

/**
 * Check if the given email is valid 
 * 
 * @param string $email
 * @return boolean
 */
function validate_email($email="test@test.com") {
    if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)) {
        //echo "Valid email address.";
        return true;
    } else {
        //echo "Invalid email address.";
        return false;
    }
}

/**
 * Class autoloading (don't know if we still need the shit after composer)
 * 
 * @deprecated
 * @param unknown $class_name
 */
function class_autoloader($class_name) {

    if ($class_name == "FPDF") {    
    
    } else if ($class_name == "Realisasi") {

    } else {
        $class_file = str_replace('Xond', '', $class_name);
        require_once $class_file.'.php';
    }
}

/**
 * PHP doesn't have println. Imagine that.
 * 
 * @param string $str
 */
function println($str) {
    echo "$str\n";
}

/**
 * Decode encoded MAK. Used in an ancient app long time ago.
 * 
 * @deprecated
 * @param unknown $str
 * @param unknown $noitem
 * @return Ambigous <string, number>
 */
function decode_mak($str, $noitem) {
    $kdmak = "";
    $decoder = array('[', '|', ']', ';', '<', '=', '>', '?', '@', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 
                     'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
    for ($j = 0; $j <=5; $j++) {
        //$kdmak .= (array_search(substr($str,$j-1,1), $decoder) - $j - ($noitem % 20))."|";
        $kdmak .= array_search(substr($str,$j,1), $decoder) - $j - ($noitem % 20);//."|";
    }
    return $kdmak;
}

/**
 * This is a very ugly Object Collection to Json function, but probably 
 * have been used thousands times per day by people all around Indonesia :)
 * 
 * @param array $array
 * @param int $rownum
 * @param array $id
 * @param string $start
 * @param string $limit
 * @return json
 */
function tableJson($array, $rownum, $id, $start="0", $limit="20") {

    $rows = sizeof($array) > 0 ? json_encode($array) : "[]";
    $result = sprintf("{ 'results' : %s, 'id' : '%s', 'start': %s, 'limit': %s, 'rows' : %s }", 
    $rownum, $id[0], $start, $limit, $rows);
    
    return $result;
} 

/**
 * Simpler version of tableJson(). Still can't figure out the difference
 * 
 * @param array $array
 * @param int $rownum
 * @param array $id
 * @param string $start
 * @param string $limit
 * @return json
 */
function tableJsonSimple(array $array, int $rownum, array $id, $start="0", $limit="20") {
    //print_r($array); die();
    $rows = sizeof($array) > 0 ? json_encode($array) : "[]";    
    $result = sprintf("{ 'results' : %s, id : '%s', 'start': %s, 'limit': %s, 'rows' : %s }", 
                       $rownum, $id[0], $start, $limit, $rows);
    return $result;
} 
/**
 * Convert an array of objects to array of associative arrays.
 * Making it easier to deal with
 * 
 * @param array $objArray
 * @param int $type
 * @return multitype:
 */
function getArray(array $objArray, $type=BasePeer::TYPE_PHPNAME) {
    $outArr = array();  
    foreach ($objArray as $o) {
        array_push($outArr, $o->toArray($type));
    }
    return $outArr;
}

/**
 * Check if the $char is upper case.
 * Don't know where i used this
 * 
 * @param string $char
 * @return boolean
 */
function isUpperCase($char)
{
  if(ord($char)>64 && ord($char)<91)
  {
    return TRUE;
  }
  elseif(ord($char)>96 && ord($char)<123)
  {
    return FALSE;
  }
  else
  {
    return FALSE;
  }
} 

/**
 * Check if the $char is lower case.
 * Don't know where i used this
 *
 * @param string $char
 * @return boolean
 */
function isLowerCase($char)
{
  if(ord($char)>64 && ord($char)<91)
  {
    return FALSE;
  }
  elseif(ord($char)>96 && ord($char)<123)
  {
    return TRUE;
  }
  else
  {
    return FALSE;
  }
} 

/**
 * Very handy converter from any string format to
 * underscore and capitalized format, i.e ABAH_SUKASEP
 * 
 * @param string $string
 * @return string
 */
function underscoreCapitalize($string) {        
    
    //$string;
    //$pattern = '/[A-Z]/';
    //$replacement = '_${0}';
    $i = 0;
    //$lastChar = " ";
    $lastChar = ""; $out = "";
    
    foreach (str_split($string) as $c) {
        //if ($i == 1) {
        if (isLowerCase($lastChar) && isUpperCase($c))
            $out .= "_".$c;
        else
            $out .= $c;
        $lastChar = $c;     
    }
    //return strtoupper(substr(preg_replace($pattern, $replacement, $string), 1));
    return strtoupper($out);    
    
} 

/**
 * Very handy converter from any string format to
 * php name format, i.e AbahSukasep
 * 
 * @param string $string
 * @return string
 */
function phpNamize($string) {        
    $rest = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    return $rest; 
}

/**
 * Very handy converter from any string format to
 * human readable title format, i.e Abah Sukasep
 *
 * @param string $string
 * @return string
 */
function humanize($string) {        
    $string = underscoreCapitalize($string);
    $rest = ucwords(strtolower(ucwords(str_replace('_', ' ', $string))));
    return $rest;    
} 

/**
 * Very handy converter from any string format to
 * human readable sentence format, i.e Abah sukasep
 *
 * @param string $string
 * @return string
 */
function sentencesize($string) {
    $string = underscoreCapitalize($string);
    $rest = ucfirst(strtolower(ucfirst(str_replace('_', ' ', $string))));
    return $rest;    
}

/**
 * Splitter for incoming json formatted array
 * 
 * @param string $jsonText
 * @return array
 */
function splitJsonArray($jsonText) {
    
    $j = $jsonText;
    $arr = explode("},{", $j);
    $size = sizeof($arr);
    for ($i=0; $i<$size; $i++) {
        if (($i==0) || ($i == ($size-1))) {
            $arr[$i] = str_replace('[{','{', $arr[$i]);
            $arr[$i] = str_replace('}]','}', $arr[$i]);
        } 
        if (($arr[$i][0] != "{")) {                
            $arr[$i] = "{".$arr[$i];    
        }
        $lastOffset = strlen($arr[$i])-1;
        if ($arr[$i][$lastOffset] != "}") {
            $arr[$i] = $arr[$i]."}";
        }
        
    }
    return $arr;
}

function getNomorUrutBaru() {
    //$c = new Criteria(); $c->add();
}

/**
 * Getting Propel's Peer object for the given tblName
 * 
 * @param string $tblName
 * @return BasePeer
 */
function getPeer($tblName){    
    //echo $tblName; die;
    $peerName = $tblName."Peer";
    //$obj = new ${tblName}();
    //$p = $obj->getPeer();
    $p = new ${'peerName'}();
    return $p;
}

/**
 * Vintage function, extracting array of table and column.
 * for CRUD purpose in Xond 1
 * 
 * @param string $table
 * @return multitype:number Ambigous <number,>
 */
function getTableColumn($table) {
    $tbl['name'] = $table;
    $tableClass = new ${table}();       
    $columns = $tableClass->getPeer()->getTableMap()->getColumns();
    $tbl['totalWidth'] = 0;
    foreach ($columns as $column) {
        $col[$i]['realname'] = strtolower($column->getColumnName());
        $col[$i]['name'] = $column->getPhpName();
        $size = $column->getSize();
        switch ($column->getType()) {
            case 'INTEGER' :
                $type = 'float';
                $col[$i]['size'] = $size ? $size : 10;
                break;
            case 'BIGINT' :
                $type = 'float';
                $col[$i]['size'] = $size ? $size : 80;
                $col[$i]['format'] = 'idMoney';
                break;
            case 'VARCHAR' :
                $type = 'string';
                $col[$i]['size'] = $size ? $size : 100;
                break;
            case 'DATE' :
                $type = 'date';
                $col[$i]['size'] = $size ? $size : 50;
                $col[$i]['format'] = 'date';
                break;
            default:
                $type = 'string';
                break;
        }
        $col[$i]['type'] = $type;
        if ($column->isPrimaryKey()) {
            $col[$i]['isPkey'] = true;
            $col[$i]['size'] = 10;
            $tbl['pkey'] = $column->getPhpName();
        }                               
        if ($column->isForeignKey()){
            $col[$i]['isFkey'] = true;
            $relTableName = XondComponent::convertCase($column->getRelatedTableName());
            $relTableClass = new ${relTableName}();
            $col[$i]['fkTbl'] = $relTableName;
            $col[$i]['fkCol'] = convertCase($column->getRelatedColumnName());
            $col[$i]['fkDsp'] = $relTableClass->getNama();              
            $tbl['fkeys'][] = $relTableName;
        }               
        $i++;
        $tbl['totalWidth'] += $column->getSize();
    }
    if (method_exists($tableClass, "getNama"))
      $tbl['dsp'] = $tableClass->getNama();
    $i = 0;
    return array($tbl, $col);
} 

/**
 * Similar with -ize functions above
 * 
 * @param unknown $str
 * @return unknown
 */
function convertCase($str){
    $str = mb_ereg_replace("_", " ", $str);
    $str = mb_convert_case($str, MB_CASE_TITLE, "UTF-8");
    $str = mb_ereg_replace(" ", "", $str);
    return $str;
}

/**
 * Encode list of values to a json array
 * @param array $array
 * @return string
 */
function encodeArray($array) {
    $i = 1;
    $s = '[';
    foreach ($array as $a) {
        if ($i > 1) $s .= ",";
        $s .= "$a";
        $i++;
    }
    $s .= ']';
    return $s;
}

/**
 * Split incoming json formatted array to php array
 * @param string $text
 * @return array
 */
function splitArray($text) {
    $text = str_replace(array('[', ']'), '', $text);
    $array = split(',', $text);
    return $array;
} 

/**
 * Prepend with zeroes
 * 
 * @param int $number
 * @param int $digits
 * @return string
 */
function addZeroes($number, $digits) {
    return str_repeat('0',$digits - strlen($number)).$number;
}


/**
 * Part of a very handy number to language text convertion in Indonesian.
 * This one get the hundreds part.
 * 
 * Originally coded by me
 * 
 * @param int $number       the number passed
 * @param boolean $ribu     is this ribuan?
 * @return string           returns dozens
 */
function getRatus(int $number, $ribu=false) {
    //echo $number."|";
    $number = strval(intval($number));
    //echo "($number)-[".strlen($number)."]";
    //$number = (strlen($number) == 2) ? "".$number : (strlen($number) == 1)  ? "00".$number : $number;
    if (strlen($number) == 2)
        $number = "0".$number;
    else if (strlen($number) == 1)
        $number = "00".$number; 
    //echo "-($number)";
    if ($number == "000")
        return "";
    $huruf = "";
    $belas = false;
    
    for ($i=0; $i<3; $i++) {
        $n = substr($number, $i, 1);
        switch ($n) {
            case "1" :              
                //$h = ($i == 0) ? 'se' : ($i == 2) ? 'satu' : '';              
                $h = ($i == 0) ? 'se' : (($i == 2) ? 'satu' : '');
                $h = ($ribu) ? 'se' : $h;               
                if ($i == 1) $belas = true;
                break;
            case "2" : 
                $h = 'dua';
                break;
            case "3" : 
                $h = 'tiga';
                break;
            case "4" : 
                $h = 'empat';
                break;
            case "5" : 
                $h = 'lima';
                break;
            case "6" : 
                $h = 'enam';
                break;
            case "7" : 
                $h = 'tujuh';
                break;
            case "8" : 
                $h = 'delapan';
                break;
            case "9" :          
                $h = 'sembilan';
                break;
            default :
                $h = "";
                break;              
        }
        //echo "($i)($n)$h|";
        if ($h == 'se') {
            $spasi = "";                     
        } else {
            $spasi = " ";
        }
        
        $spasi2 = ($ribu) ? '' : ' ';
        
        switch ($i) {
            case 0 : 
                $sat = (intval($n) > 0) ? 'ratus' : ""; 
                break;
            case 1 : 
                $sat = (intval($n) > 1) ? "puluh" : "";
                break;
                //$sat = (intval($n) == 0) ? "" : ($belas) ? "" : "puluh";
                //$sat = (intval($n) > 0) ? ($belas) ? "" : "puluh" : ""; break;
            case 2 : 
                if ($belas) {                   
                    switch ($n) {
                        case '0' : 
                            $sat = "sepuluh";
                            $h = "";
                            break;
                        case '1' :
                            $sat = "sebelas";
                            $h = "";
                            break;
                        default :
                            $sat = "belas";
                            break;
                    }                   
                } else {
                    $sat = "";
                }
                /*
                if ($belas && ($n == '0')) {
                    $sat = "sepuluh";
                    $h = "";
                } else if ($belas && ($n == '1')) {
                    $sat = "sebelas";
                    $h = "";
                } else if ($belas && (($n != 1) && ($n != 0)))   {
                    $sat = "belas";                 
                }
                */
                //$sat = ($belas && ($n == '0')) ? "sepuluh" : ($belas && ($n == '1')) ? "sebelas" : ($belas && (($n != 1) && ($n != 0))) ? "belas" : "";
                //$h = ($belas && ($n == '1')) ? "" : $h;
                break;
        }
        //$spasi = ($belas) ? "" : " "; 
        $huruf .= $h.$spasi.$sat.$spasi2;
        //echo "($i)($n)$h$sat|";
    }
    return $huruf;
}

/**
 * Part of a very handy number to language text convertion in Indonesian.
 * This one is the main function.
 * @param int $number
 * @return string
 */
function getHuruf(int $number) {
    //$number = strval(intval($number));    
    if ($number == 0) {
        return "nol";
    }
    if ((strlen($number) % 3) == 2) {
        $number = "0".$number;
    } else if ((strlen($number) % 3) == 1) {
        $number = "00".$number;
    }
    
    $e3 = strlen($number)/3;
    $huruf = "";
    for ($i=1; $i<= $e3; $i++) {
        //echo substr($number, (-3*$i), 3)."|";     
        $numberPart = substr($number, (-3*$i), 3);
        if ($i == 2 && (intval($numberPart) == 1)) {
            $ribu = true; 
        } else {
            $ribu = false;
        }
        $ratus = getRatus($numberPart, $ribu);
        //echo $ratus;
        switch ($i) {
            case 2 : $sat = "ribu"; break;
            case 3 : $sat = "juta"; break;
            case 4 : $sat = "milyar"; break;
            case 5 : $sat = "trilyun"; break;
        }       
        $arr[$i]["ratus"] = $ratus;
        $arr[$i]["sat"] = ($ratus != "") ? $sat : "";
    }
    for ($j= sizeof($arr); $j>=1; $j--) {
        $huruf .= $arr[$j]["ratus"].$arr[$j]["sat"]." ";        
    }
    
    return $huruf;
}

/**
 * Shortcut to execute SQL through Propel
 * 
 * @param string $sql
 * @param boolean $dbname
 * @return boolean
 */
function executeSql($sql, $dbname=false) {
    if (!$dbname) {
        $con = Propel::getConnection($dbname);
    } else {
        $con = Propel::getConnection(DBNAME);
    }
    $stmt = $con->prepare($sql); 
    try {
        $stmt->execute(); 
        return true;
    } catch (Exception $e) {
        return false;
    }   
}

/**
 * Shortcut to execute MySQL query
 *
 * @param string $sql
 */
function executeMysql($sql) {

    if (!$link = mysql_connect('mysql_host', 'mysql_user', 'mysql_password')) {
        throw new Exception('Could not connect to mysql');
        exit;
    }

    if (!mysql_select_db('mysql_dbname', $link)) {
        throw new Exception('Could not select database');
        exit;
    }

    //$sql    = 'SELECT foo FROM bar WHERE id = 42';
    $result = mysql_query($sql, $link);

    if (!$result) {
        //echo "DB Error, could not query the database\n";
        throw new Exception('MySQL Error: ' . mysql_error());
        exit;
    }

    while ($row = mysql_fetch_assoc($result)) {
        $arr[] = $row;
    }

    mysql_free_result($result);

}

/**
 * Shortcut to get single value data from SQL through Propel
 * 
 * @param string $sql
 * @return mixed
 */
function getValueBySql($sql) {
    
    $con = Propel::getConnection(DBNAME);   
    $stmt = $con->prepare($sql); 
    $stmt->execute(); 
    $res = $stmt->fetch(PDO::FETCH_NUM);    
    return $res[0];
    
}

/**
 * Shortcut to get associative array of data from SQL through Propel
 * 
 * @param string $sql
 * @param boolean $remove_keys
 * @param string $dbName
 * @return array
 */
function getDataBySql($sql="", $remove_keys=FALSE, $dbName=DBNAME) {
    
    $con = Propel::getConnection($dbName);
    $stmt = $con->prepare($sql); 
    $stmt->execute(); 
    if ($remove_keys) {
        $result = $stmt->setFetchMode(PDO::FETCH_NUM);      
        $res = array();
        while ($row = $stmt->fetch()) {
            $res[] = $row;
        }       
    } else {        
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);        
        $res = $stmt->fetchAll();       
    }
    
    return $res;
}

/**
 * A handy code to get Indonesian month
 * @param int $number
 * @return string
 */
function getbulan($number) {
    $bulan = array (
            '1' => 'Januari',
            '2' => 'Februari',
            '3' => 'Maret',
            '4' => 'April',
            '5' => 'Mei',
            '6' => 'Juni',
            '7' => 'Juli',
            '8' => 'Agustus',
            '9' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
            );

    $bul = $bulan[$number];
    return $bul;
}

/**
 * Number formatter with thousands separator
 * 
 * @param int $number
 * @return string
 */
function nf(int $number) {
    return number_format($number, 0, '', '.');
}

/**
 * Number formatter with percent
 * 
 * @param int $number
 */
function percent(int $number) {
    return number_format($number, 4, '.', '.'); 
}

/**
 * Number formatter otherwise blank space
 * 
 * @param int $number
 * @return string
 */

function nfOrSpace(int $number) {
    return ($number > 0) ? number_format($number, 0, '', '.') : "&nbsp";
}

/**
 * Number formatter with percent mark
 * 
 * @param int $number
 * @return string
 */
function pct(int $number){
    return number_format($number*100, 2, ',', '.').'%';
}

/**
 * Getting the peer constant value from variable 
 * 
 * @param string $ormName
 * @param string $constantName
 */
function getPeerConstant($ormName=" ", $constantName=" ") {
    return constant($ormName."Peer::".$constantName);
}

/**
 * Handy simple converter from array to html formatted table
 *  
 * @param array $array
 * @return string
 */
function arrayToHtmlTable(array $array) {
    $str = "<table border='1'>";
    $i = 0;
    foreach ($array as $a) {
        
        if ($i == 0) {
            $str .= "<tr>"; 
            foreach ($a as $key=>$val) {
                $str .= "<td>$key</td>";    
            }
            $str .= "</tr>";            
        }
        
        $str .= "<tr>";
        foreach ($a as $key=>$val) {
            $str .= "<td>$val</td>";    
        }
        $str .= "</tr>";
        $i++;
        
    }   
    return $str;
}

/**
 * Handy simple converter from array to html formatted table
 * with blue color (css separated)
 *
 * @param array $array
 * @return string
 */
function arrayToHtmlTableBlue($array) {
    $str = "<table>";
    $i = 0;
    foreach ($array as $a) {
        
        if ($i == 0) {
            $str .= "<tr>"; 
            foreach ($a as $key=>$val) {
                $str .= "<th>$key</th>";    
            }
            $str .= "</tr>";            
        }
        
        $str .= "<tr>";
        foreach ($a as $key=>$val) {
            if (is_numeric($val)) {
                $align = " align='right'";
            } else {
                $align = "";
            }
            $str .= "<td $align>$val</td>";
        }
        $str .= "</tr>";
        $i++;
        
    }
    $str .= "</table>";
    return $str;
}

/**
 * Date string parsed and returned with space in
 * Indonesian human readable format, i.e 10 Januari 2014
 *  
 * @param string $dateStr
 * @return string
 */
function processDate($dateStr){
    $date = date_parse($dateStr);
    return $date['day']." ".getbulan($date['month'])." ".$date['year'];
}

/**
 * Combined nested set / adjacency list tree.
 * For Xond 1/Extjs 3 Adjacency List tree
 *   
 * @author Abah Z
 *
 */
class Tree
{
    private $data;
    
    public function __construct($data = array())
    {
        $this->data = $data;
    }
    
    public function getNodes()
    {
        return array_values($this->data);
    }
    
    public function hasNode($node_id)
    {
        return isset($this->data[$node_id]);
    }
    
    public function getNode($node_id)
    {
        return $this->data[$node_id];
    }
    
    public function getRandomNode()
    {
        $result = null;
        if (!empty($this->data)) {
            $ids = array_keys($this->data);
            $result = $this->getNode($ids[rand(0, sizeof($ids) - 1)]);
        }
        return $result;
    }
    
    public function add($node, $parent_id = null)
    {
        $node['_id'] = $this->generateId();
        
        if ($parent_id) {
            $parent = &$this->data[$parent_id];
            $this->shiftNodesRight($parent['_rgt']);
            $node['_parent'] = $parent_id;
            $node['_level'] = $parent['_level'] + 1;
            $node['_lft'] = $parent['_rgt'] - 2;
            $node['_rgt'] = $node['_lft'] + 1;
            $parent['_is_leaf'] = false;
            $node['_is_leaf'] = true; 
        }
        else {
            $node['_parent'] = null;
            $node['_level'] = 1;
            $node['_lft'] = $this->getMaxRightPos() + 1;
            $node['_rgt'] = $node['_lft'] + 1;
            $node['_is_leaf'] = true; 
        }
        
        $this->data[$node['_id']] = $node;
    }
    
    public function getParent($node_id)
    {
        $parent = null;
        
        if (
            isset($this->data[$node_id]) && 
            isset($this->data[$this->data[$node_id]['_parent']])
        ) {
            $parent = $this->data[$this->data[$node_id]['_parent']];
        }
        
        return $parent;
    }
    
    public function getChildren($node_id = null)
    {
        $children = null;
        
        if ($node_id)
        {
            $children = array();
            foreach ($this->data as $id => $node) {
                if ($node['_parent'] == $node_id) {
                    $children[] = $node;
                }
            }
        }
        else {
            $children = $this->getRoots();
        }
        
        return $children;
    }
    
    public function getRoots()
    {
        $roots = array();
        
        foreach ($this->data as $id => $node) {
            if ($node['_parent'] === null) {
                $roots[] = $node;
            }
        }
        
        return $roots;
    }
    
    public function getChildrenPaged($node_id = null, $start = null, $limit = null)
    {
        $children = $this->getChildren($node_id);
        if (!$start) {
            $start = 0;
        }
        if (!$limit) {
            $limit = sizeof($children);
        }
        return array_slice($children, $start, $limit);
    }
    
    public function getChildrenCount($node_id = null)
    {
        $children = $this->getChildren($node_id);
        return sizeof($children);
    }
    
    private function generateId()
    {
        return empty($this->data) ? 1 : max(array_keys($this->data)) + 1;
    }
    
    private function getMinLeftPos()
    {
        $result = 0;
        
        if (!empty($this->data)) {
            $values = array();
            foreach ($this->data as $id => $node) {
                $values[] = $node['_lft'];
            }
            $result = min($values);
        }
        
        return $result;
    }
    
    private function getMaxRightPos()
    {
        $result = 0;
        
        if (!empty($this->data)) {
            $values = array();
            foreach ($this->data as $id => $node) {
                $values[] = $node['_rgt'];
            }
            $result = max($values);
        }
        
        return $result;
    }
    
    private function shiftNodesRight($start_from, $offset = 2)
    {
        foreach ($this->data as $id => &$node) {
            if ($node['_lft'] >= $start_from) {
                $node['_lft'] += $offset;
            }
            if ($node['_rgt'] >= $start_from) {
                $node['_rgt'] += $offset;
            }
        }
    }
}

/**
 * Extend the array with another array ? 
 * 
 * @param array $a
 * @param array $b
 * @return array
 */
function array_extend($a, $b) {
    foreach($b as $k=>$v) {
        if( is_array($v) ) {
            if( !isset($a[$k]) ) {
                $a[$k] = $v;
            } else {
                $a[$k] = array_extend($a[$k], $v);
            }
        } else {
            $a[$k] = $v;
        }
    }
    return $a;
}

/**
 * Handy function to format a plain nonspaced number to NPWP
 * 
 * @param string $npwp  non formatted NPWP
 * @return string       formatted NPWP
 */
function formatNpwp($npwp) {
    return substr($npwp,0,2).'.'.   substr($npwp,2,3).'.'.  substr($npwp,5,3).'.'.  substr($npwp,8,1).'-'.  substr($npwp,9,3).'.'.  substr($npwp,12,3);
}

/**
 * Converts 4B to IVB
 * @param int $number
 * @return string
 */
function get_golongan($number) {
    $digit1 = substr($number, 0, 1);
    $digit2 = substr($number, 1, 1);
    
    switch ($digit1) {
        case 1:
            $romawi = "I";
            break;
        case 2:
            $romawi = "II";
            break;
        case 3:
            $romawi = "III";
            break;
        case 4:
            $romawi = "IV";
            break;
    }
    
    switch ($digit2) {
        case 1:
            $abjad = "A";
            break;
        case 2:
            $abjad = "B";
            break;
        case 3:
            $abjad = "C";
            break;
        case 4:
            $abjad = "D";
            break;          
    }
    
    return $romawi.$abjad;
}

/**
 * Strip listed academic degree from a name
 * @param string $name
 * @return string
 */
function stripDegree($name) {
    //$degrees = array ('S.S', 'S.H', 'S.E', 'S.IP', 'S.Sos', 'S.Psi', 'S.Ked', 'S.KM', 'S.KG', 'S.P', 'S.TP', 'S.Pt', 'S.Pi', 'S.Hut', 'S.KH', 'S.Si', 'S.T', 'S.Kom', 'S.Sn', 'S.Pd', 'S.Ag', 'M.Hum', 'M.M', 'M.Si', 'M.Kes', 'M.P', 'M.T', 'M.Kom', 'M.Sn', 'M.Pd', 'M.Ag', 'SS', 'SH', 'SE', 'SP', 'ST', 'MM', 'MP', 'MT', 'Prof.', 'DR.', 'dr.', 'drg.', 'Drs.', 'Dra.', 'PhD', 'Ph.D', 'H.', 'Hj.');
    $degrees = array ('A.Md', 'S.IP', 'S.Sos', 'S.Psi', 'S.Ked', 'S.KM', 'S.KG', 'S.TP', 'S.Pt', 'S.Pi', 'S.Hut', 'S.KH', 'S.Si', 'S.Kom', 'S.Sn', 'S.Pd', 'S.Ag', 'S.S', 'S.H', 'S.E', 'S.P', 'S.T', 'MBA', 'M.Ak', 'M.Hum', 'M.Si', 'M.Kes', 'M.Kom', 'M.Sn', 'M.Pd', 'M.Ag', 'M.M', 'M.P', 'M.T', 'SAP', 'SS', 'SH', 'SE', 'SP', 'ST', 'MM', 'MP', 'MT', 'MA', 'Prof.', 'Dr.', 'Ir.', 'Drs.', 'Dra', 'dr.', 'drg.', 'PhD', 'Ph.D', 'H.', 'Hj.');
    $punctuationMark = array('.', ',');
    $name = str_replace($degrees, "", $name);   
    $name = str_replace($punctuationMark, "", $name);
    return $name;
}

/**
 * Compare two names, is it equal?
 * @param string $name1
 * @param string $name2
 * @return boolean
 */
function compareName($name1, $name2) {
    $gelars = array ('S.IP', 'S.Sos', 'S.Psi', 'S.Ked', 'S.KM', 'S.KG', 'S.TP', 'S.Pt', 'S.Pi', 'S.Hut', 'S.KH', 'S.Si', 'S.Kom', 'S.Sn', 'S.Pd', 'S.Ag', 'S.S', 'S.H', 'S.E', 'S.P', 'S.T', 'M.Hum', 'M.Si', 'M.Kes', 'M.Kom', 'M.Sn', 'M.Pd', 'M.Ag', 'M.M', 'M.P', 'M.T', 'SS', 'SH', 'SE', 'SP', 'ST', 'MM', 'MP', 'MT', 'Prof.', 'DR.', 'Drs.', 'Dra', 'dr.', 'drg.', 'PhD', 'Ph.D', 'H.', 'Hj.');
    //$name1 = str_replace($gelars, "", $name1);
    //$name2 = str_replace($gelars, "", $name2);
    $name1 = stripDegree($name1);
    $name2 = stripDegree($name2);
    return (soundex($name1) == soundex($name2)); 
}


function searchSimilarNameByArrayOfObjects($needle, $objects) { 
    $arr = array();
    $needle = stripDegree($needle);
    for ($i = 0; $i < sizeof($objects); $i++) {
        $arr[$i] = stripDegree($objects[$i]->getNama());
    }
    
    $index = searchMostSimilar($needle, $arr, true, 10);
    /*
    if (substr($needle, 0, 5) == "Catur") {
        echo "searching $needle among \n";
        print_r($arr);
        $index = searchMostSimilar($needle, $arr, true, 5);
    } else {
        return 1;
    }
    */
    //echo $index; die;
    //print_r($objects[$index]); die;
    if ($index == -1) { 
        //echo "<font color='red'><b>Untuk $needle tidak ditemukan match (lev check failed)</b></font><br>";
        return false;
    }
    if ($index == -2) {
        //echo "<font color='red'><b>Untuk $needle tidak ditemukan match (soundex check failed)</b></font><br>";
        return false;
    }
    if ($index >= 0) {
        //echo "Untuk $needle ditemukan matching dengan {$arr[$index]}<br>";
        return $objects[$index];
    }   
    //return $objects[$index];  
}

function searchSimilarByArrayOfObjects($needle, $objects) {
    $arr = array();
    for ($i = 0; $i < sizeof($objects); $i++) {
        $arr[$i] = $objects[$i]->getNama();     
    }
    $index = searchMostSimilar($needle, $arr, true);
    //echo $index; die;
    //print_r($objects[$index]); die;
    return $objects[$index];    
}

function searchMostSimilar($needle, $haystack, $returnIndex = FALSE, $matchLimit = 0) {
    
    $closest = "";
    $shortest = -1;
    
    //foreach ($haystack as $h) {
    for ($i = 0; $i < sizeof($haystack); $i++){
        
        $h = $haystack[$i];
        // calculate the distance between the input word,
        // and the current word
        $lev = levenshtein($needle, $h);
        //echo "$h : $lev<br>";
        // check for an exact match
        if ($lev == 0) {
    
            // closest word is this one (exact match)
            $closest = $h;
            $closestIndex = $i;
            $shortest = 0;
    
            // break out of the loop; we've found an exact match
            break;
        }
    
        // if this distance is less than the next found shortest
        // distance, OR if a next shortest word has not yet been found
        if ($lev <= $shortest || $shortest < 0) {
            // set the closest match, and shortest distance
            $closest  = $h;
            $closestIndex = $i;
            $shortest = $lev;
        }           
    }   
        
    if ($shortest > 0) {
        //$soundex = (soundex($needle) == soundex($closest)) ? "" : "<b><font color='red'>, namun terdengar berbeda</font></b>";
        $soundexCheckPass = (soundex($needle) == soundex($closest)) ? true : false;
    } else {
        $soundexCheckPass = true;
    }
    
    //$pass = false;
    
    if ($shortest > $matchLimit) {
        return -1;
    } else if ($shortest <= $matchLimit) {
        if ($soundexCheckPass) {
            if ($returnIndex)
                return $closestIndex;
            else 
                return $closest;
        } else {
            return -2;
        }
    }
    
    //echo "<br>Perbedaan target dengan sasaran sejumlah $shortest kata, sedangkan batasan maksimal perbedaan $matchLimit</br>";
    /*
    if ($index >= 0) {
        if ($shortest > 0) {
            $soundex = (soundex($needle) == soundex($closest)) ? "" : "<b><font color='red'>, namun terdengar berbeda</font></b>";      
        } else {
            //don't check soundex
        }
        echo "Untuk $needle ditemukan matching dengan {$closest}$soundex<br>";      
    } else {
        echo "<b><font color='red'>Untuk $needle tidak ditemukan match</font></b><br>";
    }
    
    if ($matchLimit == 0) {
        if ($returnIndex)
            return $closestIndex;
        else 
            return $closest;
    }
    
    if ($shortest <= $matchLimit) {
        if ($returnIndex)
            return $closestIndex;
        else 
            return $closest;
    } else {
        return -1;
    }
    */
}

/*
function date_diff($date1, $date2) {
    $current = $date1;
    $datetime2 = date_create($date2);
    $count = 0;
    while(date_create($current) < $datetime2){
        $current = gmdate("Y-m-d", strtotime("+1 day", strtotime($current)));
        $count++;
    }
    return $count;
}


function date_diff($date1, $date2) {
    //$date1 = "2008-11-01 22:45:00"; 
    //$date2 = "2009-12-04 13:44:01"; 
    $diff = abs(strtotime($date2) - strtotime($date1)); 
    /*
    $years   = floor($diff / (365*60*60*24)); 
    $months  = floor(($diff - $years * 365*60*60*24) / (30*60*60*24)); 
    $days    = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
    $hours   = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24)/ (60*60)); 
    $minutes  = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60)/ 60); 
    $seconds = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60 - $minuts*60)); 
    //printf("%d years, %d months, %d days, %d hours, %d minuts\n, %d seconds\n", $years, $months, $days, $hours, $minuts, $seconds);   
    switch ($jenis) {
        case "days":
            return floor($diff / (365*60*60*24));
            break;
        case "months"   
            return floor($diff / (30*60*60*24));
            break;
    } * /
    $days = floor ($diff /(60*60*24));
    return $days;
}
*/
function date_add_days ($date, $days) {
    return date("Y/m/d", strtotime("+$days day", strtotime($date)));
}

function send_sms($nomor, $pesan, $ipaddress, $user, $password, $logfile, $debug=false){
    $pesan = urlencode($pesan);
    
    $send_url = "http://$ipaddress/masking/send.php?username=$user&password=$password&hp=$nomor&message=$pesan";
    $handle = fopen($send_url, "r");
    //echo "Using $logfile as log file\n";
    
    //write_log("Writing: ".$send_url."\n", $logfile);
                
    //$handle = @fopen("/tmp/inputfile.txt", "r");
    $buffer = "";
    
    if ($handle) {
        while (!feof($handle)) {
            $buffer .= fgets($handle, 4096);                        
        }
        fclose($handle);
    }
    //$line = fgets($handle);   
    if ($debug) {
        echo "Mencoba mengirim sms dengan url: $send_url\n";
        echo "SMS Broadcast Server menjawab: $buffer\n";
    }
    //write_log("Got Report#: ".$buffer."\n", $logfile);
    
    $get_report_url = "http://$ipaddress/masking/report.php?rpt=".trim($buffer);
    $handle2 = fopen($get_report_url, "r");
    
    //write_log("Writing: ".$get_report_url."\n", $logfile);    
    $buffer2 = "";  
    
    if ($handle2) {
        while (!feof($handle2)) {
            $buffer2 .= fgets($handle2, 4096);                      
        }
        fclose($handle2);
    }   
    
    //write_log("Got Status#: ".$buffer2."\n", $logfile);
    
    return $buffer2;
    
}

function write_log($logfile, $message) {
    //echo $logfile; 
    $handle = fopen($logfile, 'w');
    fwrite($handle, $message);
    fclose($handle);
}

function isMobileBrowser() {
    
    $mobile_browser = '0';
 
    IF(PREG_MATCH('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone)/i',
        STRTOLOWER($_SERVER['HTTP_USER_AGENT']))){
        $mobile_browser++;
        }
     
    IF((STRPOS(STRTOLOWER($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml')>0) or
        ((ISSET($_SERVER['HTTP_X_WAP_PROFILE']) or ISSET($_SERVER['HTTP_PROFILE'])))){
        $mobile_browser++;
        }
     
    $mobile_ua = STRTOLOWER(SUBSTR($_SERVER['HTTP_USER_AGENT'],0,4));
    $mobile_agents = ARRAY(
        'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
        'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
        'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
        'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
        'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
        'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
        'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
        'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
        'wapr','webc','winw','winw','xda','xda-');
     
    IF(IN_ARRAY($mobile_ua,$mobile_agents)){
        $mobile_browser++;
    }
    IF (STRPOS(STRTOLOWER($_SERVER['ALL_HTTP']),'OperaMini')>0) {
        $mobile_browser++;
    }
    IF (STRPOS(STRTOLOWER($_SERVER['HTTP_USER_AGENT']),'windows')>0) {
        $mobile_browser=0;
    }
    IF($mobile_browser>0){
       // do something
       return true;
    } ELSE {
       // do something else
       return false;
    }

}

function getTimeBasedRandom() {
    //return (time()."|".strtotime('1 June 2011'));
    $time = str_pad(time()-strtotime('1 June 2011') , 10, "0", STR_PAD_LEFT);
    $time = substr($time, 0, 6);
    $rand = str_pad(mt_rand(0, 9999999999), 10, "0", STR_PAD_LEFT);
    return $time.$rand;
}

function bit32Clean($response)
{
    $response = preg_replace('/:\s*(\d{10,})/',':"$1"', $response);
    $response = preg_replace('/(\d{10,})\]/','"$1"]', $response);   
    return $response;
}

define("XOND_ASCII", 0);
define("XOND_BIN", 2);
define("XOND_DEC", 10);
define("XOND_HEX", 16);
define("XOND_B32", 32);
define("XOND_B64", 64);
define("XOND_B95", 95);

function custombase_convert_big ($numstring, $frombase, $tobase)
{
   //$chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
   $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_=!@#$%^&*()[]{}|;:,.<>/?`~ \\\'\"+-";
   //$chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_=!@#$%^&*()[]{}|;:,.<>/?`~ \\\'\"+-";
   $tostring = substr($chars, 0, $tobase);
   $numstring = strtolower($numstring);
   $length = strlen($numstring);

   $number = array();
   for ($i = 0; $i < $length; $i++) {
      $number[$i] = strpos($chars, $numstring{$i});
   }

   $result = '';
   do {
      $divide = 0;
      $newlen = 0;
      for ($i = 0; $i < $length; $i++) {
         $divide = $divide * $frombase + $number[$i];
         if ($divide >= $tobase) {
            $number[$newlen++] = (int) ($divide / $tobase);
            $divide = $divide % $tobase;
         } elseif ($newlen > 0) {
            $number[$newlen++] = 0;
         }
      }
      $length = $newlen;
      $result = $tostring{$divide} . $result;
   } while ($newlen != 0);
   return $result;
}

    
function randomGuid($identifier) {

    $timestamp = strval(time());
    $time = dec2hex($timestamp, 8); // Prints "16777215"    
    $id = dec2hex($identifier, 12); 
    /*
    $rand1 = str_pad(mt_rand(0, 1759218), 7, "0", STR_PAD_LEFT);
    $rand2 = str_pad(mt_rand(0, 6044415), 7, "0", STR_PAD_LEFT);    
    $random = $rand1.$rand2;
    */
    $random = dec2hex(randomBigint(), 12);
    return "$time$id$random";
}

function randomBigint() {
    $rand1 = str_pad(mt_rand(0, 28147497), 8, "0", STR_PAD_LEFT);     //28147497|6710655
    $rand2 = str_pad(mt_rand(0, 6710654), 7, "0", STR_PAD_LEFT);    
    return $rand1.$rand2;
}

function dec2hex($number, $digits=12) {
    /* $digits=12  means  FFFFFFFFFFFF */
    return str_pad(custombase_convert_big ($number, XOND_DEC, XOND_HEX), $digits, "0", STR_PAD_LEFT); // Prints "16777215"  
}

function hex2b32($number, $digits=26) {
    /* $digits=26  means  the whole number in base32 format */
    return str_pad(custombase_convert_big ($number, XOND_HEX, XOND_B32), $digits, "0", STR_PAD_LEFT);
}

function hex2b64($number, $digits=22) {
    /* $digits=22  means  the whole number in base64 format */
    return str_pad(custombase_convert_big ($number, XOND_HEX, XOND_B64), $digits, "0", STR_PAD_LEFT);
}

function hex2b95($number, $digits=20) {
    /* I don't think we'll use this */
    //return custombase_convert_big ($number, XOND_HEX, XOND_B95);
    return str_pad(custombase_convert_big ($number, XOND_HEX, XOND_B95), $digits, "0", STR_PAD_LEFT);
}

function getTarifPph21($status_pegawai, $ada_npwp, $golongan) {
    if ($status_pegawai == 1 ) {
        list($tingkat,$huruf) = explode("/", strtoupper($golongan));
        if ($golongan < 9) {
            return 0;
        }
        $tarif = ($golongan >= 13) ? 0.15 : 0.05;
        if (!$ada_npwp) {
            $tarif = $tarif * 1.2;
        }       
        return $tarif;
    } else {
        $tarif = 0.05;
        if (!$ada_npwp) {
            $tarif = $tarif * 1.2;
        }
        return $tarif;
    }
}

function arrayToXml($data, $tableName) {
    $xml = new XmlWriter();
    $xml->openMemory();
    $xml->startDocument($xml_version, $xml_encoding);
    $xml->startElement($tableName."s");
    foreach($data as $d){
        $xml->startElement($tableName);
        foreach($d as $key => $value){          
            //$xml->startElement($key);
            $xml->writeElement($key, $value);
            //write($xml, $value);
            //$xml->endElement();           
        }
        $xml->endElement();
    }
    $xml->endElement();
    return $xml->outputMemory(true); 
}


/*
// Usage
// Named-based UUID.
$v3uuid = UUID::v3('1546058f-5a25-4334-85ae-e68f2a44bbaf', 'SomeRandomString');
$v5uuid = UUID::v5('1546058f-5a25-4334-85ae-e68f2a44bbaf', 'SomeRandomString');
// Pseudo-random UUID
$v4uuid = UUID::v4();
*/
class UUIDold {
  public static function v3($namespace, $name) {
    if(!self::is_valid($namespace)) return false;

    // Get hexadecimal components of namespace
    $nhex = str_replace(array('-','{','}'), '', $namespace);

    // Binary Value
    $nstr = '';

    // Convert Namespace UUID to bits
    for($i = 0; $i < strlen($nhex); $i+=2) {
      $nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
    }

    // Calculate hash value
    $hash = md5($nstr . $name);

    return sprintf('%08s-%04s-%04x-%04x-%12s',

      // 32 bits for "time_low"
      substr($hash, 0, 8),

      // 16 bits for "time_mid"
      substr($hash, 8, 4),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 3
      (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

      // 48 bits for "node"
      substr($hash, 20, 12)
    );
  }

  public static function v4() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

      // 32 bits for "time_low"
      mt_rand(0, 0xffff), mt_rand(0, 0xffff),

      // 16 bits for "time_mid"
      mt_rand(0, 0xffff),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 4
      mt_rand(0, 0x0fff) | 0x4000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      mt_rand(0, 0x3fff) | 0x8000,

      // 48 bits for "node"
      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
  }

  public static function v5($namespace, $name) {
    if(!self::is_valid($namespace)) return false;

    // Get hexadecimal components of namespace
    $nhex = str_replace(array('-','{','}'), '', $namespace);

    // Binary Value
    $nstr = '';

    // Convert Namespace UUID to bits
    for($i = 0; $i < strlen($nhex); $i+=2) {
      $nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
    }

    // Calculate hash value
    $hash = sha1($nstr . $name);

    return sprintf('%08s-%04s-%04x-%04x-%12s',

      // 32 bits for "time_low"
      substr($hash, 0, 8),

      // 16 bits for "time_mid"
      substr($hash, 8, 4),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 5
      (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

      // 48 bits for "node"
      substr($hash, 20, 12)
    );
  }

  public static function is_valid($uuid) {
    return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?'.
                      '[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
  }
}

function dir_walk($dir) {
    $return = array();
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            $obj = (object)array();
            if ($file === '.' || $file === '..') {
                continue;
            }
            if (is_file($dir ."/". $file)) {
                $obj->leaf = true;
                
                /* Example how to detect file extension and add icon class
                $ext = end(explode(".", $file));
                if ($ext == "js") {
                $obj->iconCls = "js-file";
                }
                */
                continue;
                
            } elseif (is_dir($dir ."/". $file)) {
                //$obj->children = /*$this->*/dir_walk($dir ."/". $file);
                $obj->children = dir_walk($dir ."/". $file);
            }
            $obj->id = $dir ."/". $file;
            $obj->text = $file;
            $return[] = $obj;           
        }
        closedir($dh);
    }
    return $return;
}

function gen_uuid() {
    $dbname = \Propel::getDefaultDB();
    $config = \Propel::getConfiguration();
    $adapterName = $config['datasources'][$dbname]['adapter'];
    switch ($adapterName) {
    	case 'mssql':
    	    return getValueBySql("select newid()");
    	    break;
    	case 'pgsql':
    	    executeSql("CREATE EXTENSION IF NOT EXISTS 'uuid-ossp'", $dbname);
    	    return getValueBySql("select uuid_generate_v4()");
    	    break;
    	default:
    	    return strtoupper(\UUID::mint(4)->__toString());
    	    break;
    }

}

function get_adapter() {
    $dbname = \Propel::getDefaultDB();
    $config = \Propel::getConfiguration();
    return $adapterName = $config['datasources'][$dbname]['adapter'];
}

class UUID {
 const MD5  = 3;
 const SHA1 = 5;
 const clearVer = 15;  // 00001111  Clears all bits of version byte with AND
 const clearVar = 63;  // 00111111  Clears all relevant bits of variant byte with AND
 const varRes   = 224; // 11100000  Variant reserved for future use
 const varMS    = 192; // 11000000  Microsft GUID variant
 const varRFC   = 128; // 10000000  The RFC 4122 variant (this variant)
 const varNCS   = 0;   // 00000000  The NCS compatibility variant
 const version1 = 16;  // 00010000
 const version3 = 48;  // 00110000
 const version4 = 64;  // 01000000
 const version5 = 80;  // 01010000
 const interval = 122192928000000000.0; // Time (in 100ns steps) between the start of the UTC and Unix epochs
 const nsDNS  = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';
 const nsURL  = '6ba7b811-9dad-11d1-80b4-00c04fd430c8';
 const nsOID  = '6ba7b812-9dad-11d1-80b4-00c04fd430c8';
 const nsX500 = '6ba7b814-9dad-11d1-80b4-00c04fd430c8';
 protected static $randomFunc = 'randomTwister';
 protected static $randomSource = NULL;
 //instance properties
 protected $bytes;
 protected $hex;
 protected $string;
 protected $urn;
 protected $version;
 protected $variant;
 protected $node;
 protected $time;
 
 public static function mint($ver = 1, $node = NULL, $ns = NULL, $time = NULL) {
  /* Create a new UUID based on provided data. */
  switch((int) $ver) {
   case 1:
    return new self(self::mintTime($node, $ns, $time));
   case 2:
    // Version 2 is not supported 
    throw new UUIDException("Version 2 is unsupported.");
   case 3:
    return new self(self::mintName(self::MD5, $node, $ns));
   case 4:
    return new self(self::mintRand());
   case 5:
    return new self(self::mintName(self::SHA1, $node, $ns));
   default:
    throw new UUIDException("Selected version is invalid or unsupported.");
  }
 }

 public static function import($uuid) {
  /* Import an existing UUID. */
  return new self(self::makeBin($uuid, 16));
 }   

 public static function compare($a, $b) {
  /* Compares the binary representations of two UUIDs.
     The comparison will return true if they are bit-exact,
      or if neither is valid. */
  if (self::makeBin($a, 16)==self::makeBin($b, 16))
   return TRUE;
  else
   return FALSE;
 }
 
 public static function seq() {
  /* Generate a random clock sequence; this is just two random bytes */
  return self::ramdonBytes(2);
 }

 public function __toString() {
  return $this->string;
 }

 public function __get($var) {
  switch($var) {
   case "bytes":
    return $this->bytes;
   case "hex":
    return bin2hex($this->bytes);
   case "string":
    return $this->__toString();
   case "urn":
    return "urn:uuid:".$this->__toString();
   case "version":
    return ord($this->bytes[6]) >> 4;
   case "variant":
    $byte = ord($this->bytes[8]);
    if ($byte >= self::varRes)
     return 3;
    if ($byte >= self::varMS)
     return 2;
    if ($byte >= self::varRFC)
     return 1;
    else
     return 0;
   case "node":
    if (ord($this->bytes[6])>>4==1)
     return bin2hex(substr($this->bytes,10));
    else
     return NULL; 
   case "time":
    if (ord($this->bytes[6])>>4==1) {
     // Restore contiguous big-endian byte order
     $time = bin2hex($this->bytes[6].$this->bytes[7].$this->bytes[4].$this->bytes[5].$this->bytes[0].$this->bytes[1].$this->bytes[2].$this->bytes[3]);
     // Clear version flag
     $time[0] = "0"; 
     // Do some reverse arithmetic to get a Unix timestamp
     $time = (hexdec($time) - self::interval) / 10000000;
     return $time;
    }
    else
     return NULL;
   default:
    return NULL;
  }
 }

 protected function __construct($uuid) {
  if (strlen($uuid) != 16)
   throw new UUIDException("Input must be a valid UUID.");
  $this->bytes  = $uuid;
  // Optimize the most common use
  $this->string = 
   bin2hex(substr($uuid,0,4))."-".
   bin2hex(substr($uuid,4,2))."-".
   bin2hex(substr($uuid,6,2))."-".
   bin2hex(substr($uuid,8,2))."-".
   bin2hex(substr($uuid,10,6));
 }

 protected static function mintTime($node = NULL, $seq = NULL, $time = NULL) {
  /* Generates a Version 1 UUID.  
     These are derived from the time at which they were generated. */
  // Do a sanity check on clock sequence
  if ($seq !== NULL && strlen($seq) != 2) {
   throw UUIDException("Clock sequence most be a two-byte binary string.");
  }
  // If no time is specified, get time since Gregorian calendar 
  // reform in 100ns intervals.  This is exceedingly difficult
  // because of PHP's (and pack()'s) integer size limits
  // Note that this will never be more accurate than to the microsecond
  // Specifying a time for this method should only ever be used for
  // debugging purposes, lest uniqueness be compromised
  $time = ($time !== NULL) ? (float) $time : microtime(1);
  $time = $time * 10000000 + self::interval;
  // Convert to a string representation
  $time = sprintf("%F", $time);
  preg_match("/^\d+/", $time, $time); //strip decimal point
  // And now to a 64-bit binary representation
  $time = base_convert($time[0], 10, 16);
  $time = pack("H*", str_pad($time, 16, "0", STR_PAD_LEFT));
  // Reorder bytes to their proper locations in the UUID
  $uuid  = $time[4].$time[5].$time[6].$time[7].$time[2].$time[3].$time[0].$time[1];
  // Generate a random clock sequence if one is not provided
  // Please consult Sections 4.1.5 and 4.2.1 of RFC 4122 for
  // guidance regarding when to use a new clock sequence
  $uuid .= ($seq !== NULL) ? $seq : self::randomBytes(2);
  // set variant
  $uuid[8] = chr(ord($uuid[8]) & self::clearVar | self::varRFC);
  // set version
  $uuid[6] = chr(ord($uuid[6]) & self::clearVer | self::version1);
  // Set the final 'node' parameter, a MAC address
  if ($node) 
   $node = self::makeBin($node, 6);
  if (!$node) { 
    // If no node was provided or if the node was invalid, 
    //  generate a random MAC address and set the multicast bit
   $node = self::randomBytes(6);
   $node[0] = pack("C", ord($node[0]) | 1);
  }
  $uuid .= $node;
  return $uuid;
 }

 protected static function mintRand() {
  /* Generate a Version 4 UUID.  
     These are derived soly from random numbers. */
  // generate random fields
  $uuid = self::randomBytes(16);
  // set variant
  $uuid[8] = chr(ord($uuid[8]) & self::clearVar | self::varRFC);
  // set version
  $uuid[6] = chr(ord($uuid[6]) & self::clearVer | self::version4);
  return $uuid;
 }

 protected static function mintName($ver, $node, $ns) {
  /* Generates a Version 3 or Version 5 UUID.
     These are derived from a hash of a name and its namespace, in binary form. */
  if (!$node)
   throw new UUIDException("A name-string is required for Version 3 or 5 UUIDs.");
  // if the namespace UUID isn't binary, make it so
  $ns = self::makeBin($ns, 16);
  if (!$ns)
   throw new UUIDException("A valid UUID namespace is required for Version 3 or 5 UUIDs.");
  switch($ver) {
   case self::MD5: 
    $version = self::version3;
    $uuid = md5($ns.$node,1);
    break;
   case self::SHA1:
    $version = self::version5;
    $uuid = substr(sha1($ns.$node,1),0, 16);
    break;
  }
  // set variant
  $uuid[8] = chr(ord($uuid[8]) & self::clearVar | self::varRFC);
  // set version
  $uuid[6] = chr(ord($uuid[6]) & self::clearVer | $version);
  return ($uuid);
 }

 protected static function makeBin($str, $len) {
  /* Insure that an input string is either binary or hexadecimal.
     Returns binary representation, or false on failure. */
  if ($str instanceof self)
   return $str->bytes;
  if (strlen($str)==$len)
   return $str;
  else
   $str = preg_replace("/^urn:uuid:/is", "", $str); // strip URN scheme and namespace
   $str = preg_replace("/[^a-f0-9]/is", "", $str);  // strip non-hex characters
   if (strlen($str) != ($len * 2))
    return FALSE;
   else
    return pack("H*", $str);
 }

 public static function initRandom() {
  /* Look for a system-provided source of randomness, which is usually crytographically secure.
     /dev/urandom is tried first because tests suggest CAPICOM is quite slow to initialize. */
  if (is_readable('/dev/urandom')) {
   self::$randomSource = fopen('/dev/urandom', 'rb');
   self::$randomFunc = 'randomFRead';
  }
  else if (class_exists('COM', 0)) {
   try {
    self::$randomSource = new COM('CAPICOM.Utilities.1');  // See http://msdn.microsoft.com/en-us/library/aa388182(VS.85).aspx
    self::$randomFunc = 'randomCOM';
   }
   catch(Exception $e) {}
  }
  return self::$randomFunc;
 } 

 public static function randomBytes($bytes) {
  return call_user_func(array('self', self::$randomFunc), $bytes);
 } 

 protected static function randomTwister($bytes) {
  /* Get the specified number of random bytes, using mt_rand().
     Randomness is returned as a string of bytes. */
  $rand = "";
  for ($a = 0; $a < $bytes; $a++) {
   $rand .= chr(mt_rand(0, 255));
  } 
  return $rand;
 }
 
 protected static function randomFRead($bytes) {
  /* Get the specified number of random bytes using a file handle 
     previously opened with UUID::initRandom().
     Randomness is returned as a string of bytes. */
  return fread(self::$randomSource, $bytes);
 }
 
 protected static function randomCOM($bytes) {
  /* Get the specified number of random bytes using Windows'
     randomness source via a COM object previously created by UUID::initRandom().
     Randomness is returned as a string of bytes. */
  return base64_decode(self::$randomSource->GetRandom($bytes,0)); // straight binary mysteriously doesn't work, hence the base64
 }
}


function truepath($path){
    // whether $path is unix or not
    $unipath=strlen($path)==0 || $path{0}!='/';
    // attempts to detect if path is relative in which case, add cwd
    if(strpos($path,':')===false && $unipath)
        $path=getcwd().DIRECTORY_SEPARATOR.$path;
    // resolve path parts (single dot, double dot and double delimiters)
    $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
    $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
    $absolutes = array();
    foreach ($parts as $part) {
        if ('.'  == $part) continue;
        if ('..' == $part) {
            array_pop($absolutes);
        } else {
            $absolutes[] = $part;
        }
    }
    $path=implode(DIRECTORY_SEPARATOR, $absolutes);
    // resolve any symlinks
    if(file_exists($path) && linkinfo($path)>0)$path=readlink($path);
    // put initial separator that could have been lost
    $path=!$unipath ? '/'.$path : $path;
    return $path;
}

function encode_full_url(&$url) 
{ 
    $url = urlencode($url); 
    $url = str_replace("%2F", "/", $url); 
    $url = str_replace("%3A", ":", $url); 
    $url = str_replace("+", "%20", $url); 
    return $url; 
} 

function serverDirToUrl($localPath, $localPathContainer, $pathDomain) {
    $dokumenStorage = realpath($localPathContainer);
    $relativePath = str_replace($dokumenStorage, "", $localPath);
    $relativePath = str_replace("\\", "/", $relativePath);
    $docUrl = $pathDomain.$relativePath;
    $docUrl = encode_full_url($docUrl);
    return $docUrl;
}
    
class UUIDException extends \Exception {

}

    function addUuidStrip($hexstr) {
         $a = substr($hexstr, 0, 8);
         $b = substr($hexstr, 8, 4);
         $c = substr($hexstr, 12, 4);
         $d = substr($hexstr, 16, 4);
         $e = substr($hexstr, 20, 12); 
         $arr = array($a, $b, $c, $d, $e);
         return implode("-", $arr);
    }

    function generateUuid() {
        $uuidObj = UUID::mint(1);
        $uuid = $uuidObj->string;
        
        // Remove the dashes from the string
        $uuid = str_replace("-", "", $uuid);
        
        return $uuid;
    }
    
    function shortenUuid($uuid) {
        $byteString = "";
    
        // Remove the opening and closing brackets
        // $uuid = substr($uuid, 1, strlen($uuid) - 2);
        
        // echo "UUID without strips: ".$uuid."<br>";

        // Read the UUID string byte by byte
        for($i = 0; $i < strlen($uuid); $i += 2) {
            // Get two hexadecimal characters
            $s = substr($uuid, $i, 2);
            
            // Convert them to a byte
            $d = hexdec($s);
            //echo $d."<br><br>";
        
            // Convert it to a single character
            $c = chr($d);
            
            // Append it to the byte string
            $byteString = $byteString.$c;
        } 
    
        // Convert the byte string to a base64 string
        $b64uuid = base64_encode($byteString);
      
        // echo "UUID in B64: ".$b64uuid."<br>";  
      
        // Replace the "/" and "+" since they are reserved characters
        $b64uuid = str_replace("/", "_", $b64uuid);   
        $b64uuid = str_replace("+", "-", $b64uuid);  
      
        // Remove the trailing "=="
        $b64uuid = substr($b64uuid, 0, strlen($b64uuid) - 2); 
      
        // echo "UUID in B64: ".$b64uuid."<br>";
        return $b64uuid;
    }
    
    function reverseUuid($b64uuid) {
        $breverse64uuid = $b64uuid."==";
        $breverse64uuid = str_replace("-", "+", $breverse64uuid);
        $breverse64uuid = str_replace("_", "/", $breverse64uuid);
        $breverse64uuid = base64_decode($breverse64uuid);
        
        // echo "UUID decoded back:".$breverse64uuid."<br>";
      
        // $breverse64uuid = substr($breverse64uuid, 0, strlen($breverse64uuid) - 2); 
        $hexstring = "";
      
        for($i = 0; $i < strlen($breverse64uuid); $i++) {
            $s = substr($breverse64uuid, $i, 1);
            $d = ord($s);
            $c = addZeroes(dechex($d),2);
            $hexstring = $hexstring.$c;
        }  
      
        // echo $byteString." = ".$breverse64uuid;  
        // echo "UUID in reversed back: ".$hexstring."<br>";
      
        $uuidwithstrip = addUuidStrip($hexstring);
        // echo "UUID with strip: " .$uuidwithstrip."<br>";
      
        $uuidObj2 = UUID::import($uuidwithstrip);
        putenv("TZ=Asia/Jakarta");
        $convertToDate = date('l jS \of F Y h:i:S A', $uuidObj2->time);
        // echo "Time detected in UUID: ". $convertToDate ."<br>";
        // echo "Server's time: ".date('l jS \of F Y h:i:S A', $_SERVER['REQUEST_TIME']) ;
        
        return $hexstring;
    }


function datediff($interval, $datefrom, $dateto, $using_timestamps = false) {
    /*
    $interval can be:
    yyyy - Number of full years
    q - Number of full quarters
    m - Number of full months
    y - Difference between day numbers
        (eg 1st Jan 2004 is "1", the first day. 2nd Feb 2003 is "33". The datediff is "-32".)
    d - Number of full days
    w - Number of full weekdays
    ww - Number of full weeks
    h - Number of full hours
    n - Number of full minutes
    s - Number of full seconds (default)
    */
    
    if (!$using_timestamps) {
        $datefrom = strtotime($datefrom, 0);
        $dateto = strtotime($dateto, 0);
    }
    $difference = $dateto - $datefrom; // Difference in seconds
     
    switch($interval) {
     
    case 'yyyy': // Number of full years

        $years_difference = floor($difference / 31536000);
        if (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom), date("j", $datefrom), date("Y", $datefrom)+$years_difference) > $dateto) {
            $years_difference--;
        }
        if (mktime(date("H", $dateto), date("i", $dateto), date("s", $dateto), date("n", $dateto), date("j", $dateto), date("Y", $dateto)-($years_difference+1)) > $datefrom) {
            $years_difference++;
        }
        $datediff = $years_difference;
        break;

    case "q": // Number of full quarters

        $quarters_difference = floor($difference / 8035200);
        while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($quarters_difference*3), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
            $months_difference++;
        }
        $quarters_difference--;
        $datediff = $quarters_difference;
        break;

    case "m": // Number of full months

        $months_difference = floor($difference / 2678400);
        while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($months_difference), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
            $months_difference++;
        }
        $months_difference--;
        $datediff = $months_difference;
        break;

    case 'y': // Difference between day numbers

        $datediff = date("z", $dateto) - date("z", $datefrom);
        break;

    case "d": // Number of full days

        $datediff = floor($difference / 86400);
        break;

    case "w": // Number of full weekdays

        $days_difference = floor($difference / 86400);
        $weeks_difference = floor($days_difference / 7); // Complete weeks
        $first_day = date("w", $datefrom);
        $days_remainder = floor($days_difference % 7);
        $odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?
        if ($odd_days > 7) { // Sunday
            $days_remainder--;
        }
        if ($odd_days > 6) { // Saturday
            $days_remainder--;
        }
        $datediff = ($weeks_difference * 5) + $days_remainder;
        break;

    case "ww": // Number of full weeks

        $datediff = floor($difference / 604800);
        break;

    case "h": // Number of full hours

        $datediff = floor($difference / 3600);
        break;

    case "n": // Number of full minutes

        $datediff = floor($difference / 60);
        break;

    default: // Number of full seconds (default)

        $datediff = $difference;
        break;
    }    

    return $datediff;
}

/*
function convertToArrayOfObjects($tableName, $keyName=NULL) {

    $peerName = $tableName."Peer";      
    //$obj = new ${$tableName}();
    $p = new ${peerName}();
    //$p = $obj->getPeer();
    $arrOfObjects = $p->doSelect(new Criteria());
    
    foreach ($arrOfObjects as $o) { 
        
        if ($keyName) {
            $key = $o->get{$keyName}();
        } else {
            $key = $o->getPrimaryKey(); 
        }       
        $arr[$key] = $o;        
    }
    
    return $arr;
    
}
*/

function zero_filter($val) {
    $filtered = ($val === "0");
    return !$filtered;
}

function zero_empty_array_filter($val) {
    $filtered = ($val === 0) || ($val === "0") || ($val === "");
    return !$filtered;
}

function to_roman_number($N){
    $c='IVXLCDM';
    for($a=5,$b=$s='';$N;$b++,$a^=7)
            for($o=$N%$a,$N=$N/$a^0;$o--;$s=$c[$o>2?$b+$N-($N&=-2)+$o=1:$b].$s);
    return $s;
} 

function date_to_bahasa($date) {
    //date('Y-m-d')
    $date = date_parse($date);
    $tanggal = $date['day']." ".getbulan($date['month'])." ".$date['year'];
    return $tanggal;
}

function koreksi_golongan($str) {
    $angka = substr($str, 0, 1);
    $out = str_replace($angka, to_roman_number($angka)."/", $str);
    return $out;
}

function birthday($birthday){

    list($date, $time) = explode(" ", $birthday);
    list($year,$month,$day) = explode("-",$date);
    $year_diff  = date("Y") - $year;
    $month_diff = date("m") - $month;
    $day_diff   = date("d") - $day;
    if ($day_diff < 0 || $month_diff < 0)
        $year_diff--;
    
    return $year_diff;
}

function illegal_char_remover($text)
{
    //level one
    $ganti = " ";
    $cari = array("\\","\"",",","'","|","--",";"); //tambahan terbaru -- dan ;
    $illegal_char = array("select","insert","into","drop","delete","from","update","where");
    
    
    foreach ($cari as $key => $value) 
    {
        $foundItem = Nohtmlphp($text,$value);
        if ($foundItem != 0)
        {
            $text = str_replace($cari,$ganti,$text);
        }   
    }

    $text = str_ireplace($illegal_char,$ganti,$text);

    return $text;
}

function guid(){
    if (function_exists('com_create_guid')){
        return com_create_guid();
    }else{
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12);
        return $uuid;
    }
}

function execute($cmd, $stdin=null, &$stdout, &$stderr, $timeout=false)
{
    $pipes = array();
    $process = proc_open(
            $cmd,
            array(array('pipe','r'),array('pipe','w'),array('pipe','w')),
            $pipes
    );
    $start = time();
    $stdout = '';
    $stderr = '';

    if(is_resource($process))
    {
        stream_set_blocking($pipes[0], 0);
        stream_set_blocking($pipes[1], 0);
        stream_set_blocking($pipes[2], 0);
        fwrite($pipes[0], $stdin);
        fclose($pipes[0]);
    }

    while(is_resource($process))
    {
        //echo ".";
        $stdout .= stream_get_contents($pipes[1]);
        $stderr .= stream_get_contents($pipes[2]);

        if($timeout !== false && time() - $start > $timeout)
        {
            proc_terminate($process, 9);
            throw new \Exception('Process timeout');
            return 1;
        }

        $status = proc_get_status($process);
        if(!$status['running'])
        {
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            return $status['exitcode'];
        }

        usleep(100000);
    }

    return 1;
}