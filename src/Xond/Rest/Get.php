<?php

namespace Xond\Rest;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Exception\Exception;

use Xond\Info\TableInfo;
use Xond\Info\ColumnInfo;

class Get
{

    public function __construct(Request $request, Application $app, $config) {

        $this->setConfig($config);
        $this->setApp($app);
        $this->setRequest($request);
        
    }

    /**
     * Setting the request object for this generator
     * @param Request $request
     */
    public function setRequest(\Symfony\Component\HttpFoundation\Request $request){
        $this->request = $request;
    }
    
    /**
     * Getting the request
     * @return Request
     */
    public function getRequest(){
        return $this->request;
    }
    
    /**
     * Setting the Application object for this generator
     * @param Application $app
     */
    public function setApp(Application $app){
        $this->app = $app;
    }
    
    /**
     * Returning the Application object so whatever child need is available
     * @return Application
     */
    public function getApp(){
        return $this->app;
    }
    
    /**
     * Set config to be accessible troughout the class
     *
     * @param array $config
     */
    public function setConfig(array $config) {
        $this->config = $config;
        $this->appname = $config['project_php_name'];
    }
    
    /**
     * Get the config
     *
     * @return array
     */
    public function getConfig() {
        return $this->config;
    }
    
    public function getTableInfo($modelName="") {
        
        $tableInfoClassName = "\\".$this->appname."\\Info\\".$this->getModelName().'TableInfo';
        try {
            $tInfo = new ${'tableInfoClassName'}();
        } catch (Exception $e) {
            throw (new Exception("No such model", 404));
        }
        return $tInfo;
        
    }
    
    public function process(Request $request, Application $app) {
    
        // Prepare the object, setting peers etc.
        try {
            $this->prepare($request, $app);
        } catch (\Exception $e) {
            //return new Response('Object not found.', 400);
            return new Response("{ 'success' : false, 'message': 'Obyek tidak ditemukan.' }", 400);
        }
    
        // Create criteria for matching
        $c = new \Criteria();
    
        // Get the peer object for query management
        $p = $this->getPeerObj();
    
        // Handle limiters
        $start = ($request->get('start')) ? $request->get('start') : 0;
    
        //$limit = ($request->get('limit')) ? $request->get('limit') : 20;
        if ($request->get('limit') == 'unlimited') {
            $limit = 0;
        } else if (!$request->get('limit')) {
            $limit = 20;
        } else {
            $limit = $request->get('limit');
        }
    
        // Get TableInfo
        $tableInfoClassName = "\\DataDikdas\\Info\\".$this->getModelName().'TableInfo';
        $tInfo = new ${'tableInfoClassName'}();
    
        // Handle query
        $filterProperty = "";
    
        $query = $request->get('query');
        if ($query) {
            $query = "%".$query."%";
            $json = json_decode($request->get('filter'));
            //$filterProperty = $json[0]->property;
             
            $filterProperty = $tInfo->getDisplayField();
            //$columnName = $this->getPeerObj()->getTableMap()->getName().".".$filterProperty;
            $columnName = $this->getPeerObj()->getTableMap()->getName().".".$tInfo->getDisplayField();
            //echo "Columname: ".$columName."\n";
            $c->add($columnName, $query, \Criteria::ILIKE);
        }
    
        // Don't include row that is soft deleted
        // check if "soft_delete" is exist in the table
        $testModelName = "\\DataDikdas\\Model\\".$this->getModelName();
        $testObj = new ${'testModelName'}();
    
        if (method_exists($testObj, "getSoftDelete")) {
            $softDeleteColumnName = $this->getPeerObj()->getTableMap()->getName().".soft_delete";
            $c->add($softDeleteColumnName, 0, \Criteria::EQUAL);
        }
    
        if (method_exists($testObj, "getExpiredDate")) {
            $expiredDateColumnName = $this->getPeerObj()->getTableMap()->getName().".expired_date";
             
            $cton1 = $c->getNewCriterion($expiredDateColumnName, NULL, \Criteria::ISNULL);
            $cton2 = $c->getNewCriterion($expiredDateColumnName, date('Y-m-d H:i:s'), \Criteria::GREATER_THAN);
    
            $cton1->addOr($cton2);
            $c->add($cton1);
        }
    
        // Handle id (request to fill remote loaded big reference combo)
        $id = $request->get('id');
        if ($id) {
            $tableInfoClassName = "\\DataDikdas\\Info\\".$this->getModelName().'TableInfo';
            $t = new ${'tableInfoClassName'}();
            $pkName = $t->getPkName();
            $columnName = $this->getPeerObj()->getTableMap()->getName().".".$pkName;
            $c->add($columnName, $id, \Criteria::EQUAL);
        }
    
        // Other/Custom load params ()
        $params = $request->query->all();
    
        foreach ( $params as $key => $val ){
            if (!in_array($key, array("limit", "start", "query", "filter", "page", "_dc", "sort", "filter", "id"))){
    
                if ($key == "ascending") {
                    $c->addAscendingOrderByColumn($this->getPeerObj()->getTableMap()->getName().".".$val);
                    continue;
                }
                if ($key == "descending") {
                    $c->addDescendingOrderByColumn($this->getPeerObj()->getTableMap()->getName().".".$val);
                    continue;
                }
    
                if ($tInfo->getColumnByName($key)) {
    
                    $columnName = $this->getPeerObj()->getTableMap()->getName().".".$key;
                    $id = substr($columnName, -3, 3);
                    $custom = substr($val, 0, 1);
                     
                    if ($id == "_id") {
                        $c->add($columnName, $val, \Criteria::EQUAL);
                    } else {
    
                        if (in_array($typeColumn, array("string", "text"))) {
                            $val = str_replace(" ", "%", $val);
                            $val = "%".$val."%";
                             
                            $c->add($columnName, $val, \Criteria::ILIKE);
                        } else {
                            $c->add($columnName, $val, \Criteria::EQUAL);
                        }
    
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
    
        // Handle big FKs (or enabling left join filtering via foreign key. sadly only one step supported ^^ )
        if ($tInfo->getIsData()) {
            //$tInfo = new PtkTableInfo();
            $cols = $tInfo->getColumns();
            $joinColumns = array();
             
            foreach ($cols as $col) {
                //$col = new ColumnInfo();
                if ($col->getIsFk()) {
                    $fkTableInfo = $col->getFkTableInfo();
                    // print_r($fkTableInfo);
                    //$fkTableInfo = new SekolahTableInfo();
                     
                    if ($fkTableInfo->getIsBigRef()) {
                        $joinColumn = $fkTableInfo->getName().".".$fkTableInfo->getPkName();
                        if (!in_array($joinColumn, $joinColumns)) {
                             
                            // echo $tInfo->getName().".".$col->getName()."<br>";
                            if ($this->getModelName() == "PesertaDidikLongitudinal") {
                                $c->addJoin($tInfo->getName().".".$fkTableInfo->getPkName(), $joinColumn, \Criteria::LEFT_JOIN);
                            } else {
                                $c->addJoin($tInfo->getName().".".$col->getName(), $joinColumn, \Criteria::LEFT_JOIN);
                            }
                             
                        }
                        $joinColumns[] = $joinColumn;
                        //echo $tInfo->getName().".".$col->getName()." | ". $fkTableInfo->getName().".".$fkTableInfo->getPkName()."<br>";
                    }
                }
            }
        }
    
        // Handle big FKs (enabling right join not null filtering via relating tables)
        // 1) Passing parameter will be in format as follows
        //    RelatingTable.column_name: value
        // 2) Search matching class
        // 3) Create instances of TableInfo and Peer classes
        // 4) Add join
        // 5) Add value check by Relating Table's Peer
         
        //print_r($params); die;
    
        if ($tInfo->getIsData()) {
         
        //$tInfo = new PtkTableInfo();
            $relatingColumns = $tInfo->getRelatingColumns();
            //print_r($relatingColumns); die;
             
            foreach ($relatingColumns as $rc) {
    
            @list($className, $fieldName) = explode(".", $rc);
    
            // print_r($params);
                    foreach ($params as $key => $val){
                     
                    @list($paramsClassName, $fieldName) = explode("-", $key);
                     
                    // echo $fieldName."<br>";
                    if (!$fieldName) {
                    continue;
                    }
                     
                    // echo $className ."==". $paramsClassName."<br>";
                    // Match the searched classname
                    if ($className == $paramsClassName) {
                        // echo "masuk sini";
    
                        $paramsTableInfoClassName = "\\DataDikdas\\Info\\".$paramsClassName.'TableInfo';
                        $paramsTableClassName = "\\DataDikdas\\Model\\".$paramsClassName;
                        $paramsTablePeerName = $paramsTableClassName."Peer";
    
                        $paramsTableInfo = new ${'paramsTableInfoClassName'}();
                        $paramsPeerName = new ${'paramsTablePeerName'}();
    
                        $localColumn = $tInfo->getName().".".$tInfo->getPkName();
                        $rightColumn = $paramsTableInfo->getName().".".$tInfo->getPkName(); // $paramsTableInfo->getPkName();
                        $paramsColumn = $paramsTableInfo->getName().".".$fieldName;
                        $softDel = $paramsTableInfo->getName().".soft_delete";
                        // echo $localColumn."-".$rightColumn."<br>"; die();
    
                        $c->addJoin($localColumn, $rightColumn, \Criteria::LEFT_JOIN);
    
                        // Match the key (field name)
                        // echo $key;
                        $paramsColumnInfo = $paramsTableInfo->getColumnByName($fieldName);
                        // print_r($paramsColumnInfo);
                        if (!is_object($paramsColumnInfo)) {
                        continue;
                    }
                    $typeColumn =  $paramsColumnInfo->getType();
    
                    // echo $paramsColumn;
                    $other = substr($val, 0, 1);
                    // $c->add($softDel, 0);
    
                            if (in_array($typeColumn, array("string", "text")) && $other != "#") {
                            // echo "array(string, text)";
                            $val = str_replace(" ", "%", $val);
                            $val = "%".$val."%";
                            $c->add($paramsColumn, $val, \Criteria::ILIKE);
    
                            } else if ($other == "#") {
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
     
    //Get total number of row exists
    //print_r($c); die;
    //print_r($c->toString()); die();
    //$rowCount = $p->doCount($c);
    
    $rowCount = $p->doCount($c);
    //print_r($c); die;
    // Set limit. Limit will be disabled when $limit = 0. Be careful though ;)
    $c->setOffset($start);
    if ($limit > 0) {
    $c->setLimit($limit);
    }
    
    // Set order by primary key
    $p->doSelect($c);
    
    $connection = \Propel::getConnection(PesertaDidikPeer::DATABASE_NAME);
    $connection->useDebug(false);
    
    // print_r ("Last Query: ". $c->toString()); die();
    $tArr = $p->doSelect($c, $connection);
    
    
        $id = $p->getFieldNames(\BasePeer::TYPE_FIELDNAME);
    
            // Get TableInfo
                    $tableInfoClassName = "\\DataDikdas\\Info\\".$this->getModelName().'TableInfo';
                    $tInfo = new ${'tableInfoClassName'}();
    
                        // Inital vals
                        $outArr = array();
    
                        // Process for all row
                        foreach ($tArr as $t) {
                         
                        //Search for FK columns
                        /*
                        $cols = $tInfo->getColumns();
                        foreach ($cols as $col) {
                        $col = new ColumnInfo();
                        if ($col->getIsFk()) {
                        $colPhpName = $col->getColumnPhpName();
                    }
                    }
                     
    $tInfo = new PtkTableInfo();
    $tInfo->get
    $table = new Ptk();
    $table->getSekolah()->getNama();
        */
         
        $arr = $t->toArray(\BasePeer::TYPE_FIELDNAME);
         
        // Cleans odd decimal / float values for FK //
        //print_r($arr); die;
        $counter = 0;
            foreach ($arr as $key=>$val) {
            //echo "$key = $val ".($this->floatBukan($val) ? "Float" : "Not float")."|"; continue;
            if (($counter == 0) && $this->floatBukan($val)) {
            //echo $val."<br>";
                $arr["$key"] = intval($val);
                    //break;
                }
                $counter++;
                }
                 
                // Add "_str"
                 
                if ($tInfo->getIsData()) {
    
                foreach ($cols as $col) {
                //$col = new ColumnInfo();
                if ($col->getIsFk()) {
                $fkTableInfo = $col->getFkTableInfo();
                //$fkTableInfo = new SekolahTableInfo();
                $fkTableObj = "";
    
                if ($fkTableInfo->getIsBigRef()) {
                $fkColumnName = $col->getName();
                $fkTableName = phpnamize($fkTableInfo->getName());
                $fkDisplayField = phpnamize($fkTableInfo->getDisplayField());
                $fkColumnNameStr = $fkColumnName."_str";
                $fkTableNameStr = "get".$fkTableName;
                $fkDisplayFieldStr = "get".$fkDisplayField;
                //echo "Colname = $fkColumnNameStr | FKTableName = $fkTableNameStr | FKDisplayField = $fkDisplayFieldStr <br>";
                $fkTableObjRelatedBy = $fkTableNameStr."RelatedBy".$col->getPhpName();
                 
                if (method_exists($t,$fkTableNameStr)) {
                $fkTableObj = $t->$fkTableNameStr();
                } else if (method_exists($t, $fkTableObjRelatedBy)) {
                //echo $fkTableObjRelatedBy;
                $fkTabelObj = $t->$fkTableObjRelatedBy();
                //print_r($fkTabelObj);
                //die;
                }
                if (method_exists($fkTableObj, $fkDisplayFieldStr)) {
                $arr["$fkColumnNameStr"] = $fkTableObj->$fkDisplayFieldStr();
                }
                //$c->addJoin($tInfo->getName().".".$col->getName(), $fkTableInfo->getName().".".$fkTableInfo->getPkName());
                //echo $tInfo->getName().".".$col->getName()." | ". $fkTableInfo->getName().".".$fkTableInfo->getPkName()."<br>";
                }
                }
                }
                    }
                     
                    // Handling for composite PKs //
                    if ($tInfo->getIsCompositePk()) {
                     
                    $cols = $tInfo->getColumns();
    
                        $pkName = $tInfo->getPkName();
                        $virtualPkStr = "";
    
                        $i = 1;
    
                        foreach ($cols as $col) {
                        //echo $col->getName()."\n";
                        // Skip first column (virtual column)
                        if ($i == 1) {
                        $i++;
                        continue;
                        }
                         
                        if ($col->getIsPk()) {
    
                        $colName = $col->getName();
                        $virtualPkStr .= ($i > 2) ? ":" : "";
                        $virtualPkStr .= $arr["$colName"];
                        //echo "Found PK : $colName<br>\n";
                        //echo "Value: ". $arr["$colName"]."<br>\n";
                        //echo "Current val: ".$virtualPkStr."<br>\n";
    
                    }
                        $i++;
                    }
    
                    $arr["$pkName"] = $virtualPkStr;
    
                        //print_r($id);
    
                        // Removing the pk from column list
                        //$key = array_search($pkName, $id);
                        //unset($id[$key]);
    
                        // Add pkname to the top using merge
                        array_unshift($id, $pkName);
                        //print_r($id);
    
    }
    
                         
    $outArr[] = $arr;
     
     
    }
    
        //print_r($outArr);
        return tableJson($outArr, $rowCount, $id);
        //return tableJson(getArray($tArr, \BasePeer::TYPE_FIELDNAME), $rowCount, $id);
        //return "Processing GET for model ".$request->get('model')." number ".$request->get('which');
        //return "Processing GET for model ".$this->getModelName()." number ".$this->getWhich();
    
    }
    
}