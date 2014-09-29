<?php

namespace Xond\Info;

class HboxformInfo extends GroupInfo
{
	const COMP_TYPE = 'container';  
	public $members;	

	public function __construct(){
		parent::__construct();
	}	
	
}