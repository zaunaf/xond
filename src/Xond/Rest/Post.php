<?php

namespace Xond\Rest;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Exception\Exception;

class Post {
	
	public function process() {
		
		// Prepare the object, setting peers etc.
		try {
			$this->prepare ( $request, $app );
		} catch ( \Exception $e ) {
			// return new Response('Object not found.', 400);
			return new Response ( "{ 'success' : false, 'message': 'Obyek tidak ditemukan.' }", 400 );
		}
		
		// Get model name an then the object
		$modelClass = $this->getModelClass ();
		$modelName = $this->getModelName ();
		
		// Get TableInfo
		$tableInfoClassName = "\\DataDikdas\\Info\\" . $this->getModelName () . 'TableInfo';
		$tInfo = new ${'tableInfoClassName'} ();
		$pkColInfo = $tInfo->getPkColumnInfo ();
		// print_r($tInfo); die;
		
		// Be carefull if the new
		
		// // Tambahan gara2 softdelete ////
		
		$p = $this->getPeerObj ();
		$obj = "";
		
		if ($tInfo->getIsCompositePk ()) {
			
			$ids = explode ( ":", $this->getWhich () );
			
			switch (sizeof ( $ids )) {
				case 2 :
					$obj = $p->retrieveByPK ( $ids [0], $ids [1] );
					break;
				case 3 :
					$obj = $p->retrieveByPK ( $ids [0], $ids [1], $ids [2] );
					break;
				case 4 :
					$obj = $p->retrieveByPK ( $ids [0], $ids [1], $ids [2], $ids [3] );
					break;
				case 5 :
					$obj = $p->retrieveByPK ( $ids [0], $ids [1], $ids [2], $ids [3], $ids [4] );
					break;
			}
		} else {
			
			$id = $this->getWhich ();
			
			// Find the object
			$obj = $p->retrieveByPK ( $id );
		}
		
		// print_r($obj); die();
		
		// /////////////////////////////////
		
		if (! is_object ( $obj )) {
			
			// Create the new object
			$obj = new ${'modelClass'} ();
		} else {
			$obj->setSoftDelete ( 0 );
		}
		
		// Retreive the array from object typed params
		$arr = get_object_vars ( $this->getParams () );
		
		// trim all space
		$arrData = array ();
		foreach ( $arr as $key => $value ) {
			// print_r($key);
			$arr [$key] = trim ( $value );
			$arr [$key] = ($value === "") ? null : $value;
		}
		
		// Avoid error "primary_key cannot be set"
		// unset($arr[$modelName."Id"]);
		
		// Setting all the properties of the new created object from the arry
		$obj->fromArray ( $arr, \BasePeer::TYPE_FIELDNAME );
		// $obj->setLastUpdate(date('Y-m-d H:i:s'));
		$obj->setLastUpdate ( '1970-01-01 01:00:00' );
		$obj->setSoftDelete ( 0 );
		$obj->setLastSync ( '1970-01-01 00:00:00' );
		/*
		 * if (!$obj->getLastSync()) { $obj->setLastSync('2000-01-01 00:00:00'); }
		 */
		// $obj->setUpdaterId('90915957-31F5-E011-819D-43B216F82ED4');
		if ($this->getUserId ()) {
			$obj->setUpdaterId ( $this->getUserId () );
		} else {
			$obj->setUpdaterId ( '90915957-31F5-E011-819D-43B216F82ED4' );
		}
		
		// Setting the UUID
		// $tInfo = new TableInfo();
		if ($tInfo->getIsCompositePk ()) {
			
			// From array will do ??
			// print_r($obj);
		} else {
			
			if ($pkColInfo->getInputLength () == 0) {
				// $uuid = strtoupper(\UUIDpg::mint(1)->__toString());
				$uuid = pg_gen_uuid ( PenggunaPeer::DATABASE_NAME );
				$obj->setPrimaryKey ( $uuid );
			}
		}
		// return print_r($obj);
		/*
		 * try { if ($obj->save()) { $success = true; $outStr = "{ success : true, message : 'Berhasil membuat $modelName', 'rows' : ".json_encode($obj->toArray())." }"; } } catch (\Exception $e) { //var_dump($e); die; $success = false; $outStr = "{ success : false, message : 'Gagal menyimpan $modelName (".$e->getCause()->getMessage().")' }"; //$outStr = "{ 'success' : false, 'message' : 'Gagal menyimpan $modelName' }"; //var_dump($e->getCause()->getMessage()); die; }
		 */
		
		try {
			if ($obj->save ()) {
				$success = true;
				$outStr = "{ 'success' : true, 'message' : 'Berhasil mengupdate $modelName', 'rows' : " . json_encode ( $obj->toArray () ) . " }";
			}
		} catch ( \Exception $e ) {
			
			// print_r($e); die;
			// echo $e->getCause()->getMessage(); die;
			$success = false;
			$str = $this->errorProcess ( $e );
			// echo $str; die;
			// $str = $e->getCause()->getMessage();
			
			$outStr = "{ 'success' : false, 'message' : 'Gagal menyimpan $modelName (" . $str . ")' }";
    		//$outStr = "{ 'success' : false, 'message' : 'Gagal menyimpan $modelName' }";
    	}
    	
    	if ($success) {
    	return $outStr;
    	} else {
    	return new Response($outStr, 400);
    	}
    	 
    }
    
}