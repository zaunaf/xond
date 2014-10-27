<?php
namespace Xond\Rest;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\EventDispatcher\Event;
use Xond\Rest;
use Xond\Info\TableInfo;
use Xond\Info\ColumnInfo;

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
        
        // Create criteria for matching
        $c = new \Criteria();
        
        // Get the peer object for query management
        $p = $this->getPeerObj();
        
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
        
        // Handle query
        $filterProperty = "";
        
        $query = $request->get('query');
        if ($query) {
            $query = "%" . $query . "%";
            $json = json_decode($request->get('filter'));
            // $filterProperty = $json[0]->property;
            
            $filterProperty = $tInfo->getDisplayField();
            // $columnName = $this->getPeerObj()->getTableMap()->getName().".".$filterProperty;
            $columnName = $this->getPeerObj()
                ->getTableMap()
                ->getName() . "." . $tInfo->getDisplayField();
            // echo "Columname: ".$columName."\n";
            
            $c->add($columnName, $query, \Criteria::LIKE);
        }
        
        $c = $this->injectFilter($c);
        $c = $this->handleId($c);
        $c = $this->handleParams($c);
        $c = $this->handleBigLeftJoinFk($c);
        $c = $this->handleBigRightJoinFk($c);
        
        // Get total number of row exists
        // print_r($c); die;
        // print_r($c->toString()); die();
        // $rowCount = $p->doCount($c);
        
        // Do row count
        $rowCount = $p->doCount($c);
        $this->setRowCount($rowCount);
        
        // Kick the count event
        $app['dispatcher']->dispatch('rest_get.count');
        
        // print_r($c); die;
        // Set limit. Limit will be disabled when $limit = 0. Be careful though ;)
        $c->setOffset($this->getStart());
        if ($this->getLimit() > 0) {
            $c->setLimit($this->getLimit());
        }
        
        // Set order by primary key
        $p->doSelect($c); // What is THIS ?
        
        $connection = \Propel::getConnection(\Propel::getDefaultDB());
        $connection->useDebug(false);
        
        // print_r("Last Query: ". $c->toString()); die();
        $tArr = $p->doSelect($c, $connection);
        
        // $id = $p->getFieldNames(\BasePeer::TYPE_FIELDNAME);
        $fieldNames = $this->createFieldNames($this->getModelName());
        $this->setFieldNames($fieldNames);
        
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
            
            $arr = $this->addFkStrings($arr, $t);
            
            // Handling for composite PKs //
            if ($tInfo->getIsCompositePk()) {
                
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
                array_unshift($id, $pkName);
                // print_r($id);
            }
            
            $outArr[] = $arr;
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

    private function handleId(\Criteria $c)
    {
        
        // Handle id(request to fill remote loaded big reference combo)
        $id = $this->getRequest()->get('id');
        
        if ($id) {
            
            $t = $this->getTableInfoObj();
            $pkName = $t->getPkName();
            $columnName = $this->getPeerObj()
                ->getTableMap()
                ->getName() . "." . $pkName;
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
            if (! in_array($key, array(
                "limit",
                "start",
                "query",
                "filter",
                "page",
                "_dc",
                "sort",
                "filter",
                "id"
            ))) {
                
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
                
                if ($tInfo->getColumnByName($key)) {
                    
                    $cInfo = $tInfo->getColumnByName($key);
                    
                    /*
                    $columnName = $this->getPeerObj()
                        ->getTableMap()
                        ->getName() . "." . $key;
                    */
                    $columnName = Rest::convertToColumnName($tInfo, $cInfo->getName());
                    $typeColumn = $cInfo->getType();
                    
                    // Detect if it's an id
                    $id = substr($columnName, - 3, 3);
                    
                    // Custom parameter value, non variable
                    $custom = substr($val, 0, 1);
                    
                    
                    
                    if ($id == "_id") {
                        
                        $c->add($columnName, $val, \Criteria::EQUAL);
                        
                    } else {
                        
                        if (in_array($typeColumn, array(
                            "string",
                            "text"
                        ))) {
                            $val = str_replace(" ", "%", $val);
                            $val = "%" . $val . "%";
                            
                            if (get_adapter() == 'pgsql') {
                                $c->add($columnName, $val, \Criteria::ILIKE);
                            } else {
                                $c->add($columnName, $val, \Criteria::LIKE);
                            }
                        } else {
                            $c->add($columnName, $val, \Criteria::EQUAL);
                        }
                        
                        // Switcher, if non standard value is given as params
                        if ($custom == "#") {
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
                        }
                    }
                }
            }
        }
        
        return $c;
    }

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
                        
                        $fkColumnName = $col->getName();
                        $fkTableName = phpnamize($fkTableInfo->getName());
                        $fkDisplayField = phpnamize($fkTableInfo->getDisplayField());
                        $fkColumnNameStr = $fkColumnName . "_str";
                        $fkTableNameStr = "get" . $fkTableName;
                        $fkDisplayFieldStr = "get" . $fkDisplayField;
                        // echo "Colname = $fkColumnNameStr | FKTableName = $fkTableNameStr | FKDisplayField = $fkDisplayFieldStr <br>";
                        $fkTableObjRelatedBy = $fkTableNameStr . "RelatedBy" . $col->getPhpName();
                        
                        if (method_exists($t, $fkTableNameStr)) {
                            $fkTableObj = $t->$fkTableNameStr();
                        } else 
                            if (method_exists($t, $fkTableObjRelatedBy)) {
                                // echo $fkTableObjRelatedBy;
                                $fkTabelObj = $t->$fkTableObjRelatedBy();
                                // print_r($fkTabelObj);
                                // die;
                            }
                        if (method_exists($fkTableObj, $fkDisplayFieldStr)) {
                            $arr["$fkColumnNameStr"] = $fkTableObj->$fkDisplayFieldStr();
                        }
                        // $c->addJoin($tInfo->getName().".".$col->getName(), $fkTableInfo->getName().".".$fkTableInfo->getPkName());
                        // echo $tInfo->getName().".".$col->getName()." | ". $fkTableInfo->getName().".".$fkTableInfo->getPkName()."<br>";
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
    
        $app->on('rest_get.calc_offset', function(Event $e) use ($rest) {
            $rest->onCalcOffset();
        });
    
        $app->on('rest_get.calc_limit', function(Event $e) use ($rest) {
            $rest->onCalcLimit();
        });

        $app->on('rest_get.count', function(Event $e) use ($rest) {
            $rest->onCount();
        });
        
        $app->on('rest_get.data_load', function(Event $e) use ($rest) {
            $rest->onDataLoad();
        });
        
        $app->on('rest_get.response_str_load', function(Event $e) use ($rest) {
            $rest->onResponseStrLoad();
        });
            
        return $app;
    }
    
    /** These Events Must Be Overridden */
    // Example:
    // public function onCount(){
    //     $this->log("Counted records, result: ".$this->getRowCount()." rows found<br>\r\n");
    // }
    
    public function onCalcOffset(){
        
    }
    
    public function onCalcLimit(){
    
    }
    
    public function onCount(){
    
    }
    
    public function onDataLoad(){
    
    }
    
    public function onResponseStrLoad(){
    
    }
    
    
}
