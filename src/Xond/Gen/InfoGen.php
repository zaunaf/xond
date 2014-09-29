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
    
    // This one should go to config
    const BIGREF_LOWER_LIMIT = 20;
    const SMALLREF_UPPER_LIMIT = 4;
    
    const IS_SMALL_TABLE = 1;
    const IS_MEDIUM_TABLE = 2;
    const IS_BIG_TABLE = 3;
    
    const PIX_PER_CHAR_FIELD = 12;
    const PIX_PER_CHAR_COLUMN = 12;
    
    // Model Types
    const TYPE_STRING = "string";
    const TYPE_INT = "int";
    const TYPE_FLOAT = "float";
    const TYPE_DATE = "date";
    
    // Field Xtypes
    const FIELD_TEXT = 'textfield';
    const FIELD_TEXTAREA = 'textareafield';
    const FIELD_NUMBER = 'numberfield';
    const FIELD_DATE = 'datefield';
    const FIELD_COMBO = 'combobox';
    const FIELD_RADIO = 'radiogroup';
    
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
        \PropelTypes::TIMESTAMP => InfoGen::TYPE_DATE,
        \PropelTypes::BU_DATE => InfoGen::TYPE_DATE,
        \PropelTypes::BU_TIMESTAMP => InfoGen::TYPE_DATE,

        \PropelTypes::BOOLEAN => Infogen::TYPE_INT,
        \PropelTypes::ENUM => Infogen::TYPE_INT
            
    );    
    // Helper vars
    protected $tables = array();
    
    /**
     * Get table name (no schema name)
     * 
     * @param \TableMap $tmap
     * @return string
     */
    public function getName(\TableMap $tmap) {
        return strtolower(underscoreCapitalize($tmap->getPhpName()));
    }
    /**
     * Add tables to $this->tables array
     * 
     * @param TableMap $tmap
     */
    public function addTable(\TableMap $tmap) {
        
        // Load config
        $config = $this->getConfig();
        
        // Identify
        $t["name"] = $this->getName($tmap);
        $t["php_name"] = $tmap->getPhpName();
        $t["app_name"] = $config['project_php_name'];
        
        // Determine primary keys, and add virtual column to acommodate composite keys
        if (sizeof($tmap->getPrimaryKeyColumns() > 1)) {
            
            $t["composite_pk"] = 1;
            $t["pk_name"] = $this->createCompositeKeyDelegationColumn($tmap);
            
        } else {
            $t["composite_pk"] = 0;
            
            //Need to set pk_name here because createCompositeKeyDelegationId() does it too
            $pkColumns = $tmap->getPrimaryKeyColumns();
            $pkColumn = $pkColumns[0];
            $t["pk_name"] = $pkColumn->getNama();
            
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
            $t["is_big_ref"] = 0;               // create search combo

        } else {
            $t["size_type"] = InfoGen::IS_MEDIUM_TABLE;
            $t["is_static_ref"] = 1;            // create radio & static combo
            $t["is_small_ref"] = 0;             // prioritize radio
            $t["is_medium_ref"] = 1;            // create search combo
            $t["is_big_ref"] = 0;               // create search combo

        }

        // Determine display field. First try with Xond 1 standard, "nama"
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
        
        // Determine data or ref
        $pkColumns = $tmap->getPrimaryKeyColumns();
        $pkColumn = $pkColumns[0];
        
        if ($t['composite_pk']) {
            $isData = 1;            
        } else if ( (InfoGen::$extTypeMap[$pkColumn->getType()]  == InfoGen::TYPE_INT) && ($count < InfoGen::BIGREF_LOWER_LIMIT)) {
            $isData = 0;
        }
        
        if ($isData) {
            $t["is_data"] = 1;
            $t["is_ref"] = 0;
            $t["create_grid"] = 1;
            $t["create_form"] = 1;
        } else {
            $t["is_data"] = 0;
            $t["is_ref"] = 1;
            $t["create_grid"] = 0;
            $t["create_form"] = 0;
        }
        
        // Do we need this ?
        $t["has_many"] = implode(',', $rHasMany);
        $t["belongs_to"] = implode(',', array_merge($rBelongsTo, $rOneToOne));
        $t["is_split_entity"] = (sizeof($rOneToOne) > 0) ? 1 : 0;
        $t["has_split_entity"] = 0;
        $t["split_entity_name"] = '';
        $t["relating_columns"] = implode(',', $rHasMany);
        
        // Register
        $this->registerTable($t);
        
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
     *
     * @param TableMap $table
     */
    public function addColumns(\TableMap $tmap) {
    
        $config = $this->getConfig();
    
        foreach ($tmap->getColumns() as $c) {
            
            // For autocomplete purpose. NEED TO BE COMMENTED ON RUN
            //$c = new \ColumnMap($name, $containingTable);
            
            $colName = $c->getName();
            $colPhpName = $c->getPhpName();
    
            if (contains($colName, $config['front_end_skip_columns'])) {
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
            $cArr["type"] = InfoGen::$extTypeMap[$c->getType()];
            
            $cArr["is_pk_uuid"] = $isPkUuid;
            $cArr["is_pk"] = $isPk;
            $cArr["is_fk"] = $isFk;
            $cArr["fk_table_name"] = ($isFk) ? $this->getName($c->getRelatedTable()) : "";
            
            $cArr['min'] = 0;
            $cArr['max'] = ($c->isNumeric()) ? pow(10, $this->getColumnLength($c))-1 : 0;
            
            $cArr['label'] = sentencesize($this->getColumnLabel($c));
            $cArr["header"] = humanize($this->getColumnLabel($c));
            
            $cArr['column_width'] = $this->getColumnLength($c) * InfoGen::PIX_PER_CHAR_FIELD;
            $cArr['hide_column'] = $isPk ? 1 : 0;
            $cArr['field_width'] = $this->getColumnLength($c) * InfoGen::PIX_PER_CHAR_COLUMN;
            $cArr['display_field'] = $this->getColumnDisplayField($c);
            
            
            $cArr['xtype'] = $this->getColumnXtype($c);
            $cArr['allow_empty'] = $c->isNotNull() ? 1 : 0;
            $cArr['validation'] = '';
            $cArr['description'] = '';
            
            $this->registerColumn($tmap, $cArr);
            
        }
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
        if ($column->isForeignKey()) {
            return $this->tables[$this->getName($column->getRelatedTable())]["display_field"];
        } else {
            return "";
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
                    $xtype = InfoGen::FIELD_RADIO;
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
                    $xtype = strtolower($column->getRelatedTable()->getPhpName())."combo";
                    $static = 1;
                    $number = 10;
                    break;
                    
                case InfoGen::IS_BIG_TABLE:
                    $xtype = strtolower($column->getRelatedTable()->getPhpName())."combo";
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
        else {
            $xtype = 'undefined';
        }
        
        return $xtype;
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
     * @return string
     */
    public function createCompositeKeyDelegationColumn(\TableMap $tmap) {
        
        // Create virtual PK column
        $virtualPkName = $this->getName($tmap)."_id";
        
        // Fill 
        $c['column_php_name'] = phpnamize($virtualPkName);
        $c['column_name'] = $virtualPkName;
        $c["type"] = 'string';
        $c["is_pk_uuid"] = 0;
        $c["is_pk"] = 1;
        $c['min'] = 0;
        $c['max'] = 0;
        $c['label'] = ' ';
        $c["header"] = ' ';
        $c['input_length'] = 100;
        $c['hide_column'] = 1;
        $c['field_width'] = 100;
        $c['display_field'] = '';
        $c['xtype'] = 'hidden';
        $c['combo_xtype'] = strtolower(phpnamize($virtualPkName));
        $c['validation'] = '';
        $c['allow_empty'] = 0;
        $c['description'] = '';
        
        // Register Column
        $this->registerColumn($tmap, $c);
        
        // Set PK Name to the this virtual PK column
        // $this->setPkName($tmap, $virtualPkName);
        
        return $virtualPkName;
        
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
        $twig = new \Twig_Environment($loader);
        
        // Add custom filter "sizeof"
        $filter = new \Twig_SimpleFilter('sizeof', function($array){
            return sizeof($array);
        });
        $twig->addFilter($filter);
        
        // Base Files
        $templateFileName = 'info-template-base.php';
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
        $templateFileName = 'info-template.php';
        $fileName = "{$tmap->getPhpName()}TableInfo.php";
        $targetPath = $infoPath.DIRECTORY_SEPARATOR.$fileName;
        
        // Apply template
        try {
            $tplStr = $twig->render($templateFileName, $this->tables[$this->getName($tmap)]);
            $this->writeToFile($tmap, $tplStr, $targetPath);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
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
            $outStr .= "{$t['name']} $written<br>\r\n";
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
        
        // So that Silex's Request and Application accessible in any methods
        $this->setRequest($request);
        $this->setApp($app);
        
        // Get the config
        $config = $app['xond.config'];
        $this->setConfig($config);
            
        // Mark the start of gen process. Now using monolog
        $app['monolog']->addInfo("Gen start at " . date ( 'Y-m-d H:i' ));
        
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
        
        // Complete the tmap with necessary infos
        foreach ($maps as $tmap) {
            $this->toFile($tmap);
        }
        
        return $this->toStr();
        
    }
    
}