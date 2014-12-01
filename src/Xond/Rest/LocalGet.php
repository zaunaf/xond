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
use Xond\Rest\LocalRequest;

/**
 * This is an object class that will be instantiated for local data getting purposes.
 * With this object, hopefully one can create simple to middle complexity query 
 * for reporting, charting, or exporting purposes in a very short time with 
 * great consistency with the frontend. 
 *
 * @author     Donny Fauzan <donny.fauzan@gmail.com> (Nufaza)
 * @version    $Revision$
 * @package    xond.info
 */


class LocalGet extends Get {
    
    private $localRequest;
    
    public function __construct($model, $params, $request, $app){
                 
        // Create Fake Request
        $this->localRequest = new LocalRequest($model, $params);
        $this->init($request, $app);
        
    }
    
    /**
     * Override init. With the different type for $request
     * 
     * @see \Xond\Rest::init()
     */
    public function init(Request $request, Application $app) {
    
        // Processes the request
        // Run by the calling code.
        $this->prepare($this->localRequest, $app);
        $this->process();
        
    }
    
    public function getData(){
        
        // Returning the response data
        return $this->getResponseData();
        
    }
}

?>