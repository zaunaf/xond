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
 * This is an abstract base class that will be extended to many classes as defined by 
 * the database model. The file will be extended firstly to it's base model definition
 * and then further extended to user editable classes to costumize it more, enabling users
 * adapt the GUI requirement of ExtJS components layouts and config.
 *
 * @author     Donny Fauzan <donny.fauzan@gmail.com> (Nufaza)
 * @version    $Revision$
 * @package    xond.info
 */


class Printing extends Get {
    
    public $templatePath;
    public $skip_columns;
    
    /**
     * Overridden from Get. Use this if you want printing the whole page.
     * 
     * @see \Xond\Rest\Get::onCount()
     */ 
    public function onCount($e, $rest){
        
        if ($rest->getRequest()->get('page') == 'all') {

            // Since the data isn't ready (only count that is)
            // Let's retrieve the data first and store it to the responseData
            $p = $this->getPeerObj();
            
            $tArr = $p->doSelect($this->c, $connection);
            $outArr = $this->processRows($tArr);
            
            // See bottom
            $this->doPrint();    
        }
    }

    /**
     * Overridden from Get. Use this if you want the paging.
     *
     * @see \Xond\Rest\Get::onCount()
     */
    public function onDataLoad($e, $rest){
        
        if ($rest->getRequest()->get('page') != 'all') {
            $this->doPrint();
        }
    }    
    
    /**
     * Set the template. Set this in your costum doPrint method
     * @param string $path
     */
    public function setTemplate($path) {
        $this->templatePath = $path;
    }
    
    /**
     * Get the template. No need to override this.
     * @param string $path
     */
    public function getTemplate() {
        return $this->templatePath;
    }
    
    /**
     * Set which column to skip
     * @param unknown $array
     */
    public function setSkipColumns($array) {
        $this->skip_columns = $array;
    }
    
    /**
     * Get the skipped columns
     * @return unknown
     */
    public function getSkipColumns() {
        return $this->skip_columns;
    }
    
    /**
     * Override this if you want custom fields.
     * Skipping columns supported also here.
     * 
     * @return array
     */
    public function getColumnNames()
    {
        $tableInfo = $this->getTableInfoObj();
        $cols = $tableInfo->getColumns();
        $skipColumn = false;
        
        foreach ($cols as $c) {
            //$c = new ColumnInfo();
            
            if (is_array($this->getSkipColumns())) {
                if (in_array($c->getColumnName(), $this->getSkipColumns())) {
                    $skipColumn = true;
                } else {
                    $skipColumn = false;
                }
            }
            if (!$skipColumn) {
                $arr[] = $c->getColumnName();
            }
        }
        return $arr;
    }
    
    /**
     * Override this if you want custom header table
     * @return array
     */
    public function getColumnHeaders()
    {
        $tableInfo = $this->getTableInfoObj();
        $cols = $tableInfo->getColumns();
        $skipColumn = false;
        
        foreach ($cols as $c) {
            //$c = new ColumnInfo();
            if (is_array($this->getSkipColumns())) {
                if (in_array($c->getColumnName(), $this->getSkipColumns())) {
                    $skipColumn = true;
                } else {
                    $skipColumn = false;
                }
            }
            
            if (!$skipColumn) {
                $arr[] = $c->getHeader();
            }
        }
        return $arr;
    }
    
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
    
    public function doPrint() {
        
        $twig = $this->getTwig();
        $this->setTemplate('default_print.twig');
        
        // Get Parameters from the request
        $title = $this->getRequest()->get('title') ? $this->getRequest()->get('title') : $this->getTableInfoObj()->getPhpName();
        $subTitle = $this->getRequest()->get('subtitle') ? $this->getRequest()->get('subtitle') : "-";
        $config = $this->getConfig();
        $appName = $config['nama_aplikasi'];
        
        $printData = array(
            "appname" => $appName,
            "title" => $title,
            "subtitle" => $subTitle,
            "columns" => $this->getColumnNames(),
            "headers" => $this->getColumnHeaders(),
            "data" => $this->getResponseData()
        );
        
        $outStr = $twig->render($this->getTemplate(), $printData);
        die($outStr);
    }
    

    
}