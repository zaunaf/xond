<?php
namespace Xond\Util;

class Menu
{
    public function getTwig(\Symfony\Component\HttpFoundation\Request $request, \Silex\Application $app){
        
        $utilTemplatesDir = __DIR__."/templates";
        $config = $app['xond.config'];
         
        $loader = new \Twig_Loader_Filesystem($utilTemplatesDir);
        $twig = new \Twig_Environment($loader);
        
        return $twig;
        
    }
	public function show(\Symfony\Component\HttpFoundation\Request $request, \Silex\Application $app){
	    
	    $config = $app['xond.config'];
	    $twig = $this->getTwig($request, $app);
	    $tplStr = $twig->render('menu-template.php', $config);
	    		
		return $tplStr;
		
	}
	public function iconFonts(\Symfony\Component\HttpFoundation\Request $request, \Silex\Application $app){
	     
	    $config = $app['xond.config'];
	    $twig = $this->getTwig($request, $app);
	    $tplStr = $twig->render('iconfonts-template.php', $config);
	    return $tplStr;
	    
	}
	
}
