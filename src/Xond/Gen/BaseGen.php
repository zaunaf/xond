<?php

/**
 * This file is part of the Xond package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */
namespace Xond\Gen;

use Direct\Router\Request;
use Silex\Application;

/**
 * This is a utility base generator class as an abstract for any generator classes
 * that based on Propel objects.
 *
 * InfoGen, FrontEnd Gen and all extends this class
 *
 * @author     Donny Fauzan <donny.fauzan@gmail.com> (Nufaza)
 * @version    $Revision$
 * @package    xond.gen
 */

class BaseGen {
	
    /** Switcher, outputs tablename string (Classname Format), i.e UserGroup **/
    const TABLES_STRING = 1;
    
    /** Switcher, outputs tablename string with namespaces (Classname Format) i.e MyApp\Models\om\UserGroup **/
    const TABLES_NS_STRING = 2;
    
    /** Switcher, outputs Propel's table maps **/
    const TABLES_MAP = 3;
    
    /** Switcher, outputs Propel's table peers **/
    const TABLES_PEER = 4;
    
    /** Switcher, outputs Propel's table query **/
    const TABLES_QUERY = 5;
    
    /** Switcher, outputs Propel's table plain objects **/
    const TABLES_OBJECT = 6;

    /** Switcher, outputs Propel's table info objects **/
    const TABLES_INFO = 6;

    protected $request;    
    public $outStr;
    
    public function initialize(\Symfony\Component\HttpFoundation\Request $request, \Silex\Application $app) {
        
        // So that Silex's Request and Application accessible in any methods
        $this->setRequest($request);
        $this->setApp($app);
        
        // Get the config
        $config = $app['xond.config'];
        $this->setConfig($config);
        $this->setAppName($config['project_php_name']);
        
        // Mark the start of gen process. Now using monolog
        $app['monolog']->addInfo("FrontEndGen start at ". date ( 'Y-m-d H:i' ));
    }
    
    /**
     * Setting the request object for this generator
     * @param Request $request
     */
	public function setRequest(\Symfony\Component\HttpFoundation\Request $request){
		$this->request = $request;
	}
	
	/**
	 * Getting the request 
	 * @return Request
	 */
	public function getRequest(){
		return $this->request;
	}

	/**
	 * Setting the Application object for this generator
	 * @param Application $app
	 */
	public function setApp(Application $app){
		$this->app = $app;
	}
	
	/**
	 * Returning the Application object so whatever child need is available
	 * @return Application
	 */
	public function getApp(){
		return $this->app;
	}

	/**
	 * Setting the Application name for this generator
	 * @param string $app
	 */
	public function setAppName($appName){
	    $this->appName = $appName;
	}
	
	/**
	 * Returning the Application name so whatever child need is available
	 * @return string
	 */
	public function getAppName(){
	    return $this->appName;
	}
	
	/**
	 * Set config to be accessible troughout the class
	 *
	 * @param array $config
	 */
	public function setConfig(array $config) {
	    $this->config = $config;
	    $this->appname = $config['project_php_name'];
	}
	
	/**
	 * Get the config
	 *
	 * @return array
	 */
	public function getConfig() {
	    return $this->config;
	}
	
	/**
	 * Get object peer for the given classname
	 * @param String $className
	 * @return \BasePeer
	 */
	public function getPeer($className) {
		
		$peerName = $className."Peer";
		$peerObj = new ${'peerName'} ();
		
		return $peerObj;
	}
	
	/**
	 * Get table map for the given classname
	 * @param string $className
	 * @return \TableMap
	 */
	public function getMap(string $className) {
		
		$peerObj = getPeer($className);
		$mapObj = $peerObj->getTableMap();
		
		return $mapObj;
	}
	
	/**
	 * Get all tables in wanted formats (see constants)
	 *   
	 * @param int $format
	 * @return array
	 */
	public function getTables($format) {

	    // Get app
		$app = $this->getApp();
        
		// Prepare vars
		$configFile = require $app['propel.config_file'];
		$skipTables = $app['xond.config']['front_end_skip_tables'];
		$classmap = $configFile['classmap'];
		
		$oldStr = "";
		$written = 0;
	 	$tables = array();

 	    // Loop each entry in classmap, but filter only the plain objects.
 	    // Also skip unwanted tables to be shown in front end
		foreach ( $classmap as $key => $value ) {
            
		    //echo $key."<br>\r\n";
		    //continue;
		    
		    $arrSplitClassName = explode("\\", $key);
		    
		    if (sizeof($arrSplitClassName) == 3) {
                        list ($appName, $modelStr, $className) = $arrSplitClassName;
		    } else {
		        list ($appName, $modelStr, $type, $className) = $arrSplitClassName;
		    }
		       
		    
		    // Finds strings first
                    if (!is_array($skipTables)) {
                        $skipTables = explode(",", str_replace(" ", "", $skipTables));
                    }
		    if (!contains($key, array_merge(array("TableMap", "Peer", "Query", "Base"), $skipTables))) {
		        
		        // Table string with namespace
		        $tablesNsString[] = $key;
		        
		        // Table string with no namespace
		        $tablesString[] = $className;
		        
		        // Table Objects
		        $tablesObject[] = new $key();

                        // Table Info Objects
                        $infoKey = str_replace('\\Model\\', '\\Info\\', $key).'TableInfo';
		        if (class_exists($infoKey)){
                            $tablesInfo[] = new $infoKey();
                        }

		    }
		    
		    // Finds objects
		    if (!contains($key, $skipTables)) {
                
		        //echo $key."<br>\r\n";
		        
		        // Table map format
		        //if ( endsWith($key, "TableMap") && !startsWith($className, "Base") ) {
				//    $tablesMap[] = new $key();
		        //}
		        
		        // Table peer format
		        if ( endsWith($key, "Peer") && !startsWith($className, "Base") ) {
		            $tablePeer = new $key();
		            $tablesPeer[] = $tablePeer;
		            $tablesMap[] = $tablePeer->getTableMap(); 
		        }
		        // Table query format
		        if ( endsWith($key, "Query") && !startsWith($className, "Base") ) {
		            $tablesQuery[] = new $key();
		        }
		        
			}
			 
		}
		
		switch ($format) {

		    case BaseGen::TABLES_STRING:
		        return $tablesString;
		        break;

	        case BaseGen::TABLES_NS_STRING:
	            return $tablesNsString;
	            break;
	        
	        case BaseGen::TABLES_MAP:
	            return $tablesMap;
	            break;
	        
	        case BaseGen::TABLES_PEER:
	            return $tablesPeer;
	            break;
	        
	        case BaseGen::TABLES_QUERY:
	            return $tablesQuery;
	            break;
	        
	        case BaseGen::TABLES_OBJECT:
	            return $tablesInfo;
	            break;
		    
		}
		
		//return ($withNamespace) ? $tables : $tablesNoNamespace;
		
	}
	
	/**
	 * Get table name (no schema name)
	 *
	 * @param \TableMap $tmap
	 * @return string
	 */
	public function getName(\TableMap $tmap) {
	    return strtolower(underscoreCapitalize($tmap->getPhpName()));
	}
	
	/**
	 * Get schema name
	 * 
	 * @param \TableMap $tmap
	 */
	public function getSchemaName(\TableMap $tmap) {
	    
	    $config = $this->getConfig();
	    
	    $completeTableName = $tmap->getName();
	    $arrTableName = explode(".", $completeTableName);
	    
	    if (sizeof($arrTableName) > 1) {

	        list ($schemaName, $tableName) = $arrTableName;
	        return $schemaName;
	    
	    } else {
	        
	        switch ($config["db_driver"]){
	            case 'mssql':
                   return 'dbo';
	            case 'pgsql':
                   return 'public';
	            default: 
	                return false;
	        }
	        
	    }
	    
	}
	
	public function checkIsRef(\TableMap $tmap) {
	    
	    $config = $this->getConfig();
	    $refSchemas = explode(",", $config["reference_schemas"]);
	    if (in_array($this->getSchemaName($tmap), $refSchemas)) {
	        return true;
	    } else {
	        return false;
	    }
	}
	/*
	public function cekSkipTable($key) {
	
		$skipTable = array(
			"AnggotaGugus", "VersiDb", "PengawasTerdaftar", "SasaranPengawasan", "SasaranSurvey", "TableSync", "VersiDb", "SyncLog"
		);
		//$skipTable = array();
		foreach ($skipTable as $s)
		{
			if (stripos($key, $s)) {
				return true;
			}
		}
		return false;
	}
	
	
	public function render($objName, $filePath, $templateFileName, $array) {
		
		$app = $this->getApp();
		
		$fp = fopen($filePath, 'w');
		if (!$fp) {
			return "File $filePath gagal dibuka";
		}
		
		// Apply template
		$tplStr = $app['twig']->render($templateFileName, $array);
					
		if (fwrite($fp, $tplStr)) {
			//$written++;
			//$outStr .= "- $filepath written <br>\n";
			$success = true;
		}
		fclose($fp);		
		
		return $success;
	}
	*/
}