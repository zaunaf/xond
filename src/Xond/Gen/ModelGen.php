<?php

namespace Xond\gen;

use \Symfony\Component\HttpFoundation\Request;
use \Silex\Application;

class ModelGen extends BaseGen {

    public function reverse(Request $request, Application $app) {
        
        global $config;
        
        $localhostDir = $config["nama_folder_penyimpanan"];
        $folderName = $config["nama_folder"];
        $projectDir = $localhostDir."/".$folderName;
        $sdkDir = $config["sdk_folder"];
        $namaApp = $config["nama_singkat"];
        
        //die ($projectDir."/app/config");
        
        try {
            	
            if (getOs() == "Windows") {
                chdir($projectDir."/app/config");
                $out = shell_exec('set_path.bat && reverse_structure.bat');
            } else {
                chdir($projectDir."/app/config");
                $out = shell_exec('source set_path.sh && echo $PATH && source reverse_structure.sh');
            }
            	
            // Preparing schema-design xml
            $schemaPath = $projectDir."/app/config/schema.xml";
            	
            $schemaDesignStr = file_get_contents($schemaPath);
            if (!$schemaDesignStr) {
                throw new Exception("File $schemaPath gagal dibuka");
            }
            	
            $schemaDesignStr = str_replace("defaultIdMethod","namespace=\"".$config["nama_singkat"]."\Model\" defaultIdMethod", $schemaDesignStr);
            	
            $target = $projectDir."/app/config/schema-design.xml";
            	
            $fp = fopen($target, 'w');
            if (!$fp) {
                throw new Exception("File $target gagal dibuka");
            }
            if (!fwrite($fp, $schemaDesignStr)) {
                throw new Exception("File $target gagal ditulisi");
            }
            fclose($fp);
            	
            $status = 1;
            	
        } catch (Exception $e) {
            	
            $status = 2;
            $errMsg = $e->getMessage();
            	
        }
        return ("<pre>$out</pre>");
        //return ($status == 1) ? "Success reversing database<br>" : "Failed reversing database <br>";
        
    }
    
    public function build(Request $request, Application $app) {
           
        global $config;
        
        $localhostDir = $config["nama_folder_penyimpanan"];
        $folderName = $config["nama_folder"];
        $projectDir = $localhostDir."/".$folderName;
        $sdkDir = $config["sdk_folder"];
        $namaApp = $config["nama_singkat"];
        
        if (getOs() == "Windows") {
            chdir($projectDir."/app/config");
            $out = shell_exec('set_path.bat && build_model.bat');
        } else {
            chdir($projectDir."/app/config");
            $out = shell_exec('source set_path.sh && source build_model.sh');
        }
        
        //die($out);
        
        if (strpos($out, "BUILD FAILED")) {
            $status = 2;
            $errMsg = "build somehow failed";
        } else {
            $status = 1;
        }
        
        return ("<pre>$out</pre>");
        //return ($status == 1) ? "Success building ORM<br>" : "Failed building ORM <br>";
        
    }
    
}