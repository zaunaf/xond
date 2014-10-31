<?php

namespace Xond;

/**
 * This file is part of the Xond package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * This is the Main Xond Class.
 * This file registers the extended php libraries. Just that currently.
 *
 * @author     Donny Fauzan <donny.fauzan@gmail.com> (Nufaza)
 * @version    $Revision$
 * @package    xond
 */

class Xond
{
    /**
     * Doesn't do anything yet
     */
    public function __construct() {
        // Include Additional Functions
        if (!is_file(__DIR__.'/../../lib/functions.php')) {
            die ('file functions.php not found');
        }
        
        require_once __DIR__.'/../../../lib/functions.php';
    }

    /**
     * Create classnames. Could be for the entity class,
     * peer class or even table info.
     *
     * @param string $type
     * @param string $suffix
     * @return string
     */
    public static function createClassName($type="Model", $modelName="", $suffix="", $appName ) {
         
        //$modelName = ($modelName != "") ? $modelName :  $this->getModelName();
        return "\\".$appName."\\".$type."\\".$modelName.$suffix;
         
    }
    
    /**
     * Get the Peer Object for the given ModelName
     *
     * @param string $className
     * @throws Exception
     * @return unknown
     */
    public static function createPeer($modelName="", $appName) {
         
        $modelName = ($modelName != "") ? $modelName :  $this->getModelName();
        $peerName = Xond::createClassName("Model", $modelName, "Peer", $appName);
    
        if (class_exists($peerName)) {
            $peerObj = new ${'peerName'} ();
            return $peerObj;
        } else {
            throw new \Exception("No such model. Don't forget to build.", 404);
        }
    
    }
    
    /**
     * Create Table Info Object for the given ModelName
     *
     * @param string $modelName
     * @throws Exception
     * @return unknown
     */
    public static function createTableInfo($modelName="", $appName) {
    
        $modelName = ($modelName != "") ? $modelName :  $this->getModelName();
        $tableInfoClassName = Xond::createClassName("Info", $modelName, "TableInfo", $appName);
         
        if (class_exists($tableInfoClassName)) {
            $tInfo = new ${'tableInfoClassName'}();
            return $tInfo;
        } else {
            throw new Exception("No such table info. Don't forget to generate first.", 404);
        }
    
    }
    
    /**
     * Create FieldNames for given ModelName
     *
     * @param string $modelName
     * @return array
     */
    public static function createFieldNames($modelName=""){
         
        $tInfo = Xond::createTableInfo($modelName);
        $cols = $tInfo->getColumns();
         
        foreach ($cols as $col){
            $arr[] = $col->getColumnName();
        }
        return $arr;
         
    }
    
}
