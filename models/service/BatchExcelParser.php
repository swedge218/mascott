<?php

/**
     * Excel file fields
     * A - SN
     * B - Batch Number
     * C - Date of Manufacture
     * D - Date of Expiry
     * E - Quantity Registered
     * F - MAS Code Assign 
     * G - MAS Code Status
     */

namespace app\models\service;

use Yii;
use app\models\Batch;
use app\models\utils\Trailable;
use \app\models\Product;
use app\models\Provider;

/**
 * Description of ExcelParser
 * Used to fetch and parse the usage report excel files
 * Makes use of moonland PHPExcel Yii2 Extension
 *
 * @author Swedge
 */
class BatchExcelParser {
    //put your code here
    private $_startRow = 0;
    private $_productRow = 0;
    private $_nrnRow = 0;
    private $_fileName = '';
    private $_rowNumber = 0;
    private $_errors = array();
    private $_rowCoreValues = array();
    
    public function __construct($startRow, $productRow, $nrnRow, $fileName) {
        $this->_startRow = $startRow;
        $this->_productRow = $productRow;
        $this->_nrnRow = $nrnRow;
        $this->_fileName = $fileName;
        $this->_rowNumber = $startRow-1;
    }
    
    private function fetchFile($fileName){
        $data = \moonland\phpexcel\Excel::import($fileName, [
                'mode' => 'import',
		'setFirstRecordAsKeys' => false, // if you want to set the keys of record column with first record, if it not set, the header with use the alphabet column on excel. 
		'setIndexSheetByName' => true, // set this if your excel data with multiple worksheet, the index of array will be set with the sheet name. If this not set, the index will use numeric. 
		'getOnlySheet' => 'MAS BATCH REG FORM', // you can set this property if you want to get the specified sheet from the excel data with multiple worksheet.
	]);
        
        return $data;
    }
    
    public function run(){
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '2048M');
        
        $fileData = $this->fetchFile($this->_fileName);
        //var_dump($fileData[20]['C']); exit;
        
        $productName = $fileData[$this->_productRow]['B'];
        $productValidationResult = $this->validateProduct($productName);
        if(!empty($productValidationResult))
                    $this->_errors[$this->_productRow] = [$productValidationResult];

        $nrn = $fileData[$this->_nrnRow]['B'];
        $nrnValidationResult = $this->validateNRN($nrn);
        if(!empty($nrnValidationResult))
                    $this->_errors[$this->_nrnRow] = [$nrnValidationResult];
        
                    
        $productObject = $this->validateProductAndNRN($productName, $nrn);
        if(empty($productObject))
            $this->_errors[$this->_productRow . '&' . $this->_nrnRow] = ['Product and NAFDAC Reg. Number do not match'];
        
//        var_dump($this->_errors);
//        exit;
        
        if(!empty($fileData)){
            //chop off the non-content rows 
            for($i = 0; $i < $this->_startRow-1; $i++){
                array_shift($fileData);
            }
            //var_dump($fileData); exit;
            foreach($fileData as $k=>$row){
                $this->_rowNumber++;
                
                if(empty($row['A'])) continue; //assume the row is not filled with data at all. Just a blank field that excel did not clear
                
                $this->_rowCoreValues = array();
                
                //$errorsArray = $this->hasRequiredFields($row);
                $errorsArray = $this->hasRequiredFields($fileData, $k);
                if(!empty($errorsArray)){
                    $this->_errors[$this->_rowNumber] = $errorsArray;
                    continue;
                }
            }
            
            //echo 'start creating rows';exit;
            
            
            //file parsed without errors
            $this->_rowNumber = $this->_startRow - 1;
            if(empty($this->_errors)) {
                $this->_rowNumber++;
                foreach($fileData as $row){
                    $createResult = $this->createBatch($row, $productObject->id);
                    if($createResult !== true){
                        $this->_errors[$this->_rowNumber] = $createResult;
                    }
                }
            }
                
                
        }//end if fileData
        
        return $this->_errors;
            
    }
    
    private function createBatch($row, $productId){ 
        $model = new Batch(); 
        //$model = new Product(); 
        $row = array_values($row);
        $values= [];
        
        if(strlen($row[0]) == 0) return true;  
            
        foreach($row as $v){
            if(strlen($v) > 0)
                $values[] = "$v";
        }
        
        //var_dump($values); exit;
        
        foreach($values as $k=>$v){
            //var_dump($values); exit;
//          echo $values[2] . '<br>';
//          echo date('Y-m-d', strtotime($values[2])) . '<br>'; 
//          echo date('Y-m-d', strtotime($values[3])); exit;
          
          switch($k){
              case 1:
                $model->batch_number = $values[1];
                break;
              case 2:
                $model->manufacturing_date = date('Y-m-d', strtotime($values[2]));
                break;
              case 3:
                $model->expiry_date = date('Y-m-d', strtotime($values[3]));
                break;
              case 4:
                $model->quantity = $values[4];
                break;
              case 5:
                $model->mas_code_assigned = strtoupper($values['5']) == 'YES' ? 2 : 1;
                break;
              case 6:
                $model->mas_code_status = strtoupper($values['6']) == 'ACTIVATED' ? 2 : 1;
                break;
          }
        }
        
        $model->product_id = $productId;
        
        //var_dump($model->attributes); exit;
        
        (new Trailable($model))->registerInsert(); //audit trail
                    
        if($model->save()){  
            //echo 'VALIDATED';
            //var_dump($model->getErrors()); exit;
            return true;
        } else {
            //echo 'NO validate';
            //var_dump($model->getErrors()); exit;
            return $model->getErrors();
        }     
        exit;
    }
    
    
    /**
     * Check if the row has all the required fields
     * Required Fields: product name, product type, NRN, Batch number, 
     * last 4 digits, location, response
     * 
     * return array (of error messages)
     */
    private function hasRequiredFields(&$fileData, $k){
        $row = $fileData[$k];
        $errors = array();
        
        if(empty($row['B'])) 
            $errors[] = 'Batch number cannot be blank';
        else if(!empty( Batch::find()->where(['batch_number' => $row['B']])->one() ))
            $errors[] = 'Batch number ' . $row['B'] . ' already exists';
        else 
            $this->_rowCoreValues['batch_number'] = $row['B'];     //batch number
        
        //manufacturing date
        if(empty($row['C']))
            $errors[] = 'Date of Manufacture cannot be blank'; 
        else if (!empty($row['C'])){
            $manuDate = $row['C'];
            $manuDate = \DateTime::createFromFormat('m-d-y', $manuDate);
            if($manuDate == false){
                $errors[] = 'Incorrect date value for Date of Manufacture'; 
            }
            else {
                $row['C'] = $manuDate->format('d-m-y');
                $dateArray = explode('-', $row['C']);
                if(strlen($dateArray[2]) == 2) $dateArray[2] = '20' . $dateArray[2];
                $manuDate = implode('-', $dateArray);
                $this->_rowCoreValues['manufacturing_date'] = $manuDate;
                $fileData[$k]['C'] = $manuDate;
            }
        }
        
        //expirty date
        if(empty($row['D']))
            $errors[] = 'Date of Expiry cannot be blank'; 
        else if (!empty($row['D'])){
            $expDate = $row['D'];
            $expDate = \DateTime::createFromFormat('m-d-y', $expDate);
            if($expDate == false){
                $errors[] = 'Incorrect date value for Date of Expiry';
            }
            else {
                $row['D'] = $expDate->format('d-m-y');
                $dateArray = explode('-', $row['D']);
                if(strlen($dateArray[2]) == 2) $dateArray[2] = '20' . $dateArray[2];
                $expDate = implode('-', $dateArray);
                $this->_rowCoreValues['expiry_date'] = $expDate;
                $fileData[$k]['D'] = $expDate;
            }
        }
        
        //quantity
        if(empty($row['E'])) 
            $errors[] = 'Quantity Registered cannot be blank'; 
        else if ($quantityBoolean = is_int($row['E'])){
            if($quantityBoolean == false)
                $errors[] = 'Incorrect value for Quantity Registered';
        }
        else 
            $this->_rowCoreValues['quantity'] = $row['E'];
        
        //MAS Code Assigned
        if(empty($row['F'])) 
            $errors[] = 'MAS Code Assigned cannot be blank'; 
        else if ($row['F'] == 'Yes' || $row['F'] == 'No'){
            $this->_rowCoreValues['mas_code_assigned'] = $row['F'];
        }
        else {
            $errors[] = 'Incorrect value for MAS Code Assigned';
        }
            
        
        //Status of MAS Code
        if(empty($row['G'])) 
            $errors[] = 'Status of MAS Code cannot be blank'; 
        else if (strtoupper($row['G']) == 'ACTIVATED' || strtoupper($row['G']) == 'NOT ACTIVATED'){
            $this->_rowCoreValues['mas_code_status'] = $row['G'];
        }
        else {
            $errors[] = 'Incorrect value for Status of MAS Code';
        }
        
        return $errors;
        
    }

    
    private function validateProduct($productName){
        if(empty($productName))   
            return 'Product name cannot be blank'; 
        else {
            $productObject = Product::find()->where(['UPPER(product_name)' => strtoupper($productName)])->one();
            if(empty($productObject)) 
                return 'Product <strong>' . $productName . '</strong> not found'; 
        }
        
        return '';
    }
    
    private function validateNRN($nrn){
        if(empty($nrn))   
            return 'NAFDAC Reg. Number cannot be blank'; 
        else {
            $productObject = Product::find()->where(['UPPER(nrn)' => strtoupper($nrn)])->one();
            if(empty($productObject)) 
                return 'NAFDAC Reg. Number does not match any product'; 
        }
        
        return '';
    }
    
    private function validateProductAndNRN($productName, $nrn){        
        return Product::find()->where([
                    'UPPER(product_name)' => strtoupper($productName),
                    'UPPER(nrn)' => strtoupper($nrn)
                ])->one();
    }
}
