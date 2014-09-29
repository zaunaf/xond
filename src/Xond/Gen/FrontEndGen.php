<?php

namespace Xond\Gen;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

use Xond\Gen\BaseGen;
use Xond\Info\TableInfo;
use Xond\Info\ColumnInfo;
use Xond\Info\GroupInfo;
use Xond\Info\FieldsetInfo;



class FrontEndGen extends BaseGen
{
    public function prepareFolders() {
        
        $config = $this->getConfig();
        
        //Position controllers
        $appdir = $config['web_folder'].'/app';
        $controllerdir = $appdir.D.'controller';
        if (!is_dir($controllerdir)) {
            mkdir($controllerdir);
        }
        
        $basecontrollerdir = $controllerdir.D.'base';
        if (!is_dir($basecontrollerdir)) {
            mkdir($basecontrollerdir);
        }
        
        $simplecontrollerdir = $controllerdir.D.'ovd';
        if (!is_dir($simplecontrollerdir)) {
            mkdir($simplecontrollerdir);
        }
        
        //Position models
        $modeldir = $appdir.D.'model';
        if (!is_dir($modeldir)) {
            mkdir($modeldir);
        }
        
        //Position models
        $storedir = $appdir.D.'store';
        if (!is_dir($storedir)) {
            mkdir($storedir);
        }
        
        //Position grids
        $componentsdir = $viewdir.D.'_components';
        if (!is_dir($componentsdir)) {
            mkdir($componentsdir);
        }
        
        $griddir = $componentsdir.D.'grid';
        //$basegriddir = $griddir.D.'base';
        if (!is_dir($griddir)) {
            mkdir($griddir);
        }
        
        //Position combos
        $combodir = $componentsdir.D.'combo';
        //$basecombodir = $combodir.D.'base';
        if (!is_dir($combodir)) {
            mkdir($combodir);
        }
        
        //Position radios
        $radiodir = $componentsdir.D.'radio';
        //$baseradiodir = $radiodir.D.'base';
        if (!is_dir($radiodir)) {
            mkdir($radiodir);
        }
        
        //Position forms
        $formdir = $componentsdir.D.'form';
        //$baseformdir = $formdir.D.'base';
        if (!is_dir($formdir)) {
            mkdir($formdir);
        }
        
        //Position print
        $printdir = $appdir.D.'print';
        if (!is_dir($printdir)) {
            mkdir($printdir);
        }
        
        //Position checkboxgroup
        $checkboxgroupdir = $componentsdir.D.'checkboxgroup';
        //$basecheckboxgroupdir = $checkboxgroupdir.D.'base';
        if (!is_dir($checkboxgroupdir)) {
            mkdir($checkboxgroupdir);
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
        
        $filter = new \Twig_SimpleFilter('getlocalfkcolumnname', function($childTableInfo){
        
            $belongsToArr = $childTableInfo->getBelongsTo();
            $str = $belongsToArr[0];
        
            $cols = $childTableInfo->getColumns();
            $localFkColumnName = "";
            $relatedTablePkColumnName = "";
        
            foreach ($cols as $c) {
                if ($c->getIsFk() == 1) {
                    //echo "- ".$c->getFkTableName()."<br>";
                    if ($c->getFkTableName() == $str) {
                        //echo "'-match!<br>";
                        $localFkColumnName = $c->getName();
                        $relatedTablePkColumnName = $c->getFkTableInfo()->getPkName();
                    }
                }
            }
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
            return str_replace("DataDikdas\\", "", get_class($object));
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
    
    public function generate(\Symfony\Component\HttpFoundation\Request $request, \Silex\Application $app) {

        // So that Silex's Request and Application accessible in any methods
        $this->setRequest($request);
        $this->setApp($app);
        
        // Get the config
        $config = $app['xond.config'];
        $this->setConfig($config);

        // Mark the start of gen process. Now using monolog
        $app['monolog']->addInfo("FrontEndGen start at ". date ( 'Y-m-d H:i' ));
                
        // Get the tables complete with their namespace (true), false otherwise.
        $maps = $this->getTables(BaseGen::TABLES_MAP);
        //echo sizeof($maps);
        
        
        $this->prepareFolders();
        $this->initializeTwig();
        
        // Process each table
        $written = 0;
        
        foreach ($maps as $tmap) {
           $this->addTable($tmap);
        }
    }
        

/*
            foreach ($classmap as $key=>$value) {
                	
                if (stripos($key, "TableMap") && (!stripos($key, "Vld")) && (!$this->cekSkipTable($key))) {
                    $key = str_replace("DataDikdas\\Model\\map\\", "DataDikdas\\Info\\", $key);
                    $key = str_replace("TableMap", "TableInfo", $key);
                    $outStr .= "$key<br>";
                    $obj = new ${'key'}();
                    //$obj = new TableInfo();
                    if ($obj->getName() == "sekolah") {
                        //print_r($obj);
                    }
                    //if ($obj->getName() == "agama") {
                    $rowsArr = array();

                    // Create radios
                    if ($obj->getCreateRadiogroup()) {
                        	
                        // Prepare data for static combos
                        $peerName = "DataDikdas\\Model\\".$obj->getPhpName()."Peer";
                        $count = $peerName::doCount(new \Criteria());
                        	
                        // If data too many, don't create radio
                        if ($count > InfoGen::SMALLREF_UPPER_LIMIT) {

                            $rowsArr = NULL;

                        } else if ($count > 0) {

                            $c = new \Criteria();
                            $rows = $peerName::doSelect(new \Criteria());

                            foreach ($rows as $r)  {
                                	
                                // Only for autocomplete (coding) purpose
                                //$r = new AksesInternet();
                                	
                                // If display field empty, don't display them
                                $nama = $r->getByName($obj->getDisplayField(), \BasePeer::TYPE_FIELDNAME);
                                if ($nama == '' || $nama == '0') {
                                    continue;
                                }
                                //$arr = $r->toArray(\BasePeer::TYPE_FIELDNAME);
                                $arr = array('valueField' => $r->getPrimaryKey(), 'displayField' => $nama);
                                $rowsArr[] = $arr;
                                	
                            }
                        }
                        //print_r($rowsArr); die;

                        // Prepare radiodir file
                        $filePath = $radiodir."/".$obj->getPhpName().".js";
                        $templateFileName = 'radio-template.js';
                        $array = array(
                                'appName' => APPNAME,
                                'table' => $obj,
                                'data' => $rowsArr
                        );
                        	
                        if ($this->render($obj->getName(), $filePath, $templateFileName, $array)) {
                            $written++;
                            $outStr .= "- $filePath written <br>\n";
                        }
                    }
                    	
                    // Create comboboxes
                    if ($obj->getCreateCombobox()) {
                        	
                        //print_r($rowsArr);
                        	
                        //Prepare data for static combos
                        $peerName = "DataDikdas\\Model\\".$obj->getPhpName()."Peer";
                        $count = $peerName::doCount(new \Criteria());

                        if ($count > InfoGen::BIGREF_LOWER_LIMIT) {
                            	
                            $rowsArr = NULL;
                            	
                        } else {
                            $c = new \Criteria();
                            $rows = $peerName::doSelect(new \Criteria());
                            	
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
                        $filePath = $combodir."/".$obj->getPhpName().".js";
                        if ($obj->getIsStaticRef()) {
                            $templateFileName = "combo-static-template.js";
                        } else if ($obj->getIsBigRef()) {
                            $templateFileName = "combo-paged-template.js";
                        } else {
                            $templateFileName = "combo-normal-template.js";
                        }
                        $array = array(
                                'appName' => APPNAME,
                                'table' => $obj,
                                'data' => $rowsArr
                        );
                        	
                        if ($this->render($obj->getName(), $filePath, $templateFileName, $array)) {
                            $written++;
                            $outStr .= "- $filePath written <br>\n";
                        }
                        	
                        //die;
                    }
                    	

                    // Create Form
                    //if ($obj->getCreateForm() && ($obj->getName() == "peserta_didik")) {
                    if ($obj->getCreateForm()) {
                        	
                        //Create list of columns registered inside a group
                        $colsInGroups = array();
                        	
                        //Creating a very simple registry of groups to check whether a group is added to the form
                        $groupsArr = array();

                        // Identify columns in group
                        $groups = $obj->getGroups();
                        $group_id = 0;

                        if ($groups) {
                            	
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
                        $colsInTable = $obj->getColumns();
                        	
                        // Create an empty form registry
                        $formMembers = array();
                        	
                        foreach ($colsInTable as $col) {

                            // Check whether the column used in groups
                            //$col = new ColumnInfo();
                            $columnName = $col->getName();

                            $foundInGroup = isset($colsInGroups["$columnName"]);
                            $groupIndex = @$colsInGroups["$columnName"];
                            	
                            if ($foundInGroup) {

                                //echo "Kolom $columnName termasuk group! <br>";
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
                        if ($obj->getName() == 'sekolah') {
                            //print_r($formMembers);
                            //die;
                        }
                        	
                        // Prepare formdir file
                        $filePath = $formdir."/".$obj->getPhpName().".js";
                        $templateFileName = 'form-template.js';
                        $array = array(
                                'appName' => APPNAME,
                                'table' => $obj,
                                //'columns' => $obj->getColumns()
                                'columns' => $formMembers
                        );

                        if ($this->render($obj->getName(), $filePath, $templateFileName, $array)) {
                            $written++;
                            $outStr .= "- $filePath written <br>\n";
                        }
                        	
                        // Prepare printdir file
                        /*
                        $filePath = $printdir."/".$obj->getPhpName().".php";
                        $templateFileName = 'print-template.php';
                        $array = array(
                                'appName' => APPNAME,
                                'table' => $obj,
                                //'columns' => $obj->getColumns()
                                'columns' => $formMembers
                        );
                        	
                        if ($this->render($obj->getName(), $filePath, $templateFileName, $array)) {
                        $written++;
                            $outStr .= "- $filePath written <br>\n";
                        }
			
                        }

                        // Create Grid
                                //if ($obj->getCreateGrid() && ($obj->getName() == "peserta_didik")) {
                                if ($obj->getCreateGrid()) {

                        // Prepare griddir file
                        $filePath = $griddir."/".$obj->getPhpName().".js";
                        $templateFileName = 'grid-template.js';
                        $array = array(
                            'appName' => APPNAME,
                            'table' => $obj,
                            'columns' => $obj->getColumns()
                                    );

                                            if ($this->render($obj->getName(), $filePath, $templateFileName, $array)) {
                                            $written++;
                                            $outStr .= "- $filePath written <br>\n";
                            }
                            }

                            // Create Controllers
                            //if ($obj->getCreateGrid() && ($obj->getName() == "peserta_didik")) {
                            if ($obj->getCreateGrid() || $obj->getCreateForm()) {

                            // Prepare basecontrollerdir file
                            $filePath = $basecontrollerdir."/".$obj->getPhpName().".js";
                            $templateFileName = 'controller-template.js';
                            $array = array(
                            'appName' => APPNAME,
                            'table' => $obj,
                            'columns' => $obj->getColumns(),
                            'vals' => $this->getInitialValue($obj->getPhpName())
                            );

					if ($this->render($obj->getName(), $filePath, $templateFileName, $array)) {
					$written++;
					$outStr .= "- $filePath written <br>\n";
                            }

                            // Prepare simplecontrollerdir file
                            $filePath = $simplecontrollerdir."/".$obj->getPhpName().".js";
                            $templateFileName = 'controller-simple-template.js';
					$array = array(
					        'appName' => APPNAME,
					        'table' => $obj,
					        'columns' => $obj->getColumns()
                            );
                            	
                            if ($this->render($obj->getName(), $filePath, $templateFileName, $array)) {
                            $written++;
                            $outStr .= "- $filePath written <br>\n";
            }
            	
            }

            // Create Models
            // Prepare basecontrollerdir file
            $filePath = $modeldir."/".$obj->getPhpName().".js";
            $templateFileName = 'model-template.js';
            $array = array(
                    'appName' => APPNAME,
                    'tableName' => $obj->getPhpName(),
                    'table' => $obj,
                    'columns' => $obj->getColumns()
                            );
                            if ($this->render($obj->getName(), $filePath, $templateFileName, $array)) {
                                $written++;
                                $outStr .= "- $filePath written <br>\n";
                            }

                            // Create Stores
                            $filePath = $storedir."/".$obj->getPhpName().".js";
				$templateFileName = 'store-template.js';
				$array = array(
				        'appName' => APPNAME,
				    'tableName' => $obj->getPhpName(),
				    'table' => $obj,
				            'columns' => $obj->getColumns()
				                );
				                if ($this->render($obj->getName(), $filePath, $templateFileName, $array)) {
				                $written++;
				                $outStr .= "- $filePath written <br>\n";
				                }


				}
				}

				$outStr .= 'Sejumlah '.$written.' file ditulis';
				return $outStr;

				}

				public function test(\Symfony\Component\HttpFoundation\Request $request, \Silex\Application $app){
				$test = new AgamaTableInfo();
				print_r($test);
				return "hello";
				}


				/*
				* Get initial values for an asked Table
				*
				* <p>Get initial values for an asked Table in PHPName format</p>
				        *
				        * @param string $tablePhpName The name of the table in PHPName Format
				        *
				        * @return array
				        * /
				        public function getInitialValue($tablePhpName) {

		$tablePhpName = "DataDikdas\\Info\\".$tablePhpName."TableInfo";

		$tableInfo = new ${'tablePhpName'};
		//$tableInfo = new PesertaDidikTableInfo();
		$cols = $tableInfo->getColumns();
		$colnum = 0;

		foreach ($cols as $c) {
			
		//$c = new ColumnInfo();
		unset($fkId);
			
		$colName = $c->getColumnName();
		    	
		    // Prepare max/min value for numeric foreign keys
		    $relatedTblName = $c->getFkTableName();
			$relatedPeerName = "DataDikdas\\Model\\".$relatedTblName ."Peer";

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
							die ("FK Object / referensi gak ketemu untuk $tableName.$colName. Mohon tambahkan data di tabel referensi ybs.");
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
	*/
}
