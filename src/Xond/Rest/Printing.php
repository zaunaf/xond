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

/**
 * This is class that extends Export class for printing purposes match with it's REST proxy.
 *
 * @author     Donny Fauzan <donny.fauzan@gmail.com> (Nufaza)
 * @version    $Revision$
 * @package    xond.info
 */


class Printing extends Export {
        
    /**
     * Override this if you want other set of filters
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('nf', array($this, 'nf'))
        );
    }
    
    /** 
     * Override this if you need another filter etc.
     * 
     * @param string $sourceTpl Path to source template directory
     * @param \Twig_Environment $data
     */
    public function getTwig() {
    
        // Apply template
        $sourceTplDir = __DIR__."/templates/";
        
        //$outStr = $sourceTplDir."<br>".$fileName;
        //return  $outStr;
        
        $loader = new \Twig_Loader_Filesystem($sourceTplDir);
        $twig = new \Twig_Environment($loader);
                
        return $twig;
    }
    
    public function doExport($exportData) {
        
        $twig = $this->getTwig();
        $this->setTemplate('default_print.twig');
        
        $outStr = $twig->render($this->getTemplate(), $exportData);
        die($outStr);
    }
    
}