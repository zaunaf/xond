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
use Xond\Xond;
use Xond\Rest;
use Xond\Info\TableInfo;
use Xond\Info\ColumnInfo;
use SimptkEis\Model\PenggunaPeer;

class Get extends Rest
{
    private $start;
    private $limit;
    
    /**
     * Set offset of the data selected.
     * Mainly for paging purposes.
     *
     * @param int $start
     */
    public function setStart($start){
        $this->start = $start;
    }
    
    /**
     * Get offset of the data selected.
     *
     * @return int
     */
    public function getStart(){
        return $this->start;
    }
    
    /**
     * Set number of rows to be returned in the current page.
     * Mainly for paging purposes.
     *
     * @param int $limit
     */
    public function setLimit($limit){
        $this->limit = $limit;
    }
    
    /**
     * Get number of rows to be returned in the current page.
     *
     * @return int
     */
    public function getLimit(){
        return $this->limit;
    }

    /**
     * The Main process method
     *
     */
    public function process()
    {
        // Reposess Vars
        $request = $this->getRequest();
        $app = $this->getApp();
        $config = $this->getConfig();
        
        // This one tries to resolve issue #77
        // Separate concerns between request generated via http protocol
        // And the one generated from other part of the application
        
        // Still blank... Don't know what to do :(
        
        // Create criteria for matching
        $this->c = new \Criteria();
        
        // Get the peer object for query management
        $p = $this->getPeerObj();
        
        // Kick the begin event
        $app['dispatcher']->dispatch('rest_get.begin');
        
        // Handle limiters
        $start = ($request->get('start')) ? $request->get('start') : 0;
        $this->setStart($start);
        
        // Kick the offset calculation event in case someone wants to mess with the start value
        $app['dispatcher']->dispatch('rest_get.calc_offset');
        
        
        // $limit =($request->get('limit')) ? $request->get('limit') : 20;
        if ($request->get('limit') == 'unlimited') {
            $limit = 0;
        } else {
            if (! $request->get('limit')) {
                $limit = 20;
            } else {
                $limit = $request->get('limit');
            }
        }
        $this->setLimit($limit);
        
        // Kick the limit calculation event in case someone wants to mess with the limit value
        $app['dispatcher']->dispatch('rest_get.calc_limit');
        
        // Get TableInfo
        $tInfo = $this->getTableInfoObj();
        
        
        //// QUERY HANDLING ////
        
        // Handle query
        $filterProperty = "";
        
        // Get the query sent
        $query = $request->get('query');
        
        // Fuzzy searching
        $query = str_replace(" ", "%", $query);
        
        if ($query) {
            $query = "%" . $query . "%";
            $json = json_decode($request->get('filter'));
            // $filterProperty = $json[0]->property;
            
            $filterProperty = $tInfo->getDisplayField();
            // $columnName = $this->getPeerObj()->getTableMap()->getName().".".$filterProperty;
            //$columnName = $this->getPeerObj()
            //    ->getTableMap()
            //    ->getName() . "." . $tInfo->getDisplayField();
            // echo "Columname: ".$columName."\n";
            
            $columnName = Rest::convertToColumnName($tInfo, $filterProperty);
            
            $this->c->add($columnName, $query, \Criteria::LIKE);
        }

        // NEW: Enable drilldown grid //
        // Still not support combining with other handler //
        if ($request->get('restconfig')) {
            
            $this->c = $this->handleHierarchialData($this->c);
        
        } else {
        
            // If a custom descendants of the class want to add some filter injection
            $this->c = $this->injectFilter($this->c);
            
            // For loading big reference renderer (CHECK AGAIN) 
            $this->c = $this->handleId($this->c);

            // Enable left and right filtering (CHECK AGAIN)
            // $this->c = $this->handleBigLeftJoinFk($this->c);
            // $this->c = $this->handleBigRightJoinFk($this->c);
            
            // This is the MAIN parameter handling for the class
            $this->c = $this->handleParams($this->c);
            
            // Debug
            if ($this->getModelName() == "Sekolah") {
                //print_r($this->c); die;
            }
        }

        // Add custom critera setup
        $app['dispatcher']->dispatch('rest_get.custom_criteria');
        
        // Get total number of row exists
        // print_r($c); die;
        // echo $this->c->toString(); die();
        // $rowCount = $p->doCount($c);
        
        // Do row count
        if ($request->get('restconfig')) {
        
            // Special: RESTCONFIG, counting by
            // Calculating result of statement query
            $stmt = $p->doSelectStmt($this->c);
            $outArr = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $this->setRowCount(sizeof($outArr));
            
        } else {
        
            // Standard:
            $rowCount = $p->doCount($this->c);
            $this->setRowCount($rowCount);
        }
        
        
        //// Kick the count event ////
        
        // Coders can then stop here, and process the full (un-paged data) 
        // for other purposes such as exporting and filtering
        // For "hierachial data support" on printing, please don't forget
        // to NOT USING THE STANDARD PEER doSelect, because the number 
        // of parameters to be hydrated will be different. Instead use the
        // $stmt. For easy solution just read code below the comment "Process the Criteria" 
        $app['dispatcher']->dispatch('rest_get.count');
        
        // Set limit. Limit will be disabled when $limit = 0. Be careful though ;)
        $this->c->setOffset($this->getStart());
        if ($this->getLimit() > 0) {
            $this->c->setLimit($this->getLimit());
        }
        
        // Set order by primary key
        // $p->doSelect($this->c); // What is THIS ?
        $connection = \Propel::getConnection(\Propel::getDefaultDB());
        $connection->useDebug(false);
        
        // Debug
        // print_r("Last Query: ". $this->c->toString()); die();
        
        // Process the Criteria
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
        $this->setResponseCode(200);
        
        // Kick the data_load event in case someone wants to mess with the value
        $app['dispatcher']->dispatch('rest_get.data_load');
        
        // Process the response string from the attached values
        $this->createResponseStr();
        
        // Kick the response_str_load event in case someone wants to mess with the string. May be override it?
        $app['dispatcher']->dispatch('rest_get.response_str_load');
        
        
        // print_r($outArr);
        // return tableJson($outArr, $rowCount, $id);
        // return tableJson(getArray($tArr, \BasePeer::TYPE_FIELDNAME), $rowCount, $id);
        // return "Processing GET for model ".$request->get('model')." number ".$request->get('which');
        // return "Processing GET for model ".$this->getModelName()." number ".$this->getWhich();
    }
    
    /**
     * Process Rows: skip int keys, add support for Composite PK 
     * @param unknown $tArr
     * @return multitype:string
     */
    public function processRows($tArr) {
        
        // $id = $p->getFieldNames(\BasePeer::TYPE_FIELDNAME);
        $fieldNames = $this->createFieldNames($this->getModelName());
        $this->setFieldNames($fieldNames);
        $tInfo = $this->getTableInfoObj();
        
        // Inital vals
        $outArr = array();
        
        // Process for all row
        foreach ($tArr as $t) {
        
            // Search for FK columns
            /*
            * $cols = $tInfo->getColumns(); foreach ($cols as $col) { $col = new ColumnInfo(); if ($col->getIsFk()) { $colPhpName = $col->getColumnPhpName(); } } $tInfo = new PtkTableInfo(); $tInfo->get $table = new Ptk(); $table->getSekolah()->getNama();
            */
            $arr = $t->toArray(\BasePeer::TYPE_FIELDNAME);
        
            // Cleans odd decimal / float values for FK //
            // print_r($arr); die;
            $counter = 0;
            foreach ($arr as $key => $val) {
                // echo "$key = $val ".($this->floatBukan($val) ? "Float" : "Not float")."|"; continue;
                if (($counter == 0) && isFloat($val)) {
                    // echo $val."<br>";
                    $arr["$key"] = intval($val);
                    // break;
                }
                $counter ++;
            }
            
            // Check to the params, is there any instruction to skip adding FK strings?
            $params = $this->getParams();

            if(!isset($params["skipfkstr"])) {
                $arr = $this->addFkStrings($arr, $t);
            }

            // Handling for composite PKs //
            // Basically, it creates Virtual PK Column
            if ($tInfo->getIsCompositePk() && (!isset($params["skipvirtualpk"]))) {

                $cols = $tInfo->getColumns();
    
                $pkName = $tInfo->getPkName();
                $virtualPkStr = "";
    
                $i = 1;
    
                foreach ($cols as $col) {
                // echo $col->getName()."\n";
                    // Skip first column(virtual column)
                    if ($i == 1) {
                        $i ++;
                        continue;
                    }
    
                    if ($col->getIsPk()) {
        
                        $colName = $col->getName();
                        $virtualPkStr .= ($i > 2) ? ":" : "";
                        $virtualPkStr .= $arr["$colName"];
                        // echo "Found PK : $colName<br>\n";
                        // echo "Value: ". $arr["$colName"]."<br>\n";
                        // echo "Current val: ".$virtualPkStr."<br>\n";
                    }
                    $i ++;
                }
        
                $arr["$pkName"] = $virtualPkStr;
        
                // print_r($id);
        
                // Removing the pk from column list
                // $key = array_search($pkName, $id);
                // unset($id[$key]);
        
                // Add pkname to the top using merge
                // print_r($pkName); die;
                
                // Tadinya begini:
                // array_unshift($id, $pkName);
                
                // Jadi begini: (mudah2an bener)
                array_unshift($fieldNames, $pkName);
                $this->setFieldNames($fieldNames);

                // Add pkname to the top using merge (commented, i guess it would no longer needed)
                // array_unshift($id, $pkName);
                // print_r($id);
            }
            
            $outArr[] = $arr;
        }
        
        return $outArr;
        
    }
    
    private function handleId(\Criteria $c)
    {
        //// CHECK THIS ////
        
        // Handle id(request to fill remote loaded big reference combo)
        $id = $this->getRequest()->get('id');
        
        if ($id) {
            
            $t = $this->getTableInfoObj();
            $pkName = $t->getPkName();
            
            //$columnName = $this->getPeerObj()
            //    ->getTableMap()
            //    ->getName() . "." . $pkName;
            
            $tInfo = $this->getTableInfoObj();
            $columnName = Rest::convertToColumnName($tInfo, $pkName);
            $c->add($columnName, $id, \Criteria::EQUAL);
            
        }
        
        return $c;
    }

    private function handleParams(\Criteria $c)
    {
        
        // Other/Custom load params()
        $app = $this->getApp();
        $params = $this->getRequest()->query->all();
        $this->setParams($params);
        
        $tInfo = $this->getTableInfoObj();
        
        // Kick the params calculation event in case someone wants to mess with the params
        $app['dispatcher']->dispatch('rest_get.calc_params');
        
        foreach ($this->getParams() as $key => $val) {
            
            // echo "\r\n".$key."|".$val;
            
            
            // Unset or skip empty params
            if ($val == "") {
                continue;
            } 
            
            // Skip if the key is reserved keywords
            if (!in_array($key, array(
                "limit",
                "start",
                "query",
                "filter",
                "page",
                "_dc",
                "sort",
                "filter",
                "id",
                "skipfkstr",
                "skipvirtualpk",
                "restconfig"
            ))) {
                
                // If the key detected is one of the sorting keywords
                if ($key == "ascending") {
                    $c->addAscendingOrderByColumn($this->getPeerObj()
                        ->getTableMap()
                        ->getName() . "." . $val);
                    continue;
                }
                if ($key == "descending") {
                    $c->addDescendingOrderByColumn($this->getPeerObj()
                        ->getTableMap()
                        ->getName() . "." . $val);
                    continue;
                }

                 // If the detected #, process greater/less than/equal                
                if (strpos($key, "#")) {

                    $command = substr($key, strpos($key, "#"), (strlen($key) - strpos($key, "#")));
                    $key = substr($key, 0, strpos($key, "#"));

                    $cInfo = $tInfo->getColumnByName($key);
                    
                    $columnName = Rest::convertToColumnName($tInfo, $cInfo->getName());
                    $typeColumn = $cInfo->getType();

                    //echo $command."<br>".$key;
                    //die;
                    
                    if ($command == "#isgreaterthan") {
                        $c->add($columnName, $val, \Criteria::GREATER_THAN);
                    }

                    if ($command == "#isgreaterequal") {
                        $c->add($columnName, $val, \Criteria::GREATER_EQUAL);
                    }

                    if ($command == "#islessthen") {
                        $c->add($columnName, $val, \Criteria::LESS_THAN);
                    }

                    if ($command == "#islessequal") {
                        $c->add($columnName, $val, \Criteria::LESS_EQUAL);
                    }
                    
                    continue;
                }
                        
                // If the key MATCH any of the column name of the module
                // Then process them one by one 
                // Translating them to each corresponding behaviour
                
                if ($tInfo->getColumnByName($key)) {
                    
                    $cInfo = $tInfo->getColumnByName($key);
                    
                    $columnName = Rest::convertToColumnName($tInfo, $cInfo->getName());
                    $typeColumn = $cInfo->getType();
                    
                    // Detect if it's an id
                    $id = substr($columnName, - 3, 3);
                    
                    // Custom parameter value, non variable
                    $custom = substr($val, 0, 1);
                    
                    // There are 6 possibilities of param filter
                    // 1) JSON encoded array
                    // 2) Any params (PK, FK or anything) with wildcard (*) match
                    // 3) Exact match for columns ending with "_id"
                    // 4) Switcher only, no need of val ISNULL, ISNOTNULL and ISEMPTY
                    // 5) Regular string. Fuzzy searching then applied
                    // 6) Any else type. Just match it.
                    

                    // Detect JSON, it's an array !
                    if ($custom == "[") {
                        // echo "| array";
                        $c->add($columnName, json_decode($val), \Criteria::IN);
                    
                    // Wildcard match
                    } else if (!(strpos($val, "*") === false)) {
                        // echo "| wildcard";
                        $val = str_replace("*", "%", $val);
                        if (get_adapter() == 'pgsql') {
                            $c->add($columnName, $val, \Criteria::ILIKE);
                        } else {
                            $c->add($columnName, $val, \Criteria::LIKE);
                        }

                        
                    // It's an id. If the ID is a stupid string, you might want to use no.1
                    } else if ($id == "_id") {
                        // echo "| _id";
                        $c->add($columnName, $val, \Criteria::EQUAL);

                    // Switcher, if non standard value is given as params
                    } else if ($custom == "#") {
                        // echo "| custrom";
                        switch ($val) {
                        	case "#ISNULL":
                        	    // echo "#ISNULL";
                        	    $c->add($columnName, NULL, \Criteria::ISNULL);
                        	    break;
                        	case "#ISNOTNULL":
                        	    // echo "#ISNOTNULL";
                        	    $c->add($columnName, NULL, \Criteria::ISNOTNULL);
                        	    break;
                        	case "#ISEMPTY":
                        	    // echo "#ISEMPTY";
                        	    $c->add($columnName, "", \Criteria::EQUAL);
                        	    break;
                                default:
                        	    break;
                        }
                    
                    // If it's a string or text. Fuzzy search.
                    } else if (in_array($typeColumn, array(
                        "string",
                        "text"
                    ))) {
                        // echo "| fuzzy";
                        $val = str_replace(" ", "%", $val);
                        $val = "%" . $val . "%";
                        
                        if (get_adapter() == 'pgsql') {
                            $c->add($columnName, $val, \Criteria::ILIKE);
                        } else {
                            $c->add($columnName, $val, \Criteria::LIKE);
                        }

                    // Everything else
                    } else {
                        // echo "| else";
                        $c->add($columnName, $val, \Criteria::EQUAL);
                    }
                    
                
                } else {
                    
                    // It's a join thing
                    // Lets look for each available joins
                    // then do the filter if the key match
                    // one of the join's columns
                    
                    // Left
                    // print_r($tInfo->getBelongsTo());
                    $leftJoinTables = $tInfo->getBelongsTo();
                    
                    if (is_array($leftJoinTables)) {

                        foreach ($leftJoinTables as $tableName) {                            
                            
                            $tableCamelName = phpNamize($tableName);
                            $tInfoJoin = Rest::createTableInfo($tableCamelName);
                            
                            if ($tInfoJoin->getColumnByName($key)){

                                // Prepare this side
                                $columnInfo = $tInfo->getColumnInfoRelatedTo($tInfoJoin->getName());            // Get column info of the FK
                                $columnName = Rest::convertToColumnName($tInfo, $columnInfo->getName());        // Get official column name of the FK
                                
                                // Prepare join side
                                $joinColumnInfo = $tInfoJoin->getColumnByName($tInfoJoin->getPkName());              // Get ColumnInfo of PK of the FK Table
                                $joinColumnName = Rest::convertToColumnName($tInfoJoin, $joinColumnInfo->getName()); // Get official column name of the PK
                                
                                $criteriaColumnInfo = $tInfoJoin->getColumnByName($key);
                                $criteriaColumnName = Rest::convertToColumnName($tInfoJoin, $criteriaColumnInfo->getName());
                                
                                // Debug
                                // echo $columnName."\r\n";
                                // echo $joinColumnName."\r\n";
                                // echo $criteriaColumnName."\r\n";
                                
                                // Join First
                                $c->addJoin($columnName, $joinColumnName, \Criteria::LEFT_JOIN);
                                
                                // Then filter
                                $c->add($criteriaColumnName, $val, \Criteria::EQUAL);                            
                            }
                        }
                        
                    }

                    // Right
                    // Don't forget to correct them if HasMany bugs have been corrected
                    // print_r($tInfo->getHasMany());
                    
                    $rightJoinTables = $tInfo->getHasMany();
                    // print_r($tInfo->getName()); print_r($rightJoinTableArr);                    
                    // $rightJoinTableComma = $rightJoinTableArr[0];
                    // $rightJoinTables = explode(",", $rightJoinTableComma);

                    if (is_array($rightJoinTables)) {
                    
                        foreach ($rightJoinTables as $tableName) {
                            
                            $tableCamelName = phpNamize($tableName);
                            $tInfoJoin = Rest::createTableInfo($tableCamelName);
                            
                            if ($tInfoJoin->getColumnByName($key)){
                                
                                // Debug
                                // echo "$key found in $tableCamelName\r\n";  // Filtering by $joinColumnName<br>";
                                
                                // Prepare this side 
                                $columnName = Rest::convertToColumnName($tInfo, $tInfo->getPkName());
                                
                                // Prepare join side
                                $joinColumn = $tInfoJoin->getColumnInfoRelatedTo($tInfo->getName());
                                $joinColumnName = Rest::convertToColumnName($tInfoJoin, $joinColumn->getName());                            
                                $criteriaColumnName = Rest::convertToColumnName($tInfoJoin, $key);
                                
                                // Debug
                                // echo $columnName."\r\n";
                                // echo $joinColumnName."\r\n";
                                
                                // Join First
                                $c->addJoin($columnName, $joinColumnName, \Criteria::RIGHT_JOIN);
                                
                                // Then filter
                                $c->add($criteriaColumnName, $val, \Criteria::EQUAL);
                                
                            }
                        }
                    
                    }
                    
                    /*
                    $joins = $c->getJoins();
                    
                    foreach ($joins as $j) {
                        
                        $tableName = $j->getRightTableName();
                        $dotPos = strpos($tableName, ".");
                        if ($dotPos != false) {
                            $tableName = substr($tableName, $dotPos+1, strlen($tableName)-$dotPos);
                        }
                        $tableCamelName = phpNamize($tableName);
                        //echo "$tableCamelName<br>";
                        
                        $tInfoJoin = Rest::createTableInfo($tableCamelName);
                        if ($tInfoJoin->getColumnByName($key)){
                            
                            $cInfoJoin = $tInfoJoin->getColumnByName($key);
                            $joinColumnName = Rest::convertToColumnName($tInfoJoin, $cInfoJoin->getName());
                            //echo "$key found in $tableCamelName. Filtering by $joinColumnName<br>";
                            $c->add($joinColumnName, $val, \Criteria::EQUAL);
                            
                        } else {
                            //echo "$key NOT found in $tableCamelName<br>";
                        }
                    }
                    //
                     * 
                     */
                }
            }
        }
        
        return $c;
    }

    /**
     * Detect if any LEFT JOIN FK (reference) exists
     * and add them to the join criteria.
     * Still a bit ugly since it joins to tables even it's not needed.
     * Later on, detect first then only add join if needed.
     * @param \Criteria $c
     * @return \Criteria
     */
    public function handleBigLeftJoinFk(\Criteria $c)
    {
        $tInfo = $this->getTableInfoObj();
        
        // Handle big FKs(or enabling left join filtering via foreign key. sadly only one step supported ^^)
        if ($tInfo->getIsData()) {
            // $tInfo = new PtkTableInfo();
            $cols = $tInfo->getColumns();
            $joinColumns = array();
            
            foreach ($cols as $col) {
                // $col = new ColumnInfo();
                if ($col->getIsFk()) {
                    $fkTableInfo = $col->getFkTableInfo();
                    // print_r($fkTableInfo);
                    // $fkTableInfo = new SekolahTableInfo();
                    
                    if ($fkTableInfo->getIsBigRef()) {
                        $joinColumn = $fkTableInfo->getName() . "." . $fkTableInfo->getPkName();
                        $joinColumnName = Rest::convertToColumnName($fkTableInfo, $fkTableInfo->getPkName()); 
                        
                        if (! in_array($joinColumn, $joinColumns)) {
                            // $tInfo = new TableInfo();
                            $columnName = Rest::convertToColumnName($tInfo, $col->getName());
                            $c->addJoin($columnName, $joinColumnName, \Criteria::LEFT_JOIN);
                            
                            // WTF IS THIS
                            // echo $tInfo->getName().".".$col->getName()."<br>";
                            // if ($this->getModelName() == "PesertaDidikLongitudinal") {
                            // $c->addJoin($tInfo->getName() . "." . $fkTableInfo->getPkName(), $joinColumn, \Criteria::LEFT_JOIN);
                            // } else {
                            // $c->addJoin($tInfo->getName() . "." . $col->getName(), $joinColumn, \Criteria::LEFT_JOIN);
                            // }
                        }
                        $joinColumns[] = $joinColumn;
                        // echo $tInfo->getName().".".$col->getName()." | ". $fkTableInfo->getName().".".$fkTableInfo->getPkName()."<br>";
                    }
                }
            }            
        }
        
        return $c;
    }
 
    public function handleBigRightJoinFk($c)
    {
        
        // Handle big FKs(enabling right join not null filtering via relating tables)
        // 1) Passing parameter will be in format as follows
        // RelatingTable.column_name: value
        // 2) Search matching class
        // 3) Create instances of TableInfo and Peer classes
        // 4) Add join
        // 5) Add value check by Relating Table's Peer
        
        // print_r($params); die;
        $tInfo = $this->getTableInfoObj();
        $params = $this->getParams();
        
        if ($tInfo->getIsData()) {
            
            // $tInfo = new PtkTableInfo();
            $relatingColumns = $tInfo->getRelatingColumns();            
            
            if (sizeof($relatingColumns) > 1) {
                
                foreach ($relatingColumns as $rc) {
                    
                    @list ($className, $fieldName) = explode(".", $rc);
                    
                    // print_r($params);
                    foreach ($params as $key => $val) {
                        
                        @list ($paramsClassName, $fieldName) = explode("-", $key);
                        
                        // echo $fieldName."<br>";
                        if (! $fieldName) {
                            continue;
                        }
                        
                        // echo $className ."==". $paramsClassName."<br>";
                        // Match the searched classname
                        if ($className == $paramsClassName) {
                            // echo "masuk sini";
                            
                            // $paramsTableInfoClassName = "\\".$this->appName."\\Info\\" . $paramsClassName . 'TableInfo';
                            // $paramsTableClassName = "\\.$this->appName.\\Model\\" . $paramsClassName;
                            // $paramsTablePeerName = $paramsTableClassName . "Peer";
                            
                            $paramsTableInfoClassName = $this->createClassName("Info", $paramsClassName, 'TableInfo');
                            $paramsTableClassName = $this->createClassName("Model", $paramsClassName, "");
                            $paramsTablePeerName = $this->createClassName("Model", $paramsClassName, "Peer");
                            
                            $paramsTableInfo = new ${'paramsTableInfoClassName'}();
                            $paramsPeerName = new ${'paramsTablePeerName'}();
                            
                            $localColumn = $tInfo->getName() . "." . $tInfo->getPkName();
                            $rightColumn = $paramsTableInfo->getName() . "." . $tInfo->getPkName(); // $paramsTableInfo->getPkName();
                            $paramsColumn = $paramsTableInfo->getName() . "." . $fieldName;
                            $softDel = $paramsTableInfo->getName() . ".soft_delete";
                            // echo $localColumn."-".$rightColumn."<br>"; die();
                            
                            $c->addJoin($localColumn, $rightColumn, \Criteria::LEFT_JOIN);
                            
                            // Match the key(field name)
                            // echo $key;
                            $paramsColumnInfo = $paramsTableInfo->getColumnByName($fieldName);
                            // print_r($paramsColumnInfo);
                            if (! is_object($paramsColumnInfo)) {
                                continue;
                            }
                            $typeColumn = $paramsColumnInfo->getType();
                            
                            // echo $paramsColumn;
                            $other = substr($val, 0, 1);
                            // $c->add($softDel, 0);
                            
                            if (in_array($typeColumn, array(
                                "string",
                                "text"
                            )) && $other != "#") {
                                // echo "array(string, text)";
                                $val = str_replace(" ", "%", $val);
                                $val = "%" . $val . "%";
                                $c->add($paramsColumn, $val, \Criteria::ILIKE);
                            } else 
                                if ($other == "#") {
                                    switch ($val) {
                                        case "#ISNULL":
                                            // echo "#ISNULL";
                                            $c->add($paramsColumn, NULL, \Criteria::ISNULL);
                                            break;
                                        case "#ISNOTNULL":
                                            // echo "#ISNOTNULL";
                                            $c->add($paramsColumn, NULL, \Criteria::ISNOTNULL);
                                            break;
                                        case "#ISEMPTY":
                                            // echo "#ISEMPTY";
                                            $c->add($paramsColumn, "", \Criteria::EQUAL);
                                            break;
                                        default:
                                            // echo "#DEFAULT";
                                            break;
                                    }
                                } else {
                                    // echo "ELSE";
                                    $c->add($paramsColumn, $val, \Criteria::EQUAL);
                                }
                        }
                    }
                }
            }
        }
        
        return $c;
    }    
    
    /**
     * Add _str to $arr
     *
     * @param array $arr            
     * @return array
     */
    public function addFkStrings(array $arr, $t)
    {
                        
        $tInfo = $this->getTableInfoObj();
        
        
        if ($tInfo->getIsData()) {
            
            $cols = $tInfo->getColumns();
            
            foreach ($cols as $col) {
                // $col = new ColumnInfo();
                if ($col->getIsFk()) {
                    
                    $fkTableInfo = $col->getFkTableInfo();
                    // $fkTableInfo = new SekolahTableInfo();
                    $fkTableObj = "";
                    
                    if ($fkTableInfo->getIsBigRef()) {
                        
                        // Getting the FK column name
                        $fkColumnName = $col->getName();
                        
                        // Getting the FK Table name
                        $fkTableName = phpnamize($fkTableInfo->getName());
                        
                        // Getting the display field to be called as _str
                        $fkDisplayField = phpnamize($fkTableInfo->getDisplayField());
                        
                        // Creating column name string
                        $fkColumnNameStr = $fkColumnName . "_str";
                        
                        // Creating getter for FK Table Name.
                        $fkTableNameStr = "get" . $fkTableName;
                        
                        // Creating getter for the FK Display field
                        $fkDisplayFieldStr = "get" . $fkDisplayField;
                        
                        $fkTableObjRelatedBy = $fkTableNameStr . "RelatedBy" . $col->getPhpName();
                        
                        // Check whether tablepeer have the caller for the fkTableName, call the object if it can
                        if (method_exists($t, $fkTableNameStr)) {
                            
                            $fkTableObj = $t->$fkTableNameStr();
                        
                        // Check via related by
                        } else if (method_exists($t, $fkTableObjRelatedBy)) {
                        
                            //echo $fkTableObjRelatedBy;
                            $fkTabelObj = $t->$fkTableObjRelatedBy();
                            // print_r($fkTabelObj);
                            // die;
                            
                        // Ugly and database-killing FIX THIS USING CRITERIA::IN or somekind
                        } else {
                            
                            $fkColumnGetter = "get".phpNamize($fkColumnName);
                            
                            // This assumes that the FK related to the primary key. 
                            // IT WON'T WORK IF THE RELATION LINKED TO OTHER COLUMN.
                            // FIX THIS !! 
                            $peer = $this->createPeer(phpNamize($fkTableInfo->getName()));
                            $fkTableObj = $peer->retrieveByPk($t->$fkColumnGetter());
                        }
                        if (method_exists($fkTableObj, $fkDisplayFieldStr)) {
                            $arr["$fkColumnNameStr"] = $fkTableObj->$fkDisplayFieldStr();
                        }
                    }
                }
            }
        }
        
        return (array) $arr;
    }
    
    // Using the standard responsestr, so the following code is not necessary    
    // public function createResponseStr(){
    //     $this->setResponseStr(tableJson($this->getResponseData(), $this->getRowCount(), $this->getFieldNames()));
    // }
    

    public function handleHierarchialData($c) {
        
        /* SCENARIO 
           1) Define restconfig below as part of grid config:
                restconfig: {
                    title: 'Guru Kepegawaian',
                    subtitle: '',
                    filename: 'gurukepegawaian',
                    aggregate: {
                        aggregate_column: 'nama_sekolah',
                        initial_value: '000000 ',
                        hierarchy: [{
                            name: 'Nasional',
                            table_name: 'mst_wilayah',
                            table_id: 'kode_wilayah',
                            display_column: 'nama',
                            level: 0
                        },{
                            name: 'Propinsi',
                            table_name: 'mst_wilayah',
                            table_id: 'kode_wilayah',
                            parent_column: 'mst_kode_wilayah',
                            display_column: 'nama',
                            level: 1
                        },{
                            name: 'Kab/Kota',
                            table_name: 'mst_wilayah',
                            table_id: 'kode_wilayah',
                            parent_column: 'mst_kode_wilayah',
                            display_column: 'nama',
                            level: 2
                        },{
                            name: 'Kecamatan',
                            table_name: 'mst_wilayah',
                            table_id: 'kode_wilayah',
                            parent_column: 'mst_kode_wilayah',
                            display_column: 'nama',
                            link_local: 'kode_wilayah',
                            level: 3
                        }]
                    },
                    columns: [{
                        name: 'nama_sekolah',
                        width: 350,
                    },{
                        name: 'kode_wilayah',
                        align: 'right',
                        width: 120,
                    },{
                        name: 'jumlah_pns',
                        header: 'Jumlah PNS',
                        align: 'right',
                        width: 130,
                        summary: 'sum'
                    },{
                        name: 'jumlah_non_pns',
                        header: 'Jumlah Non-PNS',
                        align: 'right',
                        width: 130,
                        summary: 'sum'
                    }]
                }
            2) Load data with initial params
            3)  
            
        */
        
        // Restconfig
        $restconfig = json_decode($this->getRequest()->get('restconfig'));
        
        // Fetch the level we currently access from the Hierarch model
        $level = $this->getRequest()->get('_level');
    
        // Check the level of the current filter
        // -- not implemented yet -- 
        
        // Load Aggregate Info. This list from the highest to lowest hierarchy
        $agg = $restconfig->aggregate;
        
        // Load columns to be displayed
        $cols = $restconfig->columns;
        
        // Get the table info of the current model
        $tInfo = $this->getTableInfoObj();
        
        // Load the appName for tableInfo generation purposes
        $appName = $this->appName;
        
        // Last Index of The Hierarchy
        $lastIndex = (sizeof($agg->hierarchy)-1);
        
        // Check the level first. If the level equals last Index's level, skip all this mayhem
        if ($agg->hierarchy[$lastIndex]->level == $level) {
            
            // Stopping leaving the Criteria intact
            return $this->handleParams($c);

        } 
        
        // THIS IS THE TRICKY PART //
        // We need to: 
        // 1st. Clear select columns
        // 2nd. Loop each hierarchy and then stop on the level called

        // Clearing columns
        $c->clearSelectColumns();
        
        // Define base of the hierarchy
        $hbase = $agg->hierarchy[$lastIndex];
        $baseModelName = phpNamize($hbase->table_name);
        $baseTableInfo = Xond::createTableInfo($baseModelName, $this->appName);
        $basePeer = Xond::createPeer($baseModelName, $this->appName);
        $baseMap = Xond::createTableMap($baseModelName, $this->appName);
        
        
        for ($i = $lastIndex; $i > 0; $i--) {
            
            // When $i = 1 downwards
            $hc = $agg->hierarchy[$i];
            $childModelName = phpNamize($hc->table_name);
            $childTableInfo = Xond::createTableInfo($childModelName, $this->appName);
            $childPeer = Xond::createPeer($childModelName, $this->appName);
            $childMap = Xond::createTableMap($childModelName, $this->appName);
            
            // Preparing alias
            $c->addAlias($hc->alias, $childMap->getName());
            
            // H parent
            $hp = $agg->hierarchy[$i-1];
            $parentModelName = phpNamize($hp->table_name);
            $parentTableInfo = Xond::createTableInfo($parentModelName, $this->appName);
            $parentPeer = Xond::createPeer($parentModelName, $this->appName);
            $parentMap = Xond::createTableMap($parentModelName, $this->appName);
            
            // Preparing alias
            $c->addAlias($hp->alias, $parentMap->getName());
            
            // Then add the Join
            $c->addJoin(
                $parentPeer->alias($hc->alias, Rest::convertToColumnName($childTableInfo, $hc->parent_column)),
                $parentPeer->alias($hp->alias, Rest::convertToColumnName($parentTableInfo, $hp->table_id))
            );
            
            //echo "$i | $level\r\n";
            
            // End the query with a filter
            if ($i == $level) {
                $c->add(
                    $childPeer->alias($hc->alias, Rest::convertToColumnName($childTableInfo, $hc->parent_column)),
                    $this->getRequest()->get($hp->table_id)
                );
                break;
            }
                  
        } 
        
        // Add columns.
        // The columns NEED to be the "child" part of the hierarchy (last $hc on the code).
        // There's several possibility of values here:
        // 1) It's the non calculating column. This should display what the hierarchy table's 
        //    string representation. This is important: 
        //        NAME THE COLUMN EXACTLY LIKE THE DISPLAYING (STRING REP) OF THE BASE DATA.
        // 2) It's the calculating column. SUM ( them ) and name it the same.
            
        foreach ($cols as $col) {
            
            if ($col->name == 'id') {
                
                // Add row number if there is
                $columnStr = 'row_number() OVER (ORDER BY (SELECT 0)) ';
                
            } else if ($col->name == $agg->aggregate_column) {
                $columnStr = $childPeer->alias($hc->alias, Rest::convertToColumnName($childTableInfo, $hc->display_column));
            } else if (@$col->summary == "sum") {
                $columnStr = "sum(".Rest::convertToColumnName($tInfo, $col->name). ") ";
            } else if (@$col->summary == "count") {
                $columnStr = "count(".Rest::convertToColumnName($tInfo, $col->name). ") ";    
            } else if ($col->name == $hc->table_id) {
                $columnStr = $childPeer->alias($hc->alias, Rest::convertToColumnName($childTableInfo, $col->name));
            } else {
                $columnStr = $basePeer->alias($hbase->alias, Rest::convertToColumnName($baseTableInfo, $col->name));
                //$columnStr = $col->name;
            }
            
            $c->addAsColumn($col->name, $columnStr);
        }
        
        // Group columns
        foreach ($cols as $col) {
            if (!@$col->summary) {
                if ($col->name == 'id') {
                  // do nothin  
                } else if ($col->name == $agg->aggregate_column) {
                    $c->addGroupByColumn($childPeer->alias($hc->alias, Rest::convertToColumnName($childTableInfo, $hc->display_column)));
                } else if ($col->name == $hc->table_id) {
                    $c->addGroupByColumn($childPeer->alias($hc->alias, Rest::convertToColumnName($childTableInfo, $col->name)));
                } else {
                    $c->addGroupByColumn($basePeer->alias($hbase->alias, Rest::convertToColumnName($baseTableInfo, $col->name)));
                    //$c->addGroupByColumn($col->name);
                }
            }
        }

        return $c;
        
    }
    
    ////////////////////////
    /// Event Management ///
    ////////////////////////
    
    /**
     * Event Registration
     *
     * @param Application $app
     * @return Application
     */
    public function registerEvents(Application $app){
         
        $rest = $this;

        $app->on('rest_get.begin', function(Event $e) use ($rest) {
            $rest->onBegin($e, $rest);
        });
        
        $app->on('rest_get.calc_offset', function(Event $e) use ($rest) {
            $rest->onCalcOffset($e, $rest);
        });
    
        $app->on('rest_get.calc_limit', function(Event $e) use ($rest) {
            $rest->onCalcLimit($e, $rest);
        });

        $app->on('rest_get.custom_criteria', function(Event $e) use ($rest) {
            $rest->onCustomCriteria($e, $rest);
        });
                
        $app->on('rest_get.count', function(Event $e) use ($rest) {
            $rest->onCount($e, $rest);
        });
        
        $app->on('rest_get.data_load', function(Event $e) use ($rest) {
            $rest->onDataLoad($e, $rest);
        });
        
        $app->on('rest_get.response_str_load', function(Event $e) use ($rest) {
            $rest->onResponseStrLoad($e, $rest);
        });
            
        return $app;
    }
    
    /** These Events Must Be Overridden */
    // Example:
    // public function onCount(){
    //     $this->log("Counted records, result: ".$this->getRowCount()." rows found<br>\r\n");
    // }
    
    public function onBegin($e, $rest){
        
    }
    
    public function onCalcOffset($e, $rest){
        
    }
    
    public function onCalcLimit($e, $rest){
    
    }
    
    public function onCustomCriteria($e, $rest){
    
    }
    
    public function onCount($e, $rest){
    
    }
    
    public function onDataLoad($e, $rest){
    
    }
    
    public function onResponseStrLoad($e, $rest){
    
    }
    
    
}
