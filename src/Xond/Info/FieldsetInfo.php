<?php

namespace Xond\Info;

class FieldsetInfo extends GroupInfo
{
    const COMP_TYPE = 'fieldset';
    public $members;
    	
    public function __construct(){
        parent::__construct();
    }


}