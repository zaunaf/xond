<?php
namespace Xond\Util;

class Menu
{
	public function show(\Symfony\Component\HttpFoundation\Request $request, \Silex\Application $app){
	    
	    $utilTemplatesDir = __DIR__."/templates";
	    $config = $app['xond.config'];
	    
	    $loader = new \Twig_Loader_Filesystem($utilTemplatesDir);
	    $twig = new \Twig_Environment($loader);
	    $tplStr = $twig->render('menu-template.php', $config);
	    		
		return $tplStr;
	}
}
