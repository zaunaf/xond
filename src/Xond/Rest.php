<?php
namespace Xond;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\EventDispatcher\Event;
use Xond\Info\TableInfo;
use SpmDikdas\Model\BentukPendidikanPeer;


class Rest
{   
    public $app;
    public $request;
    public $config;
	
    public $appName;
    public $modelName;
    public $className;
    
    public $peerObj;
    public $tableInfoObj;
    
    // For GET
    public $rowCount;
    public $fieldNames;
    public $responseData;
    
    // For POST, PUT
    public $id;
    public $obj;

    // For All Methods
    public $success;
    
    public $message = "";
    public $responseCode;
    public $responseStr;
	
    public $exceptionMsg;
    public $exceptionCode;
    
    /**
     * The request landed here. The same happens for custom REST providers
     * They only need to override one method, make changes necessary to
     * this main "init" procedure and do necessary pre-processing 
     * and post processing. Don't forget the "inject filter" feature.
     * 
     * @param  Request     $request The request sent from browser.. OORR.. from local code
     * @param  Application $app     The silex application
     * @return string               No desc
     */
    public function init(Request $request, Application $app) {
        
        //print_r($app['xond.config']); die;
        
        // Processes the request. Run by the current method class
        try {

            $this->prepare($request, $app);            
            $this->process();
            
    	} catch (Exception $e) {
	        
            // Handle exceptions.
            $this->handleException($e);
            $this->createExceptionResponseStr();
	        
    	}
    	
    	return ($this->createResponse());
    	
    }
    
    /**
     * Prepares the REST object
     * 
     * @param Request $request
     * @param Application $app
     */
    public function prepare($request, Application $app) {
    	
    	// Register events first, then attach it to the object 
    	$app = $this->registerEvents($app);
    	$this->setRequest($request);
    	$this->setApp($app);
    	// $this->setMethod(strtoupper(getBaseClassName(get_class($this))));
    	$this->setMethod(strtoupper($request->getMethod()));
    	$this->setConfig($app['xond.config']);
    	 
    	// Retrieve ModelName and which ID
    	$modelName = $request->get('model');
    	$this->setModelName($modelName);
    	$this->setWhich($request->get('which'));
    	 
    	// Get params only from post & put
    	$params = null;
    	if ($this->getMethod() == 'POST' || $this->getMethod() == 'PUT') {
    		$params = json_decode(stripslashes($request->getContent()),true);

            // Detect if params empty that means the POST request is generated
            // from an ordinary html form.
            if (!sizeof($params)) {
                $params = $request->request->all();
            }

    	}


        // Attach the params to the REST object
    	$this->setParams($params);

    	// Prepare variables
    	$app['dispatcher']->dispatch('rest.prepared');
    	
    	// Set Classes. So the classname, PeerObj and TableInfo Obj is ready to use. 
    	// No need to create them later.
    	$this->setClassName(Xond::createClassName("Model", $modelName, "", $this->appName));
    	$this->setPeerObj(Xond::createPeer($modelName, $this->appName));
    	$this->setTableInfoObj(Xond::createTableInfo($modelName, $this->appName));
    	 
    }
    
    /**
     * Setting the request object for this generator
     * @param Request $request
     */
    public function setRequest($request){
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
     * Set the method (GET, PUT, PUSH, DELETE)
     * @param string $method
     */
    public function setMethod($method="GET") {
    	$this->method = $method;
    }
    
    /**
     * Get the method
     * @return string
     */
    public function getMethod(){
    	return $this->method;
    }
    
    /**
     * Set config to be accessible troughout the class
     *
     * @param array $config
     */
    public function setConfig(array $config) {
    	$this->config = $config;
    	$this->appName = $config['project_php_name'];
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
    public function setModelName($modelName) {
    	$this->modelName = $modelName;
    }
    
    /**
     * Get the model name
     * @return string
     */
    public function getModelName() {
    	return $this->modelName;
    }
    
    /**
     * Get the model class (complete qualified classname with namespace)
     * @param string $className
     */
    public function setClassName($className) {
    	$this->className = $className;
    }
    
    /**
     * Get the class name CQN
     *
     * @return string
     */
    public function getClassName() {
    	return $this->className;
    }
    
    /**
     * Set main id. If it's composite key, it will later break.
     *
     * @param string $which
     */
    public function setWhich($which) {
    	$this->which = $which;
    }
    
    /**
     * Get the main id
     *
     * @return string
     */
    public function getWhich() {
    	return $this->which;
    }
    
    /**
     * Set the params. Get them from POST & PUT in http encoded var.
     *
     * @param array $params
     */
    public function setParams($params) {
    	$this->params = $params;
    }
    
    /**
     * Get the params
     *
     * @return array
     */
    public function getParams() {
    	return $this->params;
    }
    
    /**
     * Set peer object for the current model.
     *
     * @param \BasePeer $peerObj
     */
    public function setPeerObj($peerObj) {
    	$this->peerObj = $peerObj;
    }
    
    /**
     * Get peer object for the current model.
     *
     * @return \BasePeer $peer
     */
    public function getPeerObj() {
    	return $this->peerObj;
    }
    
    /**
     * Set table info object
     * 
     * @param unknown $tableInfoObj
     */
    public function setTableInfoObj($tableInfoObj) {
    	$this->tableInfoObj = $tableInfoObj;
    }
    
    /**
     * Get table info object
     * 
     * @return unknown
     */
    public function getTableInfoObj() {
    	return $this->tableInfoObj;
    }


    /**
     * Set message about the successfullness of the process.
     *
     * @param string $success
     */
    public function setSuccess($success){
        $this->success = $success;
    }
    
    /**
     * Get message about the successfullness of the process.
     *
     * @return string
     */
    public function getSuccess(){
        return $this->success;
    }
    
    
    /**
     * Set message about the result of the process.
     *
     * @param string $message
     */
    public function setMessage($message){
        $this->message = $message;
    }
    
    /**
     * Get message about the result of the process.
     *
     * @return string
     */
    public function getMessage(){
        return $this->message;
    }
    
    
    /**
     * Set total rowcount of the result of the process.
     * Mainly for paging purposes.
     * 
     * @param int $rowCount
     */
    public function setRowCount($rowCount){
    	$this->rowCount = $rowCount;
    }

    /**
     * Get total rowcount of the result of the process.
     * 
     * @return int
     */
    public function getRowCount(){
    	return $this->rowCount;
    }
    
    /**
     * Set field names
     * 
     * @param array $fieldNames
     */
    public function setFieldNames($fieldNames){
    	$this->fieldNames = $fieldNames;
    }
    
    /**
     * Get field names
     * 
     * @return array
     */
    public function getFieldNames(){
    	return $this->fieldNames;
    }
    
    /**
     * Set the response's data. Usually in array format.
     * To be processed before sent to browser.
     *
     * @param array $responseData
     */
    public function setResponseData($responseData){
    	$this->responseData = $responseData;
    }
    
    /**
     * Get the response's data. It's separated in case
     * there's still something to do with the data after
     * it's processed by the default REST handler.
     *
     * @return array
     */
    public function getResponseData(){
    	return $this->responseData;
    }
    
    /**
     * Attach the "id" of the current processed row/object
     * @param unknown $id
     */
    public function setId($id){
        $this->id = $id;
    }
    
    /**
     * Get the "id" of the current processed row/object
     * @return string
     */
    public function getId(){
        return $this->id;
    }
    
    /**
     * Attach the current processed row/object
     * @param unknown $obj
     */
    public function setObj($obj){
        $this->obj = $obj;
    } 
    
    /**
     * Get the current processed row/object
     * @return object
     */
    public function getObj(){
        return $this->obj;
    }
    
    
    /**
     * Set the response string
     *
     * @param string $str
     */
    public function setResponseStr($str) {
        $this->responseStr = $str;
    }
    
    /**
     * Get the response string
     *
     * @return string
     */
    public function getResponseStr() {
        return $this->responseStr;
    }
    
    /**
     * Set the response code
     *
     * @param int $code
     */
    public function setResponseCode($code) {
        $this->responseCode = $code;
    }
    
    /**
     * Get the response code
     *
     * @return int
     */
    public function getResponseCode() {
        return $this->responseCode;
    }
    
    /**
     * Set Exception if it happens
     *
     * @param Exception $e
     */
    public function setException($e){
        $this->exceptionMsg = $e->getMessage();
        $this->exceptionCode = $e->getCode();
    }
    
    /**
     * Get exception message
     *
     * @return string
     */
    public function getExceptionMsg(){
        return $this->exceptionMsg;
    }
    
    /**
     * Get exception code
     *
     * @return number
     */
    public function getExceptionCode(){
        return $this->exceptionCode;
    }
    
    /**
     * Record exception to the Object
     *
     * @param \Exception $e
     */
    public function handleException(\Exception $e) {
         
        $this->setException($e);
    
        $msg = $e->getMessage();
        $code = $e->getCode();
         
        /* still considering how to implement it
        	switch ($code) {
        case 404:
        break;
        }
        */
    }
    
    /**
     * Create classnames. Could be for the entity class,
     * peer class or even table info.
     * 
     * @param string $type
     * @param string $suffix
     * @return string
     */
    public function createClassName($type="Model", $modelName="", $suffix="" ) {
    	
    	$modelName = ($modelName != "") ? $modelName :  $this->getModelName();
    	return "\\".$this->appName."\\".$type."\\".$modelName.$suffix;
    	
    }
    
    /**
     * Get the Peer Object for the given ModelName
     *
     * @param string $className
     * @throws Exception
     * @return unknown
     */
    public function createPeer($modelName="") {
        	
    	$modelName = ($modelName != "") ? $modelName :  $this->getModelName();
    	$peerName = Xond::createClassName("Model", $modelName, "Peer", $this->appName);
    
       if (class_exists($peerName)) {
    		$peerObj = new ${'peerName'} ();
    		return $peerObj;
        } else {
    		throw new Exception("No such model. Don't forget to build.", 404);
    	}
    
    }
    
    /**
     * Create Table Info Object for the given ModelName
     * 
     * @param string $modelName
     * @throws Exception
     * @return unknown
     */
    public function createTableInfo($modelName="") {
    
    	$modelName = ($modelName != "") ? $modelName :  $this->getModelName();
    	$tableInfoClassName = Xond::createClassName("Info", $modelName, "TableInfo", $this->appName);
    	
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
    public function createFieldNames($modelName=""){
    	
    	$tInfo = Xond::createTableInfo($modelName, $this->appName);
    	$cols = $tInfo->getColumns();
    	
    	foreach ($cols as $col){
    		$arr[] = $col->getColumnName();
    	}
    	return $arr;
    	
    }
    
    // Overridden by the Methods
    public function process() {
    	
    }
    
    public function buildJson($success=false, $message=false, $data=false, $rownum=false, $fieldnames=false, $start=false, $limit=false, $row_property="rows") {
        
        $message = ($message) ? ", \"message\": \"$message\"" : "";
        $rownum = ($rownum) ? ", \"results\": $rownum " : "";
        $fieldnames = ($fieldnames) ? ", \"id\": \"{$fieldnames[0]}\" " : "";
        $start = ($start) ? ", \"start\": $start" : "";
        $limit = ($limit) ? ", \"limit\": $limit" : "";
        $data = ($data) ? ", \"$row_property\": ". json_encode($data) : "";
        
        return sprintf("{ \"success\": %s %s %s %s %s %s %s  }", ($success ? 'true':'false'), $message, $rownum, $fieldnames, $start, $limit, $data);
    }
    
    // Overridden by the Methods
    public function createResponseStr(){
        $this->setResponseStr($this->buildJson(true, $this->getMessage(), $this->getResponseData(), $this->getRowCount(), $this->getFieldNames(), $this->getStart(), $this->getLimit()));
    }
    
    public function createExceptionResponseStr(){
        $this->setResponseStr($this->buildJson(false, $this->getExceptionMsg()));
    }
    
    
    public function createResponse(){
        print_r($this->getResponseStr()); die;
        //print_r($this->getResponseCode()); die;
        
        if ($this->getExceptionCode()) {
            return new Response($this->getResponseStr(), $this->getExceptionCode());
        } else {
            return new Response($this->getResponseStr(), $this->getResponseCode());
        }
    		
    }
   	
    // This need to be overridden by so called "CustomRest"
    public function injectFilter(\Criteria $c) {
    	return $c;
    }

    /** Event Management **/
    
    /**
     * Event Registration
     *
     * @param Application $app
     * @return Application
     */
    public function registerEvents(Application $app){
         
        $rest = $this;
    
        $app->on('rest.prepared', function(Event $e) use ($rest) {
            $rest->onPrepared();
        });
    
        return $app;
    }
    
    // This need to be overridden
    public function onPrepared(){
        
    }
    
    public function convertToColumnName($tInfo, $colName){
        
        $columnStr = $tInfo->getClassname()."Peer::".underscoreCapitalize($colName);
        $columnName = constant($columnStr);
        return $columnName;
        
    }
    
}
