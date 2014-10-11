<?php
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
        
        // If composite FK, split the ID first
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
            
            // Get id directly
            $id = $this->getWhich();
            
            // Find the object
            $this->obj = $p->retrieveByPK($id);
        }
        
        $this->setObj($this->obj);
        
        // Create Object if it's not created yet
        $modelClass = $this->getClassName();
                
        if (!is_object($this->obj)) {
            $this->obj = new ${'modelClass'}();
            $app['dispatcher']->dispatch('rest_post.new_object');
        } else {
            $app['dispatcher']->dispatch('rest_post.retrieved_object');
        }
        $app['dispatcher']->dispatch('rest_post.emerged_object');
        
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
        $app['dispatcher']->dispatch('rest_post.update_object');
        
        // Setting the UUID
        if ($tInfo->getIsCompositePk()) {

            // do nothing
            
        } else {
            
            if ($pkColInfo->getIsPkUuid()) {
                // $uuid = strtoupper(\UUIDpg::mint(1)->__toString());
                $uuid = pg_gen_uuid(PenggunaPeer::DATABASE_NAME);
                $this->obj->setPrimaryKey($uuid);
            }
            
        }
        
        $app['dispatcher']->dispatch('rest_post.create_uuid');
        
        if ($this->obj->save()) {
            
            $success = true;
            $this->setMessage('Berhasil mengupdate $modelName');
            
            // Register the data to the response data
            $this->setResponseData($this->obj->toArray());
            $this->setResponseCode(200);
            
            // Kick the data_load event in case someone wants to mess with the value
            $app['dispatcher']->dispatch('rest_post.data_load');
            
            // Process the response string from the attached values
            $this->createResponseStr();
            
            // Kick the response_str_load event in case someone wants to mess with the string. May be override it?
            $app['dispatcher']->dispatch('rest_post.response_str_load');
            
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
        
        $app->on('rest_post.new_object', function(Event $e) use ($rest) {
            $rest->onNewObject();
        });
        
        $app->on('rest_post.retrieved_object', function(Event $e) use ($rest) {
            $rest->onRetrievedObject();
        });
        
        $app->on('rest_post.emerged_object', function(Event $e) use ($rest) {
            $rest->onEmergedObject();
        });
        
        $app->on('rest_post.update_object', function(Event $e) use ($rest) {
            $rest->onUpdateObject();
        });

        $app->on('rest_post.create_uuid', function(Event $e) use ($rest) {
            $rest->onCreateUuid();
        });
            
            
    }
    
    public function onNewObject(){
        
    }
    
    public function onRetrievedObject(){
        
        // Revive deleted record in softDelete configuration
        if (method_exists($obj, 'setSoftDelete')) {
            $this->obj->setSoftDelete(0);
        }
        
    }
    
    public function onEmergedObject(){
    
    }
    
    public function onNewObject(){
    
    }
    
    public function onUpdateObject(){
        
        $this->obj = $this->getObj();
        
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
    
    public function onCreateUuid(){
        
    }
}