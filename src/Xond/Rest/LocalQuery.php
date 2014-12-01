<?php

namespace Xond\Rest;

class LocalQuery
{
    function __construct($params) {
        $this->params = $params;
    }
    function all(){
        return $this->params;
    }
}