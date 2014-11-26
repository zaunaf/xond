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

class Post extends Rest
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
    
        // POST by definition is CREATE
        // So, no retrieve happens
        // Also using $this->obj so that the object will be easy to manipulate directly by other method
        $modelClass = $this->getClassName();
        $this->obj = new $modelClass();
        $app['dispatcher']->dispatch('rest_post.create');
    
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
        $app['dispatcher']->dispatch('rest_post.load_updates');
    
        // Setting the PrimaryKey
        // There's 4 possibilities
        // 1.  The PK is Composite. 
        //     Action: do nothing. Because the front end should give the value of each PK Column 
        // 2.  The PK is Pre-determined.
        //     Action: Front end should pass the recommended PK via "_id" key. 
        // 3.  The PK is UUID and needs to be generated.
        //     Action: Backend generate it for each adapter/database engine
        // 4.  The PK is Integer and is Serial/Autoincremented
        //     Action: Do nothing. Null will do.
        // Don't forget that in order to kick the right REST method (namely POST in this case), the record
        // added in the Front end SHOULD ALWAYS give empty value for the primary key column.
        // Double quote (empty string) will do.

        // Composite PK
        if ($tInfo->getIsCompositePk()) {

            // do nothing
            
        } else if (isset($arr["_id"])) {

            // This feature enables user to set primary key by themself
            // Just set "_id" on the record definition
            $this->obj->setPrimaryKey($arr["_id"]);
        
        } else if ($pkColInfo->getIsPkUuid()) {
        
            // If it's a PK UUID
            $uuid = gen_uuid();
            $this->obj->setPrimaryKey($uuid);
        
        } else {
            
            // Also do nothing :)
            
        }
        
        $app['dispatcher']->dispatch('rest_post.before_save');

        try {
            
            //print_r($this->obj); die;
            
            $this->obj->save();
            
            $success = true;
            $this->setSuccess($success);

            // Kick the after save event in case someone wants to do something afterwards.
            // This can cancel successfullness.
            $app['dispatcher']->dispatch('rest_post.save');
            
            // Ask again success status from the REST Object.
            if ($this->getSuccess()) {
                
                $modelName = $this->getModelName();
                $this->setMessage("Berhasil menambahkan $modelName");
            
                // Register the data to the response data
                $this->setResponseData($this->obj->toArray());
                $this->setResponseCode('201');
            
                // Process the response string from the attached values
                $this->createResponseStr();
            
                // Kick the after save event in case someone wants to mess with the return json. May be override it?
            
            } else {
                
                // Check whether error message is set. Otherwise kick an Exception
                if ($this->getExceptionCode()) {
                    // Do nothing, let the event report itself
                } else {
                    throw new \Exception();
                }
                
            }
            
        
        } catch (\Exception $e) {
            
            $success = false;
            $this->setSuccess($success);
            
            $modelName = $this->getModelName();
            $this->setMessage("Gagal menambahkan $modelName");
            
            // Register the data to the response data
            $this->setException($e);
            $this->setResponseCode('400');
            
            // Process the response string from the attached values
            $this->createExceptionResponseStr();
            
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
        
        $app->on('rest_post.create', function(Event $e) use ($rest) {
            $rest->onCreate();
        });
        
        $app->on('rest_post.load_updates', function(Event $e) use ($rest) {
            $rest->onLoadUpdates();
        });

        $app->on('rest_post.before_save', function(Event $e) use ($rest) {
            $rest->onBeforeSave();
        });

        $app->on('rest_post.save', function(Event $e) use ($rest) {
            $rest->onSave();
        });
         
        return $app;    
    }
    
    // Override this !
    public function onCreate(){
        
    }
    
    public function onLoadUpdates(){
    
        $obj = $this->getObj();
                
        if (method_exists($obj, 'setCreateDate')) {
            // $obj->setLastUpdate(date('Y-m-d H:i:s'));
            $obj->setCreateDate('1970-01-01 01:00:00');
        }
        if (method_exists($obj, 'setLastUpdate')) {
            // $obj->setLastUpdate(date('Y-m-d H:i:s'));
            $obj->setLastUpdate('1970-01-01 01:00:00');
        }
        if (method_exists($obj, 'setExpiredDate')) {
            // $obj->setLastUpdate(date('Y-m-d H:i:s'));
            $obj->setExpiredDate('1970-01-01 01:00:00');
        }
        if (method_exists($obj, 'setSoftDelete')) {
            $obj->setSoftDelete(0);
        }
        if (method_exists($obj, 'setLastSync')) {
            $obj->setLastSync('1970-01-01 00:00:00');
        }
    
        /*
         * if(!$obj->getLastSync()) { $obj->setLastSync('2000-01-01 00:00:00'); }
        */
        // $obj->setUpdaterId('90915957-31F5-E011-819D-43B216F82ED4');
        if (method_exists($obj, 'setUpdaterId')) {
    
            // if ($this->getUserId()) {
            //    $obj->setUpdaterId($this->getUserId());
            // } else {
            //    $obj->setUpdaterId('90915957-31F5-E011-819D-43B216F82ED4');
            // }
            $obj->setUpdaterId('10000000-1000-1000-1000-100000000000');
    
        }
    
        $this->setObj($obj);
    }
    
    // Override this !
    public function onBeforeSave(){
        //print_r($this->obj); die;
    }

    // Override this !
    public function onSave(){
    
    }
    
    
}