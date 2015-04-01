<?php

/**
 * This file is part of the Xond package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Xond\Gen;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * This is a password generator class for build a new unique password
 *
 * @author     Fajri Abdillah <clasense4@gmail.com> (Nufaza)
 * @version    0.1.0
 * @package    xond.gen
 */

class PassGen extends BaseGen
{
    public function generate(Request $request, Application $app) {
        $password = $request->get('password');
        return $app['security.encoder.digest']->encodePassword($password, '');
    }
}