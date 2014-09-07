<?php

namespace Xond\gen;

use \Symfony\Component\HttpFoundation\Request;
use \Silex\Application;
    
class InfoGen extends BaseGen {
    
    // This one should go to config
    const BIGREF_LOWER_LIMIT = 20;
    const SMALLREF_UPPER_LIMIT = 4;

    public function addColumns(TableInfo $table) {
        
    }
    public function generate(Request $request, Application $app) {
        
        global $config;
        
        // So that Silex's Request and Application accessible in any methods
        $this->setRequest($request);
        $this->setApp($app);

        // Mark the start of gen process. Now using monolog
        $app['monolog']->addInfo("Gen start at " . date ( 'Y-m-d H:i' ));
        
        // Get the tables complete with their namespace (true), false otherwise.
        $tables = $this->getTables(true);
        
        // Init shit
        $objNames = $maps = array();
        $outStr = "<pre>";
        
        // Call maps
        foreach ($tables as $t) {
            
            //$objNames[] = get_class($this->getMap($t));  //Debug shit
            $maps[] = $this->getMap($t);
            
        }
        
        // Loop for each map
        foreach ($maps as $tmap) {
            
            // Init
            $tableIsRef = false;
            
            // Identify 
            $tmapArr ["name"] = $tmap->getName ();
            $tmapArr ["php_name"] = $tmap->getPhpName ();
            
            
            // Reset all first
            $columns = array ();
            $relation = array ();
            $colnum = 1;
            $cols = $tmap->getColumns ();
            $outStr .= $tmap->getName () . "<br>";
            $tvar = "";
            
            // Loop for each columns in tablemap
            foreach ( $cols as $c ) {
                
                // Initialize
                $isPK = $isFK = $isNumeric = $isText = $isNotNull = $isUUID = $isFKUUID = $isFKNUM = false;
                
                // Identify
                $colName = $c->getName ();
                $colPhpName = $c->getPhpName ();
                
                // Get from configs
                global $skipColumns;
                
                // Skip the shit following the config
                if ( in_array ($colName, $skipColumns) ) {
                    continue;
                }
                
                
                //$objNames[] = $c->getType();
                
                // Check Status
                $firstColumn = ($colnum == 1) ? true : false;
                $colnum ++;
                
                // Detect Properties Of the Column
                $isPK = $c->isPrimaryKey();
                $isFK = $c->isForeignKey();
                $isNumeric = $c->isNumeric();
                $isText = $c->isText();
                $isNotNull = $c->isNotNull();
                
                
                // Arrange the type & general type.
                $type = $c->getType ();
                
                $objNames[] = $type;
                $objNames[] = $c->getSize();
                
                $generalType = $type;
                $generalType = $isText ? "TEXT" : $generalType;
                $generalType = $isNumeric ? "NUMERIC" : $generalType;
                
                // Size (is it important now ?)
                $size = $c->getSize () ? $c->getSize () : 0;
                
                if ($isText) {
                    $size = $c->getSize () ? $c->getSize () : 0;
                } else if ($isNumeric) {
                    $size = 100;
                }
                
                // After type found, set isUUID, isFKUUID, isFKNUM
                // Check if it's a UUID, hence call UUID generator
                if ($isPK && $isText) {
                    $isUUID = true;
                    $generalType = "PKUUID";
                }
                    
                // Check if it's a FK UUID, hence call UUID generator for false values
                if ($isFK && $isText) {
                    $isFKUUID = true;
                    $generalType = "FKUUID";
                }
                
                if ($isFK && $isNumeric) {
                    $isFKNUM = true;
                    $generalType = "FKNUM";
                }
                
                // Prepare max/min value for numeric foreign keys
                // $isRefColumn = 1;
                $relatedTblName = '';
                $relatedPeerName = '';
                
                $isBigRefColumn = $isRefColumn = $isStaticRefColumn = $isSmallFKNUM = 0;
                
                $recNum = 0;
                
                if ($isFK) {
                
                    // $isBigRef = $isStaticRef = 0;
                    $relTblName = $c->getRelatedTableName ();
                    // print_r($relTblName); echo "<br>";
                    
                    // Read table name, if it contains schema name - separated by ".", remove it 
                    $arrTblName = explode(".", $relTblName);
                    if (sizeof($arrTblName) > 1) {
                        //$relTblName = str_replace ( "ref.", "", $relTblName );
                        $relTblName = $arrTblName[1];
                    }
                    
                    $relatedTblName = phpNamize ($relTblName);
                    $relatedPeerName = $config["nama_singkat"]."\\Model\\" . $relatedTblName . "Peer";
                    
                    //echo $relatedPeerName."<br>"; continue;
                    
                    //if ($isFKNUM ) {
                                 
                        $recNum = $relatedPeerName::doCount ( new \Criteria () );
                        echo $relatedPeerName."<br>"; 
                        
                        $min = 0;
                        $max = $recNum;
                
                        if ($recNum > InfoGen::BIGREF_LOWER_LIMIT) {
                            
                        } else if ($recNum > InfoGen::SMALLREF_UPPER_LIMIT) {
                            
                        } else {
                            $isSmallFKNUM = true;
                        }
                        
                    //} else {
                        
                    //}
                    
                } else {
                    $min = 0;
                    $max = 99999999;
                }
                
                // Debug
                $outStr .= "- " . $colPhpName . " | " . $type = $c->getType () . "( " . $c->getSize () . " ) | " . ($isNotNull ? "NOT NULL" : "NULL") . " | " . ($isPK ? "PK" : "-") . " | " . ($isFK ? "FK" : "-") . " | " . ($isFKNUM ? "FKNUM" : "-") . " | ". $recNum. " records <br>";
                
                
                
            }
            
            
        }
        
        //return implode(", ", $tables);
        //return implode(", ", $objNames);
        return $outStr;
    }
    
}