<?php
/**
 * This file is part of the Xond package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Xond\Rest;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\EventDispatcher\Event;
use Xond\Rest;
use Xond\Info\TableInfo;
use Xond\Info\ColumnInfo;

/**
 * This is class that extends Export class for export to Excel purposes match with it's REST proxy.
 *
 * @author     Donny Fauzan <donny.fauzan@gmail.com> (Nufaza)
 * @version    $Revision$
 * @package    xond.info
 */

class Excel extends Export {
        
    
    public function getExcel() {
    
        // Apply template
        $sourceTplDir = __DIR__."/templates/";
        $file = $sourceTplDir."/".$this->getTemplate();

        $reader = \PHPExcel_IOFactory::createReaderForFile($file);
        $excelFile = new \PHPExcel();
        $excelFile = $reader->load($file);
        
        return $excelFile;
    }
    
    public function render($excelFile, $data){
        
        $namaFile = $data["filename"];
        
        try {
            
            $ds = $excelFile->getSheet(0);
            $ds->getCell("A1")->setValue($data["title"]);
            $ds->getCell("A2")->setValue($data["subtitle"]);
    
            // Determine range of alphabets (assuming < 26 columns exported)
            $alphas = range("A", "Z");
            $i = 0;
    
            // Set header names
            foreach ($data["headers"] as $h) {
                $ds->getCell($alphas[$i]."4")->setValue($h);
                $i++;
            }

            // Get the last column's alphabet for headers
            $lastColumn = $alphas[$i-1];
            
            // Style the sheet
            $styleArrayHeader = array(
                'alignment' => array(
                    'indent' => 1
                ),
                'font' => array(
                    'name' => 'Calibri',
                    'size' => '12'
                ),
                'borders' => array(
                    'top' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array(
                            'argb' => '00000000',
                        )
                    ),
                    'bottom' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_MEDIUM,
                    ),
                )
            );
    
            $styleArray = array(
                'alignment' => array(
                    'indent' => 1
                ),
                'font' => array(
                    'name' => 'Calibri',
                    'size' => '12'
                ),
                'borders' => array(
                    'right' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array(
                            'argb' => '00000000',
                        )
                    ),
                    'left' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array(
                            'argb' => '00000000',
                        )
                    ),
                    'bottom' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_NONE,
                    ),
                )
            );
    
            $styleArrayFooter = array(
                'alignment' => array(
                    'indent' => 1
                ),
                'font' => array(
                    'name' => 'Calibri',
                    'size' => '12'
                ),
                'borders' => array(
                    'top' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array(
                            'argb' => '00000000',
                        )
                    ),
                    'bottom' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    ),
                )
            );
    
            // Format the header. Assuming the header row is 4.
            foreach (range('A', $lastColumn) as $alphabet) {
                $ds->duplicateStyleArray($styleArrayHeader, $alphabet."4");
            }
    
            // Start filling the data from row 5 of the sheet
            $row = 5;
    
            foreach ($data["data"] as $d)
            {
                // Prepare array to get the columns from
                $colAlphas = range("A", $lastColumn);
                
                // Prepare counter
                $j = 0; 
                
                // Fill in the cells
                foreach ($data["columns"] as $key) {
                    $ds->getCell($colAlphas[$j].$row)->setValue($d[$key]);
                    $j++;
                }
                
                // Reset the counter
                $j = 0; 
                
                // Add garis
                foreach (range('A', $lastColumn) as $alphabet) {
                    $ds->duplicateStyleArray($styleArray, $alphabet.$row);
                }
                
                // Add numbering format (later on, get from columnInfo)
                // foreach (range('B', $lastColumn) as $alphabet) {
                //    $ds->getStyle($alphabet.$row)->getNumberFormat()->setFormatCode('#,##');
                // }
    
                $row++;
            }
            
            // Summary belum ada jadinya $row. Kalau sudah ada summary, nanti $row-1 ya. (Garis terakhir)
            foreach (range('A', $lastColumn) as $alphabet) {
                $ds->duplicateStyleArray($styleArrayFooter, $alphabet.($row));
            }
    
        } catch (Exception $e){
    
        }
    
        $filename = $namaFile.".xlsx";
    
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($excelFile, 'Excel2007');
        $objWriter->save('php://output'); die;
    
    }
    
    public function doExport($exportData) {
        
        $this->setTemplate('default_excel.xlsx');
        $excelFile = $this->getExcel();
        
        $outStr = $this->render($excelFile, $exportData);
    }
    
}