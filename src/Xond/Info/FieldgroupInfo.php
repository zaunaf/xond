<?php

namespace Xond\Info;

class FieldgroupInfo extends GroupInfo
{
    const COMP_TYPE = 'fieldcontainer';
    public $members;

    public function __construct(){
        parent::__construct();
    }

}