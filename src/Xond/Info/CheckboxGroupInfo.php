<?php

namespace Xond\Info;

class CheckboxGroupInfo extends GroupInfo
{
    const COMP_TYPE = 'checkboxgroup';
    public $members;

    public function __construct(){
        parent::__construct();
    }

    public function setColumnNumber($colNum) {
        $this->column_number = $colNum;
    }

    public function getColumnNumber(){
        return $this->column_number;
    }
}
