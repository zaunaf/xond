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
use Xond\Rest;
use Xond\Rest\LocalQuery;

/**
 * This class is an attempt to create a phony Request. Instead of generated
 * from the client, this LocalRequest is instaniated by the application's code,
 * intended to be used as replacement to the Symfony's Request by the LocalGet class.
 * 
 * @author abah
 *
 */
class LocalRequest {
    
    
    public function __construct($model, $params){
        
        $this->setModel($model);
        $this->setParams($params);
        $this->query = new LocalQuery($params);
        
    }
    
    /**
     * Setting the modelname, simulating the REST call /rest/{model}
     * in which the modelname is in the classname format, i.e: UserRole
     *  
     * @param unknown $modelName
     */
    public function setModel($model) {
        
        $this->model= $model;
        
    } 
    
    /**
     * Setting the params, in key=>value (associative array) format. The usual variables are:
     * -  which         (usually represents id of the object being queried)
     * -  start         (offset)
     * -  limit         (limit per page)
     * -  page          (don't know if it's kicking)
     * -  query         (fuzzy search to the displayField)
     * -  filter        (json encoded array)
     * -  restconfig    (also json encoded)
     * -  id            (pseudo id for returning back to the store proxy)
     * @param array $params This should be in Array format. We obviously can overload it with object format, but may be later. 
     */
    public function setParams($params) {
        $this->params = $params;
    }
    
    /**
     * Simulating the getMethod().
     * 
     * @return string
     */
    public function getMethod(){
        return 'LOCALGET';
    }
    
    /**
     * The main get-teway for almost anything
     * 
     * @param string $what
     */
    public function get($what){
        
        switch ($what) {
        	case 'model':
        	    return $this->model;
        	default:
        	    return @$this->params[$what] ?: null;
        }
    }
}
