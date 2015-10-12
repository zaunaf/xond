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
use Simpak\Model\KegiatanGuru;
use Simpak\Model\KegiatanGuruPeer;

class Delete extends Rest
{

    public function process()
    {
        
        try {
            
            // Reposess Vars
            $request = $this->getRequest();
            $app = $this->getApp();
            $config = $this->getConfig();
            
            // Get the tableInfo object
            $tInfo = $this->getTableInfoObj();
            $pkColInfo = $tInfo->getPkColumnInfo();
            
            // Get model name an then the object
            $modelClass = $this->getModelName();
            
            // Get TableInfo, check if isCompositeKey
            $tInfo = $this->getTableInfoObj();
            $pkColInfo = $tInfo->getPkColumnInfo();
            
            $p = $this->getPeerObj();
            
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
                
                // Get id directly from http parameters
                $id = $this->getWhich();
                
                // Find the object
                $this->obj = $p->retrieveByPK($id);
                // print_r($this->obj);
            }
            
            $app['dispatcher']->dispatch('rest_delete.retrieved_object');
            
            $childColObj = $tInfo->getRelatingColumns();
            $arrTInfo = (array) $tInfo;
            
            $value = $this->obj->getPrimaryKey();
            
            //if (sizeof($childColObj) > 0) {
            //     $relatingColumns = $arrTInfo["relating_columns"];
            //    $child = $this->deltree($relatingColumns, $value);
            //}
            $this->obj->delete();
            
            $this->setMessage("Berhasil menghapus ".$this->getModelName());
            
            // Register the data to the response data
            $this->setResponseCode(200);
            
            // Process the response string from the attached values
            $this->createResponseStr();
            
            // Kick the response_str_load event in case someone wants to mess with the string. May be override it?
            $app['dispatcher']->dispatch('rest_delete.response_str_load');
        
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
            $app['dispatcher']->dispatch('rest_delete.delete_failed');
        
        }
        
    }
    
    public function createResponseStr(){
        $this->setResponseStr($this->buildJson(true, $this->getMessage()));
    }
    
    public function createExceptionResponseStr(){
        $this->setResponseStr($this->buildJson(false, $this->getExceptionMsg()));
    }
    
    /**
     * Event Registration
     *
     * @param Application $app
     * @return Application
     */
    public function registerEvents(Application $app){
        
        $rest = $this;
        
        $app->on('rest_delete.retrieved_object', function(Event $e) use ($rest) {
            $rest->onRetrievedObject();
        });

        $app->on('rest_delete.response_str_load', function(Event $e) use ($rest) {
            $rest->onResponseStrLoad();
        });
        
//         $app->on('rest_delete.retrieved_object', function(Event $e) use ($rest) {
//             $rest->onRetrievedObject();
//         });

        $app->on('rest_delete.delete_failed', function(Event $e) use ($rest) {
            $rest->onDeleteFailed();
        });

        return $app;
            
    }
    
    public function onRetrievedObject(){
    
        
        // Revive deleted record in softDelete configuration
        if (method_exists($this->obj, 'setSoftDelete')) {
            $this->obj->setSoftDelete(0);
        }
    
    }
    
    public function onResponseStrLoad(){
        
    }
    
    public function onDeleteFailed(){
        
    }
}