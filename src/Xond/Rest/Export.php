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


class Export extends Get {
    
    public $templatePath;
    public $skip_columns;
    
    /**
     * Overridden from Get. Use this if you want printing the whole page.
     * 
     * @see \Xond\Rest\Get::onCount()
     */ 
    public function onCount($e, $rest){
        
        $request = $rest->getRequest();
        
        // This gate checks whether the user wants all records or follow the paging.
        if ($rest->getRequest()->get('page') == 'all') {

            // Since the data isn't ready (only count that is)
            // Let's retrieve the data first and store it to the responseData
            $p = $this->getPeerObj();

            $connection = \Propel::getConnection(\Propel::getDefaultDB());
            
            if ($request->get('restconfig')) {
            
                // Special: RESTCONFIG
                $stmt = $p->doSelectStmt($this->c);
                $outArr = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            } else {
            
                // Standard:
                $tArr = $p->doSelect($this->c, $connection);
                $outArr = $this->processRows($tArr);
            }

            // Register the data to the response data
            $this->setResponseData($outArr);
            
            // See bottom
            $this->prepareExport();
        }
        
    }

    /**
     * Overridden from Get. Use this if you want the paging.
     *
     * @see \Xond\Rest\Get::onDataLoad()
     */
    public function onDataLoad($e, $rest){
        
        // This gate checks whether the user want the paging
        if ($rest->getRequest()->get('page') != 'all') {
            //die('you choose '+ $rest->getRequest()->get('page'));
            $this->prepareExport();
        }
    }    
    
    /**
     * Set the template. Set this in your costum prepareExport method
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
     * Set which column to display
     * @param array $array
     */
    public function setDisplayColumns($array) {
        $this->display_columns = $array;
    }
    
    /**
     * Get the displayed columns
     * @return array
     */
    public function getDisplayColumns() {
        return $this->display_columns;
    }

    /**
     * Set which headers column to display
     * @param array $array
     */
    public function setDisplayHeaders($array) {
        $this->display_headers = $array;
    }
    
    /**
     * Get the displayed columns
     * @return array
     */
    public function getDisplayHeaders() {
        return $this->display_headers;
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
        
        $displayColumn = true;
        $skipColumn = false;
        
        foreach ($cols as $c) {
            //$c = new ColumnInfo();
            
            if (is_array($this->getDisplayColumns())) {
                if (in_array($c->getColumnName(), $this->getDisplayColumns())) {
                    $displayColumn = true;   
                } else {
                    $displayColumn = false;
                }
            } else if (is_array($this->getSkipColumns())) {
                if (in_array($c->getColumnName(), $this->getSkipColumns())) {
                    $skipColumn = true;
                } else {
                    $skipColumn = false;
                }
            }
            if ($this->getDisplayColumns() && $displayColumn) {
                $arr[] = $c->getColumnName();
            }
            if ($this->getSkipColumns() && !$skipColumn) {
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
        
        $displayColumn = false;
        $skipColumn = false;

        if ($this->getDisplayHeaders()) {
            return $this->getDisplayHeaders();
        }
        
        foreach ($cols as $c) {
            //$c = new ColumnInfo();
            if (is_array($this->getDisplayColumns())) {
                
                if (in_array($c->getColumnName(), $this->getDisplayColumns())) {
                    $displayColumn = true;   
                } else {
                    $displayColumn = false;
                }
            } else if (is_array($this->getSkipColumns())) {
                if (in_array($c->getColumnName(), $this->getSkipColumns())) {
                    $skipColumn = true;
                } else {
                    $skipColumn = false;
                }
            }
            if ($this->getDisplayColumns() && $displayColumn) {
                $arr[] = $c->getHeader();
            }
            if ($this->getSkipColumns() && !$skipColumn) {
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
    
    public function prepareExport() {
        
        // Get Parameters from the request
        
        // This is standard parameters processing, each of the parameters directly stated in the request parameters
        $title = $this->getRequest()->get('title') ? $this->getRequest()->get('title') : $this->getTableInfoObj()->getPhpName();
        $subTitle = $this->getRequest()->get('subtitle') ? $this->getRequest()->get('subtitle') : "-";
        $fileName = $this->getRequest()->get('filename') ? $this->getRequest()->get('filename') : "-";
        
        $displayColumns = $this->getRequest()->get('display_columns');
        $displayHeaders = $this->getRequest()->get('display_headers');
        $skipColumns = $this->getRequest()->get('skip_columns');
        
        // This states that if displayColumns stated, skipColumns is not processed
        if ($displayColumns) {
            $displayColumnsArr = json_decode($displayColumns);
            $this->setDisplayColumns($displayColumnsArr);
        } else if ($skipColumns) {
            $skipColumnsArr = json_decode($skipColumns);
            $this->setSkipColumns($skipColumnsArr);
        } else {
            // Yaaaa..
        }

        if ($displayHeaders) {
            $displayHeadersArr = json_decode($displayHeaders);
            $this->setDisplayHeaders($displayHeadersArr);
        }
        
        // Alternatives to show/display columns is via the restconfig parameters
        // Start
        $rc = $this->getRequest()->get('restconfig');
        if ($rc) {
            $restconfig = json_decode($rc);
            $title = $restconfig->title;
            $subTitle = $restconfig->subtitle;
            $fileName = $restconfig->filename;
            $aggregate = $restconfig->aggregate;
            $columns = $restconfig->columns;
            
            foreach ($columns as $col) {
                $colNames[] = $col->name;
                $colHeaders[] = $col->header;
            }
            $this->setDisplayColumns($colNames);
            $this->setDisplayHeaders($colHeaders);
            
            //print_r($restconfig); die;
        }
        // End
        
        $config = $this->getConfig();
        $appName = $config['nama_aplikasi'];
        
        $exportData = array(
            "appname" => $appName,
            "title" => $title,
            "subtitle" => $subTitle,
            "filename" => $fileName,
            "columns" => $this->getColumnNames(),
            "headers" => $this->getColumnHeaders(),
            "data" => $this->getResponseData()
        );
        
        $this->doExport($exportData);
        die;
    }

    // Override this
    public function doExport($exportData) {
        
    } 
}