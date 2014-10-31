<?php

/**
 * This file is part of the Xond package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */
namespace Xond\Info;

/**
 * This is an abstract class that should be extended to define any style of 
 * field grouping further, like FieldGroup, Fieldset, Hbox etc.
 *
 * @author     Donny Fauzan <donny.fauzan@gmail.com> (Nufaza)
 * @version    $Revision$
 * @package    xond.info
 */

class GroupInfo
{
    public $title;
    public $group_id;				// number, each table restart to 1
    public $parent_group_id;		// if cascade / tree grouping
    public $grouping_method;		// inline / fieldset / vbox / hbox / etc

    public function __construct(){
    	// Nothing i can think of. Yet.	
    }
    
    public function getTitle( ) {
    	return $this->title;
    }
    
    public function setTitle( $title ) {
    	$this->title = $title;
    }
    
    public function getGroupId( $group_id ) {
    	return $this->group_id;
    }
    
    public function setGroupId( $group_id ) {
    	$this->group_id = $group_id;
    }
    
    public function getParentGroupId( $parent_group_id ) {
    	return $this->parent_group_id;
    }
    
    public function setParentGroupId( $parent_group_id ) {
    	$this->parent_group_id = $parent_group_id;
    }
    
    public function getGroupingMethod( $grouping_method ) {
    	return $this->grouping_method;
    }
    
    public function setGroupingMethod( $grouping_method ) {
    	$this->grouping_method = $grouping_method;
    }
    
    public function addColumn($columnInfo) {
    	$this->members[] = $columnInfo;
    }
    
    public function getColumns() {
    	return $this->members;
    }
    
    function getColumnByName($name) {
    	$cols = $this->getColumns();
    	foreach($cols as $c) {
    		if ($c->getColumnName() == $name) {
    			$retObj = $c;
    			break;
    		}
    	}
    	return $retObj;
    }

    function getXtype() {
    	return $this::COMP_TYPE;
    }    
    
}