<?php

namespace Xond\Util;

class ExtCookieGenerator {

    function __construct(){
        
        // Does nothing really. Just convinience
            
    }
    
    function getTypeChar($val) {

        $valType = gettype($val);
        $typeChar = '';

        switch ($valType) {
            case "boolean":
                $typeChar = 'b';
                break;
            case "integer":
                $typeChar = 'i';
                break;
            case "double":
                $typeChar = 'd';
                break;
            case "string":
                $typeChar = 's';
                break;
            case "array":
                $typeChar = 'a';
                break;
            case "object":
                $typeChar = 'o';
                break;
            case "resource":
                $typeChar = 'r';
                break;
        }
        return $typeChar;
    }
    
    function convert($val) {
        
        $typeChar = ExtCookieGenerator::getTypeChar($val);
        
        if (in_array($typeChar, array('o', 'a'))) {

            foreach ($val as $attrKey=>$attrVal) {
                
                $type = ExtCookieGenerator::getTypeChar($attrVal);

                if (!is_integer($attrKey)) {
                    $str = "$attrKey=".str_replace("+", "%20", urlencode("$type:$attrVal"));
                } else {
                    $str = str_replace("+", "%20", urlencode("$type:$attrVal"));
                }
                $strArr[] = $str;
            }
            $cookieStr = implode("^", $strArr);

        } else {

            $cookieStr = $val;
            //$cookieStr = str_replace("+", "%20", urlencode("$val"));

        }

        return $typeChar.":".$cookieStr;
    }

    function set($key, $val) {
        
        $cookieKey = "ext-".$key;
        $cookieVal = ExtCookieGenerator::convert($val);
        setcookie($cookieKey, $cookieVal, 0, '/');

    }

    function remove($key) {
        
        if (isset($_COOKIE["$key"])) {
            unset($_COOKIE["$key"]);
            setcookie($key, null, -1, '/');
        }

    }

    function removeAll() {
        
        foreach ($_COOKIE as $key => $val) {
            
            if (substr($key, 0, 3) == "ext") {
                //echo "removing ".$key." | ".substr($key, 0, 3)."<br>\r\n";
                unset($_COOKIE["$key"]);
                setcookie($key, null, -1, '/');
            }
        }
        //die;
        
    }
    
}
?>