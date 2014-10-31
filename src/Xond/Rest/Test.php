<?php
/**
 * This file is part of the Xond package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

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