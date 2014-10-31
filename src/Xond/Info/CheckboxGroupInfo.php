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
 * This is a base class as an abstract for CheckBoxGroupInfo component
 * that based on ExtJS CheckBoxGroup.
 *
 * @author     Donny Fauzan <donny.fauzan@gmail.com> (Nufaza)
 * @version    $Revision$
 * @package    xond.info
 */

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
