<?php

/**
 * This file is part of the Xond package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Xond\Gen;

use \Symfony\Component\HttpFoundation\Request;
use \Silex\Application;
use Propel\Silex\PropelServiceProvider;
use Symfony\Component\Security\Acl\Exception\Exception;


/**
 * This is a utility info generator class for extracting table information 
 * that based on Propel objects, and store it in BaseInfo classes (TableInfo, ColumnInfo, etc)
 * The extending Info classes than will be able to be modified and persist the modification
 * even though the database and the BaseInfo is dynamically updated. 
 *
 * @author     Donny Fauzan <donny.fauzan@gmail.com> (Nufaza)
 * @version    $Revision$
 * @package    xond.gen
 */

class InfoGen extends BaseGen {
    
    // Constants
    public $genType = "Info";
    
    // This one should go to config
    const BIGREF_LOWER_LIMIT = 20;
    const SMALLREF_UPPER_LIMIT = 4;
    
    const IS_SMALL_TABLE = 1;
    const IS_MEDIUM_TABLE = 2;
    const IS_BIG_TABLE = 3;
    
    const PIX_PER_CHAR_FIELD = 12;
    const PIX_PER_CHAR_COLUMN = 4;
    const PIX_PER_CHAR_LABEL = 7;
    
    // Model Types
    const TYPE_STRING = "string";
    const TYPE_INT = "int";
    const TYPE_FLOAT = "float";
    const TYPE_DATE = "date";
    const TYPE_TIMESTAMP = "timestamp";
    
    // Field Xtypes
    const FIELD_TEXT = 'textfield';
    const FIELD_TEXTAREA = 'textareafield';
    const FIELD_NUMBER = 'numberfield';
    const FIELD_DATE = 'datefield';
    const FIELD_COMBO = 'combobox';
    const FIELD_RADIO = 'radiogroup';
    
    // Nature
    const NATURE_PEOPLE_NAME = 'peoplename';
    const NATURE_EMAIL_ADDRESS = 'email';
    const NATURE_INTEGER = 'int';
    
    /**
     * Map Propel types to Ext
     * @var array
     */
    private static $extTypeMap = array(
            
        \PropelTypes::CHAR => InfoGen::TYPE_STRING,
        \PropelTypes::VARCHAR => InfoGen::TYPE_STRING,
        \PropelTypes::LONGVARCHAR => InfoGen::TYPE_STRING,
        \PropelTypes::CLOB => InfoGen::TYPE_STRING,
        \PropelTypes::CLOB_EMU => InfoGen::TYPE_STRING,

        \PropelTypes::TINYINT => Infogen::TYPE_INT,
        \PropelTypes::SMALLINT => Infogen::TYPE_INT,
        \PropelTypes::INTEGER => Infogen::TYPE_INT,
        \PropelTypes::BIGINT => Infogen::TYPE_INT,

        \PropelTypes::REAL => InfoGen::TYPE_FLOAT,
        \PropelTypes::FLOAT => InfoGen::TYPE_FLOAT,
        \PropelTypes::DOUBLE => InfoGen::TYPE_FLOAT,
        \PropelTypes::DECIMAL => InfoGen::TYPE_FLOAT,
        \PropelTypes::NUMERIC => InfoGen::TYPE_FLOAT,

        \PropelTypes::BINARY => InfoGen::TYPE_STRING,
        \PropelTypes::VARBINARY => InfoGen::TYPE_STRING,
        \PropelTypes::LONGVARBINARY => InfoGen::TYPE_STRING,
        \PropelTypes::BLOB => InfoGen::TYPE_STRING,

        \PropelTypes::DATE => InfoGen::TYPE_DATE,
        \PropelTypes::TIME => InfoGen::TYPE_DATE,
        \PropelTypes::TIMESTAMP => InfoGen::TYPE_TIMESTAMP,
        \PropelTypes::BU_DATE => InfoGen::TYPE_DATE,
        \PropelTypes::BU_TIMESTAMP => InfoGen::TYPE_DATE,

        \PropelTypes::BOOLEAN => Infogen::TYPE_INT,
        \PropelTypes::ENUM => Infogen::TYPE_INT
            
    );    
    // Helper vars
    protected $tables = array();
    
    /**
     * Add tables to $this->tables array
     * 
     * @param TableMap $tmap
     */
    public function addTable(\TableMap $tmap) {
        
        // Load config
        $config = $this->getConfig();
        $refSchemaArr = explode(",", $config["reference_schemas"]);
        
        // Identify
        $t["name"] = $this->getName($tmap);
        $t["php_name"] = $tmap->getPhpName();
        $t["app_name"] = $config['project_php_name'];
        $t["schema_name"] = $this->getSchemaName($tmap);
        
        // Check if schema is reference
        if (sizeof($refSchemaArr) > 0) {
            
            if (in_array($t["schema_name"], $refSchemaArr)) {
                $isReference = true;
            } else {
                $isReference = false;
            }
        }
        
        // Determine primary keys, and add virtual column to acommodate composite keys
        if (sizeof($tmap->getPrimaryKeyColumns()) > 1) {
            
            $t["composite_pk"] = 1;
            $t["pk_name"] = $this->getName($tmap)."_id";
            
        } else {
            
            $t["composite_pk"] = 0;
            
            //Need to set pk_name here because createCompositeKeyDelegationId() does it too
            $pkColumns = $tmap->getPrimaryKeyColumns();
            $pkColumn = $pkColumns[0];
            $t["pk_name"] = $pkColumn->getName();
            
        }
        
        // Determine type of the table, small, medium or large
        $peerName = $tmap->getPeerClassname();
        $count = $peerName::doCount(new \Criteria());
        $t["record_count"] = $count;
        
        if ($count <= InfoGen::SMALLREF_UPPER_LIMIT) {
            $t["size_type"] = InfoGen::IS_SMALL_TABLE;
            $t["is_static_ref"] = 1;            // create radio & static combo
            $t["is_small_ref"] = 1;             // prioritize radio
            $t["is_medium_ref"] = 0;            // create search combo
            $t["is_big_ref"] = 0;               // create search combo
            
        } else if ($count >= InfoGen::BIGREF_LOWER_LIMIT) {
            $t["size_type"] = InfoGen::IS_BIG_TABLE;
            $t["is_static_ref"] = 0;            // create radio & static combo
            $t["is_small_ref"] = 0;             // prioritize radio
            $t["is_medium_ref"] = 1;            // create search combo
            $t["is_big_ref"] = 1;               // create search combo

        } else {
            $t["size_type"] = InfoGen::IS_MEDIUM_TABLE;
            $t["is_static_ref"] = 1;            // create radio & static combo
            $t["is_small_ref"] = 0;             // prioritize radio
            $t["is_medium_ref"] = 1;            // create search combo
            $t["is_big_ref"] = 0;               // create search combo

        }

        // Determine display field. First try with Xond 1 standard, "nama"
        // This should be configurable. Put on a ticket.
        if ($tmap->hasColumn("nama")) {
            $t["display_field"] = "nama";
        // Else loop the table, first occurance of string will be it
        } else {
            
            foreach ($tmap->getColumns() as $c) {
                //$c = new \ColumnMap();
                if ($c->getType() == \PropelTypes::VARCHAR) {
                    $t["display_field"] = $c->getName();
                    break;
                }
            }
            
            if (!isset($t["display_field"])) {
                $pks = $tmap->getPrimaryKeyColumns();
                $t["display_field"] = $pks[0]->getName();
            }
        }
        
        // Determine renderer string name. 
        // Useful for giving related object's name without the need of front end renderer.
        // For example, a user table has city_of_residence as foreign key. 
        // The REST proxy will giveout the user table's city_of_residence_id along with the city_of_residence_str. 
        $t["renderer_string"] = $t['pk_name']."_str";

        // Table Header
        $t["header"] = humanize($t['name']);
        
        // Form Label (If the table called as FKs)
        $t["label"] = sentencesize($t['name']);
        
        // Widget generation options
        $t["create_combobox"] = 1;
        $t["create_radiogroup"] = ($t["size_type"] == InfoGen::IS_SMALL_TABLE) ? 1 : 0;
        $t["create_list"] = true;
        $t["create_model"] = true;
        $t["xtype_combo"] = str_replace("_", "", $t['name']).'combo';
        $t["xtype_radio"] = str_replace("_", "", $t['name']).'radio';
        $t["xtype_list"] = str_replace("_", "", $t['name']).'list';
        
        // Relation stuff
        $rManyToMany = array();
        $rHasMany = array();
        $rBelongsTo = array();
        $rOneToOne = array();
        
        $relations = $tmap->getRelations();
        
        foreach($relations as $r) {
            
            switch ($r->getType()) {
                case \RelationMap::ONE_TO_MANY:
                    $rHasMany[] = $r->getRightTable()->getName();
                    break;
                case \RelationMap::MANY_TO_ONE:
                    $rBelongsTo[] = $r->getRightTable()->getName();
                    break;
                case \RelationMap::ONE_TO_ONE:
                    $rOneToOne[] = $r->getRightTable()->getName();
                    break;
            }
            
        }
        
        //if ($t['name'] == 'sekolah') {
        //    print_r($rManyToMany);
        //    print_r($rHasMany);
        //    print_r($rBelongsTo);
        //    print_r($rOneToOne);
        //    //die;
        //}
        
        
        // Determine data or ref
        $pkColumns = $tmap->getPrimaryKeyColumns();
        $pkColumn = $pkColumns[0];
        
        //if ($t['name'] == 'sumber_dana') {
        //    echo $pkColumn->getType()."<br>";
        //    echo InfoGen::$extTypeMap[$pkColumn->getType()]."<br>";
        //    echo $count;
        //    die;
        //}
        
        if ($t['composite_pk']) {
            $isData = 1;            
        } else if ($this->checkIsRef($tmap)) {
            $isData = 0;
        } else if ( ($pkColumn->isNumeric()) && ($count < InfoGen::BIGREF_LOWER_LIMIT)) {
            $isData = 0;
        } else {
            $isData = 1;
        }
        
        if (isset($isReference)) {
            $isData = $isReference ? 0 : 1;
        }
        
        if ($isData) {

            // If clearly stated (or by detection)
            $t["is_data"] = 1;
            $t["is_ref"] = 0;
            $t["create_grid"] = 1;
            $t["create_form"] = 1;

            //reset refs
            $t["is_static_ref"] = 0;
            $t["is_small_ref"] = 0;
            
        } else {

            // Assume it's ref
            $t["is_data"] = 0;
            $t["is_ref"] = 1;
            $t["create_grid"] = 0;
            $t["create_form"] = 0;
        }
        
        
        // Do we need this ? YES
        $t["has_many"] = implode(',', $rHasMany);
        $t["belongs_to"] = implode(',', array_merge($rBelongsTo, $rOneToOne));
        $t["is_split_entity"] = (sizeof($rOneToOne) > 0) ? 1 : 0;
        $t["has_split_entity"] = 0;
        $t["split_entity_name"] = '';
        $t["relating_columns"] = implode(',', $rHasMany);
        $t["info_before_delete"] = '';
        
        if ($t['name'] == 'sekolah') {
            //echo "\r\nhas_many: ".$t["has_many"];
            //echo "\r\nbelongs to: ".$t["belongs_to"];
            //die;
        }
        
        // Register
        $this->registerTable($t);
        
        // Add the composite column
        if (sizeof($tmap->getPrimaryKeyColumns()) > 1) {
            $this->createCompositeKeyDelegationColumn($tmap);
        }
        
    }
    
    

    
    /**
     * Register the table to $this->tables
     * @param array $t
     */
    public function registerTable($t) {
        
        $tName = $t["name"];
        $this->tables[$tName] = $t;
        
    }    
    
    /**
     * Set PKName for the given Table represented by The TableMap
     *
     * @param \TableMap $tmap
     * @param string $pkName
     */
    public function setPkName(\TableMap $tmap, $pkName) {
    
        $tableName = $this->getName($tmap);
        $this->tables[$tableName]["pk_name"] = $pkName;
    
    }
    
    /**
     * Get PKName for the given Table represented by The TableMap
     *
     * @param \TableMap $tmap
     * @return string
     */
    public function getPkName(\TableMap $tmap) {
    
        $tableName = $this->getName($tmap);
        return $this->tables[$tableName]["pk_name"];
    
    }
    
    /**
     * Add columns to the one $table of $this->tables array
     * Also calculate longest strlen.
     * 
     * @param TableMap $table
     */
    public function addColumns(\TableMap $tmap) {
    
        $config = $this->getConfig();
        $maxLabelWidth = 0;
        
        foreach ($tmap->getColumns() as $c) {
            
            // For autocomplete purpose. NEED TO BE COMMENTED ON RUN
            // $c = new \ColumnMap($name, $containingTable);
            
            $colName = $c->getName();
            $colPhpName = $c->getPhpName();
    
            if (is_string($config['front_end_skip_columns'])) {
                $skipColumnsStr = str_replace(" ", "", $config['front_end_skip_columns']);
                $arr = explode(",", $skipColumnsStr);
            } else if (is_array($config['front_end_skip_columns'])) {
                $arr = $config['front_end_skip_columns'];
            } else {
                $skipColumnsStr = "";
                $arr = explode(",", $skipColumnsStr);
            }
            
            if (contains($colName, $arr)) {
                continue;
            }
            
            // Identify
            $isPk = boolToNum($c->isPrimaryKey());
            $isFk = boolToNum($c->isForeignKey());
            $isUuid = boolToNum($this->isUuid($c));
            $isPkUuid = boolToNum($c->isPrimaryKey() && $this->isUuid($c));
                        
            // Insert            
            $cArr['column_php_name'] = $colPhpName;
            $cArr['column_name'] = $colName;
            $cArr['column_length'] = $this->getColumnLength($c);
            $cArr['input_length'] = $this->getColumnLength($c);
            $cArr["type"] = InfoGen::$extTypeMap[$c->getType()];
            
            $cArr["is_pk_uuid"] = $isPkUuid;
            $cArr["is_pk"] = $isPk;
            $cArr["is_fk"] = $isFk;
            $cArr["fk_table_name"] = ($isFk) ? $this->getName($c->getRelatedTable()) : "";
            
            $cArr['min'] = 0;
            $cArr['max'] = ($c->isNumeric()) ? pow(10, $this->getColumnLength($c))-1 : 0;
            
            $cArr['label'] = sentencesize($this->getColumnLabel($c));
            $cArr["header"] = humanize($this->getColumnLabel($c));
            
            $colWidth = $this->getColumnLength($c) * InfoGen::PIX_PER_CHAR_COLUMN;
            $colWidth = ($colWidth > 80) ? $colWidth : 80;
            
            $cArr['column_width'] = $colWidth;
            $cArr['hide_column'] = $isPk ? 1 : 0;
            $cArr['field_width'] = $this->getColumnLength($c) * InfoGen::PIX_PER_CHAR_FIELD;
            $cArr['display_field'] = $this->getColumnDisplayField($c);
            
            list($cArr['xtype'], $static) = $this->getColumnXtype($c);
            
            if ($isFk) {
                $cArr['combo_xtype'] = strtolower(phpNamize($c->getRelatedTable()->getPhpname()))."combo";
                $cArr['radiogroup_xtype'] = strtolower(phpNamize($c->getRelatedTable()->getPhpname()))."radiogroup";
            } else {
                $cArr['combo_xtype'] = "";
                $cArr['radiogroup_xtype'] = "";
            }
            
            $cArr['allow_empty'] = $c->isNotNull() ? 1 : 0;
            $cArr['validation'] = '';
            $cArr['description'] = '';
            
            // Calculate maxLabelWidth
            if (strlen($colPhpName) > $maxLabelWidth) {
                $maxLabelWidth = strlen($colPhpName); 
            } 
            
            $this->registerColumn($tmap, $cArr);
            
        }
        $this->setFormDefaultLabelWidth($tmap, $maxLabelWidth);
    }
    
    /**
     * Register the column to the $this->tables
     *
     * @param \TableMap $tmap
     * @param array $a
     */
    public function registerColumn(\TableMap $tmap, array $cArr) {
    
        $tableName = $this->getName($tmap);
        
        // Register
        $this->tables[$tableName]["columns"][] = $cArr;
    
    }

    /**
     * Get column length, by directly get from the ColumnMap,
     * or by checking the related data. 
     * "Length" here represents how many characters to display the field.
     *
     * @param \ColumnMap $column
     * @return number
     */
    public function getColumnLength(\ColumnMap $column) {
        
        $size = $column->getSize();
        if ($this->isUuid($column)) {
            $size = 36;
        }
        
        return $size;
         
    }
    
    /**
     * Get the column label for forms or header for grid
     * 
     * @param \ColumnMap $column
     * @return string|Ambigous <string, string>
     */
    public function getColumnLabel(\ColumnMap $column) {
        
        if ($column->isPrimaryKey()) {
            return 'ID';
        } else if ($column->isForeignKey()) {
            return str_replace('_id', '', $column->getColumnName());
        } else {
            return $column->getColumnName();
        }
        
    }
    
    /**
     * Get display field for the column. Only for foreign key
     * 
     * @param \ColumnMap $column
     * @return string
     */
    public function getColumnDisplayField(\ColumnMap $column) {
        try {
            if ($column->isForeignKey()) {
                return $this->tables[$this->getName($column->getRelatedTable())]["display_field"];
            } else {
                return "";
            }
        } catch (\Exception $e) {
            die("The column ".$column->getName()." is foreign key, but doesn't have any display_field.");
        }
    }
    
    /**
     * Get Column's Xtype. This predict the correct xtypes for fields. Can be overridden.
     * 
     * @param \ColumnMap $column
     * @return array                Returns array of string of xtype, and int (combo: rec per page, radio: num of columns), and staticness
     */
    public function getColumnXtype(\ColumnMap $column) {
        
        // Init
        $xtype = $static = $number = "";
        
        // The column is Foreign Key
        if ($column->isPrimaryKey() && (!$column->isForeignKey())) {
            $xtype = 'hidden';
        }
        else if ($column->isForeignKey()) {
            
            $recordCount = $this->tables[$this->getName($column->getRelatedTable())]["record_count"];
            $sizeType =  $this->tables[$this->getName($column->getRelatedTable())]["size_type"];
            
            switch($sizeType) {
                
                case InfoGen::IS_SMALL_TABLE:
                    // $xtype = InfoGen::FIELD_RADIO;
                    $xtype = InfoGen::FIELD_COMBO;
                    $static = 1;
                    if ($recordCount <= 4) {
                        $number = 0;        // auto, no columns
                    } else if ($recordCount % 3 === 0) {
                        $number = 3;
                    } else if ($recordCount % 4 === 0) {
                        $number = 4;
                    } else if ($recordCount % 4 === 0) {
                        $number = 5;
                    } else {
                        $number = 4;
                    }
                    break;
                    
                case InfoGen::IS_MEDIUM_TABLE:
                    // $xtype = strtolower($column->getRelatedTable()->getPhpName())."combo";
                    $xtype = InfoGen::FIELD_COMBO;
                    $static = 1;
                    $number = 10;
                    break;
                    
                case InfoGen::IS_BIG_TABLE:
                    // $xtype = strtolower($column->getRelatedTable()->getPhpName())."combo";
                    $xtype = InfoGen::FIELD_COMBO;
                    $static = 0;
                    $number = 10;
                    break;
            }
        }
        
        // Column is regular column
        else if ($column->isText() && ($column->getType() == \PropelTypes::LONGVARCHAR)) {
            $xtype = InfoGen::FIELD_TEXTAREA;
        }
        else if ($column->isText()) {
            $xtype = InfoGen::FIELD_TEXT;
        }
        else if ($column->isNumeric()) {
            $xtype = InfoGen::FIELD_NUMBER;
        }
        else if (InfoGen::$extTypeMap[$column->getType()] == InfoGen::TYPE_DATE) {
            $xtype = InfoGen::FIELD_DATE;
        } 
        else if (InfoGen::$extTypeMap[$column->getType()] == InfoGen::TYPE_TIMESTAMP) {
            $xtype = InfoGen::FIELD_DATE;
        }
        else {
            $xtype = 'undefined';
        }
        
        return array($xtype, $static);
    }
    
    /**
     * Get the relations (FK's) size of table data, to determine
     * whether this column is big or small. If small, the application will
     * generate radio button; Bigger: static combo box; Too big: remote search combo
     *
     * @param \ColumnMap $column
     * @return number
     */
    public function getRelatedFkTableSize(\ColumnMap $column) {
    
        return $this->tables[$this->getName($column->getRelatedTable())]["record_count"];
         
    }
    
    /**
     * Get UUID string length. Could be differ between databases .
     * 
     * @return number
     */
    public function getUuidLength() {
        $db = \Propel::getDB();
        return 16;
    }
    
    /**
     * Check whether the primary key is an UUID. With the ACCURATE guess :)
     *
     * @param \ColumnMap $column
     * @return boolean
     */
    public function isUuid(\ColumnMap $column) {
        
        return ($column->getSize() == $this->getUuidLength()) && ($column->getType() == \PropelTypes::CHAR);
        
    }
    
    /**
     * For tables with composite keys, create single id that represent all keys
     * 
     * @param \TableMap $tmap
     */
    public function createCompositeKeyDelegationColumn(\TableMap $tmap) {
        
        // Create virtual PK column
        $virtualPkName = $this->getName($tmap)."_id";
        
        // Calculate length
        $length = 0;
        foreach ($tmap->getPrimaryKeyColumns() as $column) {
            $length += $this->getColumnLength($column);
            $length++;
        }
        
        // Fill
        $cArr['column_php_name'] = phpnamize($virtualPkName);
        $cArr['column_name'] = $virtualPkName;
        $cArr['column_length'] = $length;
        $cArr['input_length'] = 100;
        $cArr["type"] = 'string';
        
        $cArr["is_pk_uuid"] = 0;
        $cArr["is_pk"] = 1;
        $cArr["is_fk"] = 0;
        $cArr["fk_table_name"] = '';
        $cArr["is_virtual"] = 1;
        
        $cArr['min'] = 0;
        $cArr['max'] = 0;
        
        $cArr['label'] = ' ';
        $cArr["header"] = ' ';
        
        $cArr['column_width'] = $cArr['column_length'] * InfoGen::PIX_PER_CHAR_FIELD;
        $cArr['hide_column'] = 1;
        $cArr['field_width'] = $cArr['column_length'] * InfoGen::PIX_PER_CHAR_COLUMN;;
        $cArr['display_field'] = '';
        $cArr['xtype'] = 'hidden';
        $cArr['combo_xtype'] = strtolower(phpnamize($virtualPkName));
        $cArr['validation'] = '';
        $cArr['allow_empty'] = 0;
        $cArr['description'] = '';
        
        // Register Column
        $this->registerColumn($tmap, $cArr);
        
        // Set PK Name to the this virtual PK column
        // $this->setPkName($tmap, $virtualPkName);
        
    }

    /**
     * This function corrects "BelongsTo" list so that only tables
     * listed is amongst data table and then change their format
     * to php_name format
     * 
     * @param \TableMap $tmap
     * @return type
     */
    public function addManyToOneRelationInformation(\TableMap $tmap) {
        
        /*
        // Reset everything first
        $belongsTo = array();
        	
        // Get FK info from the map
        $fkArr = $tmap->getForeignKeys();
        	
        if (is_array($fkArr)) {
        
            foreach ($fkArr as $key => $fk) {
                	
                $fkname = $fk->getName();
                	
                foreach ($this->tables as $t) {
                    	
                    // Match
    
                    // DEBUG
                    // echo "$fkname | ".$t["pk"]." | ". (($fkname == $t["pk"]) ? "SAMA" : "BEDA")."<br>" ;
                    // if ($tvars[$i]['name'] == "ptk_terdaftar") {
                    // echo "$fkname | ".$t["pk"]." | ". (($fkname == $t["pk"]) ? "SAMA" : "BEDA")."<br>" ;
                    // }
    
                    if ($fkname == $t["pk_name"]) {
                        if ($t["is_data"] == 1) {
                            $belongsTo[] = $t["php_name"];
                        }
                    }
                }
            }
            
        } else {
            
            return;
            
        }
        */
        $belongsTo = explode(",", $this->tables[$this->getName($tmap)]["belongs_to"]);
        $newBelongsTo = array();

        foreach ($belongsTo as $b) {

            if (strpos($b, ".") > 0) {
                list($ref,$tableName) = explode(".", $b);                
            } else {
                $tableName = $b;
            }
            //echo $tableName."\r\n";

            foreach ($this->tables as $t) {
                
                if ($t["name"] == $tableName) {                        
                    if ($t["is_data"] == 1) {
                        $newBelongsTo[] = $t["php_name"];
                    }
                }                    
                
            }                
        }

        $this->tables[$this->getName($tmap)]["belongs_to"] = $newBelongsTo;
        
    }

    public function addOnetoManyRelationInformation(\TableMap $tmap) {

        $hasMany = explode(",", $this->tables[$this->getName($tmap)]["has_many"]);
        $newHasMany = array();
       
        foreach ($hasMany as $h) {
            
            if (strpos($h, ".") > 0) {
                list($ref,$tableName) = explode(".", $h);                
            } else {
                $tableName = $h;
            }
            
            foreach ($this->tables as $t) {
                if ($t["name"] == $tableName) {                        
                    if ($t["is_data"] == 1) {
                        $newHasMany[] = $t["php_name"];
                    }
                }                    
            }                
        }

        $this->tables[$this->getName($tmap)]["has_many"] = $newHasMany;

    }
    
    /**
     * Set the table's default label width for forms
     * @param \TableMap $tmap
     * @param number $formDefaultWidth
     */
    public function setFormDefaultLabelWidth(\TableMap $tmap, $formDefaultLabelWidth = 80) {
        $this->tables[$this->getName($tmap)]["form_default_label_width"] = roundUp($formDefaultLabelWidth,10) * InfoGen::PIX_PER_CHAR_LABEL;
    }
    
    
    /**
     * Output the tables as Info files
     * 
     * @param \TableMap $tmap
     * @throws Exception
     */
    public function toFile(\TableMap $tmap) {
        
        // Prepare Folders
        $app = $this->getApp();
        $config = $this->getConfig();
            
        $classPath = $app['xond.config']['class_folder'];
        $infoPath = $classPath.DIRECTORY_SEPARATOR.'Info';
        $baseInfoPath = $infoPath.DIRECTORY_SEPARATOR.'base';
        
        if (!is_dir($baseInfoPath)) { 
            if (!mkdir($baseInfoPath, 0777, true)) {
                throw new Exception('Failed to create folder base info folder');
            }
        }
        
        /** Prepare twig **/
        $templateRoot = realpath(__DIR__."/templates/php");
        
        // Loader path
        $loader = new \Twig_Loader_Filesystem($templateRoot);
        
        // The twig object
        // $twig = new \Twig_Environment($loader);
        $twig = new \Twig_Environment($loader, array(
            'debug' => true
        ));

        // Add custom filter "sizeof"
        $filter = new \Twig_SimpleFilter('sizeof', function($array){
            return sizeof($array);
        });
        $twig->addFilter($filter);
        
        // Base Files
        $templateFileName = 'info-template-base.twig';
        $fileName = "Base{$tmap->getPhpName()}TableInfo.php";
        $targetPath = $baseInfoPath.DIRECTORY_SEPARATOR.$fileName;
        
        // Apply template
        try {
            $tplStr = $twig->render($templateFileName, $this->tables[$this->getName($tmap)]);
            $this->writeToFile($tmap, $tplStr, $targetPath);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // Info Files
        $templateFileName = 'info-template.twig';
        $fileName = "{$tmap->getPhpName()}TableInfo.php";
        $targetPath = $infoPath.DIRECTORY_SEPARATOR.$fileName;
        
        if (!is_file($targetPath)) { 
            
            // Apply template
            try {
                $tplStr = $twig->render($templateFileName, $this->tables[$this->getName($tmap)]);
                $this->writeToFile($tmap, $tplStr, $targetPath);
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
            
        }
    }
    
    /**
     * Write twig rendered string to the requested path
     * 
     * @param \TableMap $tmap
     * @param string $str
     * @param string $targetPath
     * @throws Exception
     */
    public function writeToFile(\TableMap $tmap, $str, $targetPath) {
        
        if (!isset($this->written)) {
            $this->written = 0;
        }
        $fp = fopen($targetPath, 'w');
        if (!$fp) {
            throw new Exception("Failed to create file ($targetPath)");
        }
        if (fwrite($fp, $str)) {
            $this->written++;
        } else {
            throw new Exception("Failed to write file ($targetPath)");
        }
        fclose($fp);
        $this->tables[$this->getName($tmap)]["written"] = 1;
        
    }
    

    /**
     * Output the tables as string
     * @return string
     */
    public function toStr(){
    
        $outStr = "";
    
        foreach ($this->tables as $t){
            
            $written = isset($t['written']) ? " - written" : "";
            $tableType = $t['is_data'] ? " - data " : " - ref ";
            $outStr .= "{$t['name']} $tableType $written<br>\r\n";
            foreach ($t['columns'] as $c) {
                $outStr .= "- ".$c["column_name"]." (size: {$c['column_length']} xtype: {$c['xtype']})<br>\r\n";
            }
            
        }
        $outStr .= "<br>\r\nSejumlah {$this->written} file berhasil ditulis";
        
        return $outStr;
    }
        
    /**
     * Main "Generate" method. Called directly from XondServiceProvider.
     * 
     * Generates InfoTables
     * 
     * @param Request $request
     * @param Application $app
     * @return string
     */
    public function generate(Request $request, Application $app) {
        
        error_reporting(E_ALL);
        
        // Initialize
        $this->initialize($request, $app);
               
        // Get the tables complete with their namespace (true), false otherwise.
        $maps = $this->getTables(BaseGen::TABLES_MAP);        
        //echo sizeof($maps);
        
        // Init shit
        $objNames = array();
        $outStr = "<pre>";
        
        // Complete the tmap with necessary infos
        foreach ($maps as $tmap) {
            $this->addTable($tmap);
        }
        
        // Add columns so the app can utilize the data
        foreach ($maps as $tmap) {
            $this->addColumns($tmap);
        }
        
        // Add correct relation iformation
        foreach ($maps as $tmap) {
            $this->addManyToOneRelationInformation($tmap);
            $this->addOnetoManyRelationInformation($tmap);
        }
        
        // Complete the tmap with necessary infos
        foreach ($maps as $tmap) {
            $this->toFile($tmap);
        }
        
        return $this->toStr();
        
    }
    
}