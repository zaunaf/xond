<?php

namespace Xond;

/**
 * This file is part of the Xond package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * This is the Main Xond Class.
 * This file registers the extended php libraries. Just that currently.
 *
 * @author     Donny Fauzan <donny.fauzan@gmail.com> (Nufaza)
 * @version    $Revision$
 * @package    xond
 */

class Xond
{
    public function __construct() {
        // Include Additional Functions
        if (!is_file(__DIR__.'/../../lib/functions.php')) {
            die ('file functions.php not found');
        }
        
        require_once __DIR__.'/../../../lib/functions.php';
    }        
}
