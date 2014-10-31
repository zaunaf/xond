<?php
/**
 * This file is part of the Xond package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Xond\Rest;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\EventDispatcher\Event;
use Xond\Rest;
use Xond\Info\TableInfo;
use Xond\Info\ColumnInfo;

class Put extends Rest
{
    
    public function process()
    {
        // Reposess Vars
        $request = $this->getRequest();
        $app = $this->getApp();
        $config = $this->getConfig();
        
        // Get the tableInfo object
        $tInfo = $this->getTableInfoObj();
        $pkColInfo = $tInfo->getPkColumnInfo();
        
        // Get the peer object
        $p = $this->getPeerObj();
        $this->obj = "";

        // Getting the Data
        // There's 2 possibilities
        // 1.  The PK is Composite.
        //     Action: split the PK first, because our GET service creates a virtual PK that is
        //     the combined string representative of each PK. Currently, we support only composit of 5 PK columns.
        // 2.  The PK is Normal.
        //     Action: retrieveByPk.
        // Don't forget that in order to kick the right REST method (namely PUT in this case), the record
        // added in the Front end SHOULD ALWAYS give the correct value for the primary key column.
        
        if ($tInfo->getIsCompositePk()) {
        
            $ids = explode(":", $this->getWhich());
        
            switch (sizeof($ids)) {
            	case 2:
            	    $this->obj = $p->retrieveByPK($ids[0], $ids[1]);
            	    break;
            	case 3:
            	    $this->obj = $p->retrieveByPK($ids[0], $ids[1], $ids[2]);
            	    break;
            	case 4:
            	    $this->obj = $p->retrieveByPK($ids[0], $ids[1], $ids[2], $ids[3]);
            	    break;
            	case 5:
            	    $this->obj = $p->retrieveByPK($ids[0], $ids[1], $ids[2], $ids[3], $ids[4]);
            	    break;
            }
            
        } else {
        
            // Get id directly from http parameters
            $id = $this->getWhich();
        
            // Find the object
            $this->obj = $p->retrieveByPK($id);
        }
        
        $app['dispatcher']->dispatch('rest_put.retrieve');
        
        // Retreive the array from object typed params
        $arr = get_object_vars($this->getParams());
        
        // Trim all space
        $arrData = array();
        foreach ($arr as $key => $value) {
            // print_r($key);
            $value = trim($value);
            $arr[$key] = ($value === "") ? null : $value;
        }
        
        // Setting all the properties of the new created object from the arry
        $this->obj->fromArray($arr, \BasePeer::TYPE_FIELDNAME);
        $app['dispatcher']->dispatch('rest_put.before_save');
        
        if ($this->obj->save()) {
        
            $success = true;
            $modelName = $this->getModelName();
            $this->setMessage("Berhasil mengupdate $modelName");
        
            // Register the data to the response data
            $this->setResponseData($this->obj->toArray());
            $this->setResponseCode('200');
        
            // Process the response string from the attached values
            $this->createResponseStr();
        
            // Kick the after save event in case someone wants to mess with the return json. May be override it?
            $app['dispatcher']->dispatch('rest_put.save');
                    
        } else {

            $success = true;
            $modelName = $this->getModelName();
            $this->setMessage("Gagal mengupdate $modelName");
            
            // Register the data to the response data
            $this->setResponseData($this->obj->toArray());
            $this->setResponseCode('400');
            
            // Process the response string from the attached values
            $this->createResponseStr();
            
            // Kick the after save event in case someone wants to mess with the return json. May be override it?
            $app['dispatcher']->dispatch('rest_put.save_failed');
            
        }
        
    }

    /**
     * Only returns status, message and the single object as responseData
     */
    public function createResponseStr(){
        $this->setResponseStr($this->buildJson(true, $this->getMessage(), $this->getResponseData(), false, false, false, false));
    }
    
    /**
     * Event Registration
     *
     * @param Application $app
     * @return Application
     */
    public function registerEvents(Application $app){

        $rest = $this;
        
        $app->on('rest_put.retrieve', function(Event $e) use ($rest) {
            $rest->onRetrieve();
        });
        
        $app->on('rest_put.before_save', function(Event $e) use ($rest) {
            $rest->onBeforeSave();
        });
        
        $app->on('rest_put.save', function(Event $e) use ($rest) {
            $rest->onSave();
        });
            
        return $app;
    }
    
    // Override this !
    public function onRetrieve(){
        
        // Revive deleted record in softDelete configuration
        if (method_exists($this->obj, 'setSoftDelete')) {
            $this->obj->setSoftDelete(0);
        }
        
    }
    
    // Override this !
    public function onBeforeSave(){
        
        if (method_exists($this->obj, 'setLastUpdate')) {
            // $this->obj->setLastUpdate(date('Y-m-d H:i:s'));
            $this->obj->setLastUpdate('1970-01-01 01:00:00');
        }
        if (method_exists($this->obj, 'setSoftDelete')) {
            $this->obj->setSoftDelete(0);
        }
        if (method_exists($this->obj, 'setLastSync')) {
            $this->obj->setLastSync('1970-01-01 00:00:00');
        }
        
        /*
         * if(!$this->obj->getLastSync()) { $this->obj->setLastSync('2000-01-01 00:00:00'); }
        */
        // $this->obj->setUpdaterId('90915957-31F5-E011-819D-43B216F82ED4');
        if (method_exists($this->obj, 'setUpdaterId')) {
            
            if ($this->getUserId()) {
                $this->obj->setUpdaterId($this->getUserId());
            } else {
                $this->obj->setUpdaterId('90915957-31F5-E011-819D-43B216F82ED4');
            }
            
        }
        
    }
    
    // Override this !
    public function onSave(){
        
    }
}