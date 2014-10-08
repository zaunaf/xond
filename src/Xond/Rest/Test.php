<?php

class Test {
    
    public function getTableInfo($modelName="") {
    
        $tableInfoClassName = "\\".$this->appname."\\Info\\".$this->getModelName().'TableInfo';
        try {
            $tInfo = new ${'tableInfoClassName'}();
        } catch (Exception $e) {
            throw (new Exception("No such model", 404));
        }
        return $tInfo;
    
    }
    
    /**
     * 
     */
    public function test($int = 10, $str = "test") {
        return false;
    }
}