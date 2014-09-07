<?php

namespace Xond\gen;

class BaseGen {
	
	public function setRequest($request){
		$this->request = $request;
	}
	
	public function getRequest(){
		return $this->request;
	}

	public function setApp($app){
		$this->app = $app;
	}
	
	public function getApp(){
		return $this->app;
	}
	
	public function getPeer($tableName) {
		
		$peerName = $tableName."Peer";
		$peerObj = new ${'peerName'} ();
		
		return $peerObj;
	}
	
	public function getMap($tableName) {
		
		$peerObj = getPeer($tableName); 
		$mapObj = $peerObj->getTableMap();
		
		return $mapObj;
	}
	
	public function getTables($withNamespace = true) {
		
		global $skipTables;
		
		$app = $this->getApp();
		$configFile = require $app['propel.config_file'];
		$classmap = $configFile['classmap'];
		
		$oldStr = "";
		$written = 0;
	 	$tables = array();
	 	
		foreach ( $classmap as $key => $value ) {
 
			if (!contains($key, array_merge(array("TableMap", "Peer", "Query", "Base"), $skipTables))) {
				
				$tables[] = $key;
				
				list ($app, $model, $tablename)  = explode("\\", $key);
				
				$tablesNoNamespace[] = $tablename;
			}
			 
		}
		
		return ($withNamespace) ? $tables : $tablesNoNamespace;
		
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