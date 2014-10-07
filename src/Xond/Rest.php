<?php

namespace Xond;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Exception\Exception;

use Xond\Rest\Get;
use Xond\Rest\Put;
use Xond\Rest\Post;
use Xond\Rest\Delete;

class Rest
{    
    private function floatBukan($val) {
	    $pattern = '/^[+-]?(\d*\.\d+([eE]?[+-]?\d+)?|\d+[eE][+-]?\d+)$/';
	    return preg_match($pattern, trim($val));
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
	 * Set the model name for the whole class
	 * @param string $modelName
	 */
    private function setModelName($modelName) {
        $this->modelName = $modelName;
    }
    
    /**
     * Get the model name 
     * @return string
     */
    private function getModelName() {
        return $this->modelName;
    }
    
    /**
     * Get the model class (complete qualified classname with namespace)
     * @param string $modelClass
     */
    private function setModelClass($modelClass) {
        $this->modelClass = $modelClass;
    }
    
    /**
     * Get the model class CQDN
     * 
     * @return string
     */
    private function getModelClass() {
        return $this->modelClass;
    }
    
    /**
     * Set main id. If it's composite key, it will later break.
     * 
     * @param string $which
     */
    private function setWhich($which) {        
        $this->which = $which;
    }
    
    /**
     * Get the main id
     * 
     * @return string
     */
    private function getWhich() {
        return $this->which;
    }
    
    /**
     * Set the params. Get them from POST & PUT in http encoded var.
     * 
     * @param array $params
     */
    private function setParams($params) {        
        $this->params = $params;
    }

    /**
     * Get the params
     * 
     * @return array
     */
    private function getParams() {
        return $this->params;
    }
    
    /**
     * Set peer object for the current model.
     * 
     * @param \BasePeer $peerObj
     */
    private function setPeerObj($peerObj) {
        $this->peerObj = $peerObj;
    }
    
    /**
     * Get peer object for the current model.
     * 
     * @return \BasePeer $peer
     */
    private function getPeerObj() {
        return $this->peerObj;
    }
    
    /**
     * Set the userId
     * 
     * @param string $userId
     */
    private function setUserId($userId) {
    	$this->userId = $userId;
    }
    
    /**
     * Get the userId
     * 
     * @return string
     */
    private function getUserId() {
    	return $this->userId;
    }
    
    /**
     * Get object peer for the given classname
     * 
     * @param string $className
     * @throws Exception
     * @return unknown
     */
    public function getPeer($className) {
    
        $peerName = $className."Peer";
        
        try {
            $peerObj = new ${'peerName'} ();
            return $peerObj;
        } catch (Exception $e) {
            throw new Exception($e->getMessage()); 
        }
        
    }
    
    /**
     * Prepares everything before the main service kicked
     * 
     * @param Request $request
     * @param Application $app
     * @param string $type
     */
    private function prepare(Request $request, Application $app, $type="POST") {

        // Getting and setting the config
        $config = $app['xond.config'];
        $this->setConfig($config);
        
        // Getting and setting the model name
        $modelName = $request->get('model');
        $className = "{$this->appname}\\Model\\".$relatedTblName;
        
        // Check if the model exists then set the identities
        try {
            
            $peerObj = $this->getPeer($className);
            $this->setModelName($modelName);
            $this->setModelClass($className);
            $this->setPeerObj($peerObj);
            
        } catch (Exception $e) {
            
            throw new Exception("No such model", 404);
            
        }
        
        // Set which object identified with id should be edited or deleted
        $which = $request->get('which');
        
        // Get params from post & put
        $params = null;
        
        if ($type == 'POST' || $type == 'PUT') {
            $params = json_decode(stripslashes($request->getContent())); 
        }
        
        $this->setParams($params);
        
    }
    
    /**
     * Get. Only forward the request to the respective classes.
     * 
     * @param Request $request
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(Request $request, Application $app) {
        
        try {
            
            $this->prepare($request, $app, "GET");
            
            $get = new Xond\Rest\Get()
            
        } catch (\Exception $e) {
            
            return new Response("{ 'success' : false, 'message': '".$e->getMessage()."' }", $e->getCode());
            
        }
        
    }
    
    /**
     * Post. Only forward the request to the respective classes.
     * 
     * @param Request $request
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function post(Request $request, Application $app) {
        
        try {
        
            $this->prepare($request, $app, "POST");
        
        } catch (\Exception $e) {
        
            return new Response("{ 'success' : false, 'message': '".$e->getMessage()."' }", $e->getCode());
            
        }
        
    }

    /**
     * Post. Only forward the request to the respective classes.
     * 
     * @param Request $request
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function put(Request $request, Application $app) {
    
        try {
        
            $this->prepare($request, $app, "PUT");
        
        } catch (\Exception $e) {
        
            return new Response("{ 'success' : false, 'message': '".$e->getMessage()."' }", $e->getCode());
        }
        
    }
    
    public function delete(Request $request, Application $app) {
        
        try {
        
            $this->prepare($request, $app, "DELETE");
        
        } catch (\Exception $e) {
        
            return new Response("{ 'success' : false, 'message': '".$e->getMessage()."' }", $e->getCode());
        }
        
    }
    
    public function getExceptionCodes(\Exception $e) {
        switch ($e->get) {
            case
        }
    }
    
}