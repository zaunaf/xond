<?php

/**
 * This file is part of the Xond package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Xond\gen;

use \Symfony\Component\HttpFoundation\Request;
use \Silex\Application;

/**
 * This is a generator class that helps the execution of Propel's command line 
 * Model reverse and generation. This helper also aids the skipping of schema, tables
 * and columns, and also adds namespace to the reverse schema result.
 *
 * @author     Donny Fauzan <donny.fauzan@gmail.com> (Nufaza)
 * @version    $Revision$
 * @package    xond.gen
 */

class ModelGen extends BaseGen {

    public function reverse(Request $request, Application $app) {    

        // Initialize
        $this->initialize($request, $app);
        
        $config = $this->getConfig();        
        $localhostDir = $config["nama_folder_penyimpanan"];
        $folderName = $config["nama_folder"];
        $projectDir = $localhostDir."/".$folderName;
        $sdkDir = $config["sdk_folder"];
        $namaApp = $config["nama_singkat"];
        
        $os = getOs();
        
        try {
        
            if ($os == "Windows") {
        
                chdir($projectDir."/app/config");
                // $out = shell_exec('set_path.bat && reverse_structure.bat');
                $cmd = 'set_path.bat && reverse_structure.bat';
        
            } else {
        
                chdir($projectDir."/app/config");
                //$out = shell_exec('source set_path.sh && echo $PATH && source reverse_structure.sh');
                $cmd ='source set_path.sh && echo $PATH && source reverse_structure.sh';
        
            }
        
            execute($cmd, null, $out, $out, $config["execution_timeout"]);
        
            $app['monolog']->addInfo($out);
        
            // Throw if any of ugly report comes out
            if (stripos($out, "BUILD FAILED")) {
                throw new \Exception("Build somehow FAILED");
        
            }
            if (stripos($out, "Aborting")) {
                throw new \Exception("Build somehow aborted");
            }
                    
            // 1. readline_info()g from schema.xml
            $schemaPath = $projectDir."/app/config/schema.xml";
            
            if (!is_file($schemaPath)) {
                throw new \Exception("File $schemaPath gagal dibuka, proses reverse gagal..");
                $app['monolog']->addError($errMsg);
            }
        
            $schemaDesignObj = simplexml_load_file($schemaPath);
        
            // Skip shit
            $skipSchemas = explode(",", str_replace(' ', '', $config["skip_schemas"]));
            $skipTables = explode(",", str_replace(' ', '', $config["skip_tables"]));
            $skipColumns = explode(",", str_replace(' ', '', $config["skip_columns"]));
        
            if (!$schemaDesignObj) {
                throw new \Exception("File $schemaPath gagal dimuat dalam XML Object");
        
            }
            $schemaDesignObj->addAttribute("namespace", $config["nama_singkat"]."\\Model" );
        
            // echo "Jumlah tabel sebelumnya : ".sizeof($schemaDesignObj->table)."<br>\r\n";
        
            $tablesToRemove = "";
            $columnsToRemove = "";
        
            foreach ($schemaDesignObj->table as $t) {
        
                $schemaName = $t['schema'] == "" ? "dbo" : $t['schema'];
                $tableName = $t['name'];
        
                if (contains($schemaName, $skipSchemas)) {
                    // echo "SKIPSCHEMA -- $schemaName<br>\r\n";
                    $tablesToRemove[] = $t;
                    continue;
                }
        
                if (contains($tableName, $skipTables)) {
                    // echo "SKIPTABLE -- $tableName<br>\r\n";
                    $tablesToRemove[] = $t;
                    continue;
                }
        
                // Clean up tables with space in it
                if (strpos($tableName, " ")) {
                    // echo "SKIPTABLE -- $tableName<br>\r\n";
                    $tablesToRemove[] = $t;
                    continue;
                }
        
                // echo "Schema: ". $schemaName."; Table: ".$tableName."<br>\r\n";
        
                foreach ($t->column as $c) {
        
                    $columnName = $c['name'];
        
                    if (contains($columnName, $skipColumns)) {
                        // echo "SKIPCOLUMN -- $columnName<br>\r\n";
                        $columnsToRemove[] = $c;
                        continue;
                    }
        
                    // echo "&nbsp;&nbsp;&nbsp; Column: ". $c['name']."<br>\r\n";
                }
        
            }
        
            if (is_array($tablesToRemove)) {
                foreach ($tablesToRemove as $t) {
                    unset ($t[0]);
                }
            }
        
            if (is_array($columnsToRemove)) {
                foreach ($columnsToRemove as $c) {
                    unset ($c[0]);
                }
            }
        
        
            // echo "Jumlah tabel sesudahnya : ".sizeof($schemaDesignObj->table)."<br>\r\n";;
        
            $schemaDesignStr = $schemaDesignObj->asXml();
            //$schemaDesignStr = preg_replace('/[\n\r]+/', '', $schemaDesignStr);
            //$schemaDesignStr = str_replace(array("\n", "\r"), '', $schemaDesignStr);
        
            // Effort to clean up lines
            $xmlObj = simplexml_load_string($schemaDesignStr);
            $schemaDesignStr = $xmlObj->asXml();
        
            $target = $projectDir."/app/config/schema-design.xml";
        
            $fp = fopen($target, 'w');
            if (!$fp) {
                throw new \Exception("File $target gagal dibuka");
            }
            if (!fwrite($fp, $schemaDesignStr)) {
                throw new \Exception("File $target gagal ditulisi");
            }
        
            fclose($fp);
        
            $status = 1;
            // echo "<br>\r\nFile written with no exception";
            return "Everything seems fine.. <a href='/Menu'>Back</a>";
            
        } catch (\Exception $e) {
        
            $status = 2;
            $errMsg = $e->getMessage();
            $app['monolog']->addError($errMsg);
             
            return "Build failed. Please read log.. <a href='/Menu'>Back</a>";
            
        }
    }
    
    public function build(Request $request, Application $app) {
        
        // Initialize
        $this->initialize($request, $app);
        $config = $this->getConfig();
        $os = getOs();
        
        // Get vars
        $localhostDir = $config["nama_folder_penyimpanan"];
        $folderName = $config["nama_folder"];
        $projectDir = $localhostDir."/".$folderName;
        $sdkDir = $config["sdk_folder"];
        $namaApp = $config["nama_singkat"];
        
        try {
        
            if ($os == "Windows") {
                chdir($projectDir."/app/config");
                //$out = shell_exec('set_path.bat && build_model.bat');
                $cmd = 'set_path.bat && build_model.bat';
                execute($cmd, null, $out, $out, $config["execution_timeout"]);
        
            } else {
                chdir($projectDir."/app/config");
                //$out = shell_exec('source set_path.sh && source build_model.sh');
        
                $cmd = 'source set_path.sh && source build_model.sh';
                execute($cmd, null, $out, $out, $config["execution_timeout"]);
            }
        
            $app['monolog']->addInfo($out);
        
            if (stripos($out, "BUILD FAILED")) {
                throw new \Exception("Build somehow FAILED");
        
            }
            if (stripos($out, "Aborting")) {
                throw new \Exception("Build somehow ABORTED");
            }
            
            $status = 1;
            
            return "Everything seems fine.. <a href='/Menu'>Back</a>";
        
        } catch (\Exception $e) {
        
            $status = 2;
            $errMsg = $e->getMessage();
            $app['monolog']->addError($errMsg);
            
            return "Build failed. Please read log.. <a href='/Menu'>Back</a>";
            
        }
        
    }
        
}