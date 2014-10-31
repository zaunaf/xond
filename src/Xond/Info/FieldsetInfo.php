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
 * This is a base class that will be instaniated by the users to extend
 * the base generated TableInfo to define fieldset of ExtJS form fields.
 *
 * @author     Donny Fauzan <donny.fauzan@gmail.com> (Nufaza)
 * @version    $Revision$
 * @package    xond.info
 */

class FieldsetInfo extends GroupInfo
{
    const COMP_TYPE = 'fieldset';
    public $members;
    	
    public function __construct(){
        parent::__construct();
    }


}