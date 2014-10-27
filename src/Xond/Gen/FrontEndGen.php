<?php

/**
 * This file is part of the Xond package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Xond\Gen;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

use Xond\Gen\BaseGen;
use Xond\Info\TableInfo;
use Xond\Info\ColumnInfo;
use Xond\Info\GroupInfo;
use Xond\Info\FieldsetInfo;

/**
 * This is a front end generator class for building GUI components 
 * that based on ExtJS class structure, and store it in web/app/view/_components.
 * The components can be created instantly or extended before use.
 * The generated components adhere to MVC/MVVM concept standardized by Sencha.
 * This generator supports version 5 of Sencha ExtJS and Sencha Command.
 * 
 * @author     Donny Fauzan <donny.fauzan@gmail.com> (Nufaza)
 * @version    $Revision$
 * @package    xond.gen
 */

class FrontEndGen extends BaseGen
{
    public function prepareFolders() {
        
        $config = $this->getConfig();
        
        //Position controllers
        $appdir = $config['web_folder'].'/app';
        $viewdir = $config['web_folder'].'/app/view';
        
        $controllerdir = $appdir.D.'controller';
        if (!is_dir($controllerdir)) {
            mkdir($controllerdir, 0777, true);
        }
        
        $basecontrollerdir = $controllerdir.D.'base';
        if (!is_dir($basecontrollerdir)) {
            mkdir($basecontrollerdir, 0777, true);
        }
        
        $simplecontrollerdir = $controllerdir.D.'ovd';
        if (!is_dir($simplecontrollerdir)) {
            mkdir($simplecontrollerdir, 0777, true);
        }
        
        //Position models
        $modeldir = $appdir.D.'model';
        if (!is_dir($modeldir)) {
            mkdir($modeldir, 0777, true);
        }
        
        //Position models
        $storedir = $appdir.D.'store';
        if (!is_dir($storedir)) {
            mkdir($storedir, 0777, true);
        }
        
        //Position grids
        $componentsdir = $viewdir.D.'_components';
        if (!is_dir($componentsdir)) {
            mkdir($componentsdir, 0777, true);
        }
        
        $griddir = $componentsdir.D.'grid';
        //$basegriddir = $griddir.D.'base';
        if (!is_dir($griddir)) {
            mkdir($griddir, 0777, true);
        }
        
        //Position combos
        $combodir = $componentsdir.D.'combo';
        //$basecombodir = $combodir.D.'base';
        if (!is_dir($combodir)) {
            mkdir($combodir, 0777, true);
        }
        
        //Position radios
        $radiodir = $componentsdir.D.'radio';
        //$baseradiodir = $radiodir.D.'base';
        if (!is_dir($radiodir)) {
            mkdir($radiodir, 0777, true);
        }
        
        //Position forms
        $formdir = $componentsdir.D.'form';
        //$baseformdir = $formdir.D.'base';
        if (!is_dir($formdir)) {
            mkdir($formdir, 0777, true);
        }
        
        //Position print
        $printdir = $appdir.D.'print';
        if (!is_dir($printdir)) {
            mkdir($printdir, 0777, true);
        }
        
        //Position checkboxgroup
        $checkboxgroupdir = $componentsdir.D.'checkboxgroup';
        //$basecheckboxgroupdir = $checkboxgroupdir.D.'base';
        if (!is_dir($checkboxgroupdir)) {
            mkdir($checkboxgroupdir, 0777, true);
        }
                
        /*
         //Position print
        $printdir = $componentsdir.D.'print';
        //$printdir = $print.D.'base';
        if (!is_dir($printdir)) {
        mkdir($printdir);
        }
        */
        
        //Position fieldsets
        $fieldsetdir = $componentsdir.D.'fieldset';
        $basefieldsetdir = $fieldsetdir.D.'base';
        
        //Position models
        $fieldsetdir = $componentsdir.D.'fieldset';
        $basefieldsetdir = $fieldsetdir.D.'base';
        
        //Position fieldsets
        $fieldsetdir = $componentsdir.D.'fieldset';
        $basefieldsetdir = $fieldsetdir.D.'base';
        
        //Register in class
        $this->controllerdir = $controllerdir;
        $this->basecontrollerdir = $basecontrollerdir;
        $this->simplecontrollerdir = $simplecontrollerdir;
        $this->modeldir = $modeldir;
        $this->storedir = $storedir;
        $this->componentsdir = $componentsdir;
        $this->griddir = $griddir;
        $this->combodir = $combodir;
        $this->radiodir = $radiodir;
        $this->formdir = $formdir;
        $this->printdir = $printdir;
        $this->checkboxgroupdir = $checkboxgroupdir;
        $this->fieldsetdir = $fieldsetdir;
        $this->basefieldsetdir = $basefieldsetdir;
    }
    
    public function initializeTwig() {
        
        /** Prepare twig **/
        $templateRoot = realpath(__DIR__."/templates/js");
        
        // Loader path
        $loader = new \Twig_Loader_Filesystem($templateRoot);
        
        // The twig object
        $twig = new \Twig_Environment($loader);
        
        // Add custom filter "sizeof"
        $filter = new \Twig_SimpleFilter('sizeof', function($array){
            return sizeof($array);
        });
        $twig->addFilter($filter);
        
        // For child that only belongs to one parent, it's definitely master-detail.
        // Get the local link (FK_ID) column name. to link it
        $filter = new \Twig_SimpleFilter('getlocalfkcolumnname', function($childTableInfo){
        
            $belongsToArr = $childTableInfo->getBelongsTo();
            $str = $belongsToArr[0];
            $str = strtolower(underscoreCapitalize($str));
            
            $cols = $childTableInfo->getColumns();
            
            $localFkColumnName = "";
            $relatedTablePkColumnName = "";
// Debug shit            
//             if ($childTableInfo->getName() == 'jenis_beasiswa') {
//                 echo "<br><br><br>";
//             }
            
            foreach ($cols as $c) {
                if ($c->getIsFk() == 1) {
// Debug shit
//                     if ($childTableInfo->getName() == 'beasiswa_ptk') {
//                         echo "- ".$c->getFkTableName()."<br>";
//                     }
                    if ($c->getFkTableName() == $str) {
// Debug shit
//                         if ($childTableInfo->getName() == 'beasiswa_ptk') {
//                             echo "'-match!<br>";
//                         }
                        $localFkColumnName = $c->getName();
                        $relatedTablePkColumnName = $c->getFkTableInfo()->getPkName();
                    }
                }
            }
// Debug shit
//             if ($childTableInfo->getName() == 'beasiswa_ptk') {
//                 die;
//             }
            
            //return array("localFkColumnName" => $localFkColumnName, "relatedTablePkColumnName" => $relatedTablePkColumnName);
            return $localFkColumnName;
        });
             
        $twig->addFilter($filter);
         
        $filter = new \Twig_SimpleFilter('sizeof', function($array){
            return sizeof($array);
        });
        $twig->addFilter($filter);
         
        $filter = new \Twig_SimpleFilter('multiply', function($number){
            $number = !$number ? 10 : $number;
            return ($number * 2) + 60;
        });
        $twig->addFilter($filter);
         
        $filter = new \Twig_SimpleFilter('get_class', function($object){
            return getBaseClassName($object);
            //return str_replace("DataDikdas\\", "", get_class($object));
        });
        $twig->addFilter($filter);
         
        $filter = new \Twig_SimpleFilter('cek_xtype', function($object){
            $xtype = $object->getXtype();
            if (stripos($xtype, 'combo')) {
                return 'combo';
            } else if (stripos($xtype, 'radio')) {
                return 'radio';
            }
            else return false;
        });
        $twig->addFilter($filter);
        
        $this->twig = $twig;
            
    }
    
    public function getTwig(){
        
        return $this->twig;
        
    }
    
    public function getTableInfo(\TableMap $tmap) {
        
        $config = $this->getConfig();
        $projectPhpName = $config['project_php_name'];
        
        $infoClassName = "$projectPhpName\\Info\\{$tmap->getPhpName()}TableInfo";
        //echo "Creating $infoClassName<br>\r\n";
        
        return new $infoClassName();
        //return $infoClassName;
        
            
    }
    
    public function getTablePeer(\TableMap $tmap) {
        
        $peerClassName = $tmap->getPeerClassname();
        return new $peerClassName();
                    
    }
    
    public function printBuffered($str){
        
        if (!isset($this->outStr)) {
            $this->outStr = "";
        }
        
        $this->outStr .= $str."<br>\r\n";
         
    }
    
    public function toStr() {
        return $this->outStr;
    }
    
    public function generate(\Symfony\Component\HttpFoundation\Request $request, \Silex\Application $app) {
        
        // Initialize
        $this->initialize($request, $app);
                
        // Get the tables complete with their namespace (true), false otherwise.
        $maps = $this->getTables(BaseGen::TABLES_MAP);
        //echo sizeof($maps);
        
        
        $this->prepareFolders();
        $this->initializeTwig();
        
        // Process each table
        $this->written = 0;
        
        foreach ($maps as $tmap) {
            
            $infoObj = $this->getTableInfo($tmap);
            $peerObj = $this->getTablePeer($tmap);
            
            $this->createRadioGroup($infoObj, $peerObj);
            
            $this->createCombobox($infoObj, $peerObj);

            $this->createForm($infoObj, $peerObj);
            $this->createGrid($infoObj, $peerObj);
            $this->createControllers($infoObj, $peerObj);
            $this->createModels($infoObj, $peerObj);
            
            $this->printBuffered($infoObj->getName());
             
        }
        
        return $this->toStr();
        
    }
        
    public function render($objName, $filePath, $templateFileName, $array) {
    
        $app = $this->getApp();
    
        $fp = fopen($filePath, 'w');
        if (!$fp) {
            return "File $filePath gagal dibuka";
        }
    
        // Apply template
        $twig = $this->getTwig();
        
        $tplStr = $twig->render($templateFileName, $array);
        	
        if (fwrite($fp, $tplStr)) {
            //$this->written++;
            //$outStr .= "- $filepath written <br>\n";
            $success = true;
        }
        fclose($fp);
        
        return $success;
        
    }
    
    public function createRadioGroup($infoObj, $peerObj) {
        
        if (!$infoObj->getCreateRadiogroup()) {
            return;
        }
        
        $count = $infoObj->getRecordCount();
        
        if ($count > InfoGen::SMALLREF_UPPER_LIMIT) {
        
            $rowsArr = NULL;
        
        } else if ($count > 0) {
                
            $rows = $peerObj::doSelect(new \Criteria());
        
            foreach ($rows as $r)  {
                 
                // Only for autocomplete (coding) purpose
                //$r = new AksesInternet();
                 
                // If display field empty, don't display them
                $nama = $r->getByName($infoObj->getDisplayField(), \BasePeer::TYPE_FIELDNAME);
                if ($nama == '' || $nama == '0') {
                    continue;
                }
                //$arr = $r->toArray(\BasePeer::TYPE_FIELDNAME);
                $arr = array('valueField' => $r->getPrimaryKey(), 'displayField' => $nama);
                $rowsArr[] = $arr;
                 
            }
            
            $filePath = $this->radiodir."/".$infoObj->getPhpName().".js";
            $templateFileName = 'radio-template.js';
            
            $array = array(
                'appName' => $this->appname,
                'table' => $infoObj,
                'data' => $rowsArr
            );
             
            if ($this->render($infoObj->getName(), $filePath, $templateFileName, $array)) {
                $this->written++;
                $this->outStr .= "- $filePath written <br>\n";
            }
            
        }
        
    }
    /**
     * 
     * @param \TableInfo $infoObj
     * @param \BasePeer $peerObj
     */
    public function createCombobox($infoObj, $peerObj) {
        
        $rowsArr = NULL;
        
        if (!$infoObj->getCreateCombobox()) {
            return;    
        }

        //print_r($rowsArr);
             
        //Prepare data for static combos
        $count = $peerObj::doCount(new \Criteria());
    
        if ($count > InfoGen::BIGREF_LOWER_LIMIT) {
             
            $rowsArr = NULL;
             
        } else {
            
            $rows = $peerObj::doSelect(new \Criteria());
             
            foreach ($rows as $r)  {
                $arr = $r->toArray(\BasePeer::TYPE_FIELDNAME);
                $data = array();
                foreach($arr as $key=>$val) {
                    $data[] = $val;
                }
                $rowsArr[] = $data;
            }
        }
         
        // Prepare combo file
        $filePath = $this->combodir."/".$infoObj->getPhpName().".js";
        
        if ($infoObj->getIsStaticRef()) {
            $templateFileName = "combo-static-template.js";
        } else if ($infoObj->getIsBigRef()) {
            $templateFileName = "combo-paged-template.js";
        } else {
            $templateFileName = "combo-normal-template.js";
        }
        $array = array(
                'appName' => APPNAME,
                'table' => $infoObj,
                'data' => $rowsArr
        );
         
        if ($this->render($infoObj->getName(), $filePath, $templateFileName, $array)) {
            $this->written++;
            $this->outStr .= "- $filePath written <br>\n";
        }
        
    } 
    /**
     * 
     * @param \TableInfo $infoObj
     * @param \BasePeer $peerObj
     */
    public function createForm($infoObj, $peerObj) {
        
        if (!$infoObj->getCreateForm()) {
            return;
        }
        
        //Create list of columns registered inside a group
        $colsInGroups = array();
         
        //Creating a very simple registry of groups to check whether a group is added to the form
        $groupsArr = array();
    
        // Identify columns in group
        $groups = $infoObj->getGroups();
        $group_id = 0;
    
        if (sizeof($groups) > 0) {
            
            foreach ($groups as $group) {
                //$group = new GroupInfo();
                 
                // Register the group identified by it's group id (for now, we use array index)
                $groupsArr[$group_id] = 0;
                 
                // Get all the group's columns
                $members = $group->getColumns();
                 
                foreach ($members as $m) {
                    //$m = new ColumnInfo();
                    $colsInGroups[$m->getColumnName()] = $group_id;
                }
                $group_id++;
            }
             
        }
        //print_r($groupsArr);
        //print_r($colsInGroups); die;
         
        // Begin insert thing for forms
        $colsInTable = $infoObj->getColumns();
         
        // Create an empty form registry
        $formMembers = array();
         
        foreach ($colsInTable as $col) {
    
            // Check whether the column used in groups
            //$col = new ColumnInfo();
            
            $columnName = $col->getName();
    
            $foundInGroup = isset($colsInGroups["$columnName"]);
             
            if ($foundInGroup) {
                
                $groupIndex = @$colsInGroups["$columnName"];
                
                // echo "Kolom $columnName termasuk group! <br>";
                // If true skip adding the column to the form
                // Instead insert the group to the form
                // But check the registry first, 1 = already included
                if (@$groupsArr["$groupIndex"] != 1) {
                     
                    //Not included yet, so include the group now
                    $formMembers[] = @$groups[$groupIndex];
    
                    //Tell the group registery that the particular group has been added already
                    $groupsArr["$groupIndex"] = 1;
    
                } else {
    
                    //Do nothing, the group already included
                }
                 
            } else {
                 
                // The column was not a member of any group. Set the column on the registry
                $formMembers[] = $col;
            }
        }
    /*
        if ($infoObj->getName() == "data_tambahan_sekolah"){
            foreach ($formMembers as $col) {
                echo "Test getNama: {$col->getName()}<br>\r\n";
                echo "Test getLabel: {$col->getLabel()}<br>\r\n";
                //echo "Test getTitle: {$col->getTitle()}<br>\r\n";
                
            }
        }
      */  
        // Prepare formdir file
        $filePath = $this->formdir."/".$infoObj->getPhpName().".js";
        $templateFileName = 'form-template.js';
        $array = array(
                'appName' => APPNAME,
                'table' => $infoObj,
                'columns' => $formMembers
        );
    
        if ($this->render($infoObj->getName(), $filePath, $templateFileName, $array)) {
            $this->written++;
            $this->outStr .= "- $filePath written <br>\n";
        }
    
    }
    
    /**
     * 
     * @param \TableInfo $infoObj
     * @param \BasePeer $peerObj
     */
    public function createGrid($infoObj, $peerObj) {
    
        //if ($infoObj->getCreateGrid() && ($infoObj->getName() == "peserta_didik")) {
        if (!$infoObj->getCreateGrid()) {
            return;
        }
        
        // Prepare griddir file
        $filePath = $this->griddir."/".$infoObj->getPhpName().".js";
        $templateFileName = 'grid-template.js';
        $array = array(
            'appName' => $this->appname,
            'table' => $infoObj,
            'columns' => $infoObj->getColumns()
        );
        
        if ($this->render($infoObj->getName(), $filePath, $templateFileName, $array)) {
            $this->written++;
            $this->outStr .= "- $filePath written <br>\n";
        }
        
    }
    
    /**
     * 
     * @param \TableInfo $infoObj
     * @param \BasePeer $peerObj
     */
    public function createControllers($infoObj, $peerObj) {
    
        // Create Controllers
        if (!($infoObj->getCreateGrid() || $infoObj->getCreateForm())) {
            return;
        }
        // Prepare basecontrollerdir file
        $filePath = $this->basecontrollerdir."/".$infoObj->getPhpName().".js";
        $templateFileName = 'controller-template.js';
        $array = array(
                'appName' => $this->appname,
                'table' => $infoObj,
                'columns' => $infoObj->getColumns(),
                'vals' => $this->getInitialValue($infoObj, $peerObj)
        );
    
        if ($this->render($infoObj->getName(), $filePath, $templateFileName, $array)) {
            $this->written++;
            $this->outStr .= "- $filePath written <br>\n";
        }
    
        // Prepare simplecontrollerdir file
        /*
        $filePath = $this->simplecontrollerdir."/".$infoObj->getPhpName().".js";
        $templateFileName = 'controller-simple-template.js';
        $array = array(
                'appName' => $this->appname,
                'table' => $infoObj,
                'columns' => $infoObj->getColumns()
        );
         
        if ($this->render($infoObj->getName(), $filePath, $templateFileName, $array)) {
            $this->written++;
            $this->outStr .= "- $filePath written <br>\n";
        }
        */
    }
    
    /**
     * 
     * @param \TableInfo $infoObj
     * @param \BasePeer $peerObj
     */
    public function createModels($infoObj, $peerObj) {
    
        // Create Models
        // Prepare basecontrollerdir file
        $filePath = $this->modeldir."/".$infoObj->getPhpName().".js";
        $templateFileName = 'model-template.js';
        $array = array(
            'appName' => $this->appname,
            'tableName' => $infoObj->getPhpName(),
            'table' => $infoObj,
            'columns' => $infoObj->getColumns()
        );
        if ($this->render($infoObj->getName(), $filePath, $templateFileName, $array)) {
            $this->written++;
            $this->outStr .= "- $filePath written <br>\n";
        }
        
        // Create Stores
        $filePath = $this->storedir."/".$infoObj->getPhpName().".js";
        $templateFileName = 'store-template.js';
        $array = array(
                'appName' => $this->appname,
                'tableName' => $infoObj->getPhpName(),
                'table' => $infoObj,
                'columns' => $infoObj->getColumns()
        );
        if ($this->render($infoObj->getName(), $filePath, $templateFileName, $array)) {
            $this->written++;
            $this->outStr .= "- $filePath written <br>\n";
        }
        
        
    }
    
    /*
     * Get initial values for an asked Table
    *
    * <p>Get initial values for an asked Table in PHPName format</p>
    *
    * @param string $tablePhpName The name of the table in PHPName Format
    *
    * @return array
    */
    
    /**
     * Get initial values for an asked Table
     * 
     * @param unknown $tablePhpName
     * @return string
     */
    public function getInitialValue($infoObj, $peerObj) {
            
        $tableInfo = $infoObj;
        
        $cols = $tableInfo->getColumns();
        $colnum = 0;
        $allRec = array();
    
        foreach ($cols as $c) {
            	
            //$c = new ColumnInfo();
            unset($fkId);
            	
            $colName = $c->getColumnName();
             
            // Prepare max/min value for numeric foreign keys
            $relatedTblName = phpNamize($c->getFkTableName());
            $relatedPeerName = "{$this->appname}\\Model\\".$relatedTblName ."Peer";
    
            if ($c->getInitialValue()) {
    
                $fkId = $c->getInitialValue();
    
            } else {
    
                if ($c->getIsFk() && in_array($c->getType(), array("int","float"))) {
                    	
                    // do nothing, set by min
                    	
                }
    
                // Prepare default value for numeric foreign keys
                if ($c->getIsFk() && ($c->getInputLength() == 36)) {
    
                    $cr = new \Criteria();
                    //$cr->addAscendingOrderByColumn('random()');
                    	
                    $fkObj = $relatedPeerName::doSelectOne($cr);
                    if (is_object($fkObj)) {
                        $fkId = $fkObj->getPrimaryKey();
                    } else {
                        if (!$c->getAllowEmpty()) {
                            $fkId = "NULL";
                        } else {
                            //die ("FK Object / referensi gak ketemu untuk $tableName.$colName. Mohon tambahkan data di tabel referensi ybs.");
                            continue;
                        }
                    }
                }
    
            }
    
    
            // Generate Record
            switch ($c->getType()) {
    
                case "int":
                    $val = $c->getMin();
                    break;
    
                case "float":
                    $val = $c->getMin();
                    break;
    
                case "string":
                    $val = "''";
                    break;
    
                case "date":
                    $val = "Ext.Date.clearTime(new Date())";
                    break;
                    	
                default:
                    $val = "''";
                    break;
            }
    
            if (isset($fkId)) {
                $val = "'$fkId'";
            }
    
            //if ((!$c->getIsFk()) && ($c->getInputLength() == 36)) {
            //	$val = "generateUuid()";
            //}
    
            if ($c->getIsPk() && ($c->getInputLength() == 0)) {
                //$val = "generateUuid()";
                //$val = " ";
                continue;
            }
    
            $rec = "$colName : $val";
    
            $allRec[] = $rec;
    
            $colnum++;
        }
    
        return $allRec;
    }
    
}
