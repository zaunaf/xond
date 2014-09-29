<?php

/**
 * This file is part of the Xond package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
*/

/**
 * This is a service provider that links Silex with the Xond libraries and generators.
 * This file registers the extended php libraries, register the config 
 *
 * InfoGen, FrontEnd Gen and all extends this class
 *
 * @author     Donny Fauzan <donny.fauzan@gmail.com> (Nufaza)
 * @version    $Revision$
 * @package    xond.gen
 */

namespace Xond\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;


class XondServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        // Include Additional Functions
        if (!is_file(__DIR__.'/../../../lib/functions.php')) {
            die ('file functions.php not found');
        }
        
        require_once __DIR__.'/../../../lib/functions.php';
        
        // Include Configurations
        $config = require_once $app['xond.config_file'];
        
        // Project PHP Name
        $config['project_php_name'] = $config['nama_singkat'];
        
        // Project Folder
        $config['project_folder'] = 
                $config['nama_folder_penyimpanan'].
                    DIRECTORY_SEPARATOR.$config['nama_folder']; 
        
        // Project's Main Class Folder
        $config['class_folder'] = 
                $config['project_folder'].
                    DIRECTORY_SEPARATOR.'src'.
                    DIRECTORY_SEPARATOR.$config['project_php_name'];
        
        // Project's Main Web Folder
        $config['web_folder'] =
                $config['project_folder'].
                DIRECTORY_SEPARATOR.'web';

        // Register it
        $app['xond.config'] = $config;
        
        // Add route for menu
        $app->get('/Menu', '\Xond\Util\Menu::show');

        // Info Generation
        $app->get('/InfoGen', '\Xond\Gen\InfoGen::generate');
        
        // Unit Test Generation
        $app->get('/TestGen', '\Xond\Gen\TestGen::generate');
        
        // Models Generation
        $app->get('/ModelGen', '\Xond\Gen\ModelGen::generate');
        
        // Frontend Generation
        $app->get('/FrontEndGen', '\Xond\Gen\FrontEndGen::generate');
        
    }

    public function boot(Application $app)
    {
        
    }

}
