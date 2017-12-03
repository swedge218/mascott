<?php

/**
     * Excel file fields
     * A - SN
     * B - Product Name (Good)
     * C - Product Type (Good)
     * D - Dosage form
     * E - NAFDAC Reg Number (Good)
     * F - Country of production
     * G - Brand Name
     * H - Generic Name
     * I - Manu. Date
     * J - Expiry Date
     * K - Batch No. (Good)
     * L - Lats 4 digits of PIN (Good)
     * M - Location of MAS Usage (Good)
     * N - Response Status (Good)
     * N - Response(Genuine)
     * O - Response(Fake)
     * P - Response(Invalid)
     */

namespace app\models\service;

use Yii;
use app\models\Batch;
use app\models\UsageReport;
use app\models\Provider;
use app\models\Product;
use app\models\Location;
use app\models\utils\Trailable;
use app\models\Permission;
use app\controllers\services\AlertsService;

/**
 * Description of ExcelParser
 * Used to fetch and parse the usage report excel files
 * Makes use of moonland PHPExcel Yii2 Extension
 *
 * @author Swedge
 */
class RequestExcelParser {
    //put your code here
    private $_startRow = 2;
    private $_providerRow = 0;
    private $_productRow = 0;
    private $_fileName = '';
    private $_rowNumber = 0;
    private $_errors = array();
    private $_rowCoreValues = array();
    
    public function __construct($startRow, $productRow, $providerRow, $fileName) {
        $this->_startRow = $startRow;
        $this->_productRow = $productRow;
        $this->_providerRow = $providerRow;
        $this->_fileName = $fileName;
        $this->_rowNumber = $startRow-1;
    }
    
    private function fetchFile($fileName){
        $data = \moonland\phpexcel\Excel::import($fileName, [
                'mode' => 'import',
		'setFirstRecordAsKeys' => false, // if you want to set the keys of record column with first record, if it not set, the header with use the alphabet column on excel. 
		'setIndexSheetByName' => true, // set this if your excel data with multiple worksheet, the index of array will be set with the sheet name. If this not set, the index will use numeric. 
		'getOnlySheet' => 'MAS REQUEST REPORT FORM', // you can set this property if you want to get the specified sheet from the excel data with multiple worksheet.
	]);
        
        return $data;
    }
    
    public function run(){
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '2048M');
        
        $fileData = $this->fetchFile($this->_fileName);
        $batch = new Batch();
        
        $provider = '';
        $providerName = $fileData[$this->_providerRow]['C'];
        $providerValidationResult = $this->validateProvider($providerName);
        if(!empty($providerValidationResult))
            $this->_errors[$this->_providerRow] = [$providerValidationResult];
        else 
            $provider = Provider::find()->where(['UPPER(provider_name)' => strtoupper($providerName)])->one();
        
        
        $productName = '';
        $productName = $fileData[$this->_productRow]['C'];
        $productValidationResult = $this->validateProduct($productName);
        if(!empty($productValidationResult))
                    $this->_errors[$this->_productRow] = [$productValidationResult];
        
        
        $productObject = $this->validateProductAndProvider($productName, $providerName);
        if(empty($productObject))
            $this->_errors[$this->_productRow . '&' . $this->_providerRow] = ['Product and Provider do not match'];
        
                
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
                
                $errorsArray = $this->hasRequiredFields($fileData, $k);
                if(!empty($errorsArray)){
                    $this->_errors[$this->_rowNumber] = $errorsArray;
                    continue;
                }
                
                //args: product_name, product type title, NRN, batch number
                if(!$batch->hasMatch($productName, $this->_rowCoreValues['batch_number'])){
                    $this->_errors[$this->_rowNumber] = array('No matching records found: <br/>' .
                                                            'Product Name: <strong>' . $productName . '</strong><br/>' .
                                                            'Batch Number: <strong>' . $row['B'] . '</strong>'
                                                    ); //make array for easy/uniform procesing on view
                    continue;
                }
            }
            
//            echo 'about to start creating'; 
//            var_dump($this->_errors);
//            exit;
            
            //file parsed without errors
            $this->_rowNumber = $this->_startRow - 1;
            if(empty($this->_errors)) { 
                $this->_rowNumber++;
                foreach($fileData as $row){
                    //if(empty($row['A'])) continue;
                    $createResult = $this->createReport($row);
                    if($createResult !== true){
                        $this->_errors[$this->_rowNumber] = $createResult;
                    }
                }
            }
                
                
        }//end if fileData
        
        return $this->_errors;
            
    }
    
    private function createReport($row){                
        $model = new UsageReport(); $locationId = 0;
        //echo $row['M']; exit;
        $row = array_values($row);
        $values= [];
        
        if(strlen($row[0]) == 0) return true;  
            
        foreach($row as $v){
            if(strlen($v) > 0)
                $values[] = "$v";
        }
        
        //var_dump($values); exit;
        foreach($values as $k=>$v){

          switch($k){
              case 1:
                  $model->batch_number = $values[1];
                  break;
              case 2:
                  $model->phone = $values[2];
                  break;
              case 3: 
                  $model->date_reported = date('Y-m-d', strtotime($values[3]));
                  break;
              case 4:
                  $model->pin_4_digits = $values[4] . ''; //convert to string for validation
                  break;
              case 5:
                  if(strtoupper($values[5]) === UsageReport::GENUINE) {
                        $model->response = UsageReport::GENUINE_DBVALUE;
                  } else if(strtoupper($row[5]) == UsageReport::FAKE) {
                        $model->response = UsageReport::FAKE_DBVALUE;
                  } else if(strtoupper($row[5]) == UsageReport::INVALID) {
                        $model->response = UsageReport::INVALID_DBVALUE;
                  } else { 
                    $model->response = UsageReport::INVALID_DBVALUE;
                  }
                  break; 
          }
        }
        
        $model->location_id = 0; //unknown location
        
                (new Trailable($model))->registerInsert(); //audit trail

                if($model->save()){    
                    //send email if response is NOT Genuine i.e. fake or invalid
                    if($model->getResponseAsText() != UsageReport::GENUINE){
                        $permission = Permission::find()->where(['alias'=>'resolution_reminder'])->one();
                        $permissionUsers = $permission->getMyUsers();
                        (new AlertsService())->sendResolutionRequestEmail($permissionUsers, $model);
                    }

                    return true;
                } else {
                    return $model->getErrors();
                }
        
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
        
        if(empty($row['B'])) $errors[] = 'Batch number cannot be blank'; else $this->_rowCoreValues['batch_number'] = $row['B'];     //BAtch number
        if(empty($row['C'])) $errors[] = 'Phone number cannot be blank'; else $this->_rowCoreValues['phone_number'] = $row['C'];     //phone
        
        //date reported
        if(empty($row['D']))
            $errors[] = 'Date Reported cannot be blank'; 
        else if (!empty($row['D'])){
            $dateReported = $row['D'];
            $dateReported = \DateTime::createFromFormat('m-d-y', $dateReported);
            if($dateReported == false){
                $errors[] = 'Incorrect date value for Date Reported';
            }
            else {
                $row['D'] = $dateReported->format('d-m-y');
                $dateArray = explode('-', $row['D']);
                if(strlen($dateArray[2]) == 2) $dateArray[2] = '20' . $dateArray[2];
                $dateReported = implode('-', $dateArray);
                $this->_rowCoreValues['reported_date'] = $dateReported;
                $fileData[$k]['D'] = $dateReported;
            }
        }
        
        
        if(empty($row['E'])) 
            $errors[] = 'Last 4 digits of PIN cannot be blank'; 
        else {
            $last4 = $row['E'];
            if(strlen($last4) != 4)
                $errors[] = 'Exactly 4 digits required for last 4 digits of PIN';
            else
                $this->_rowCoreValues['last4digits'] = $row['E'];    //last 4 digits
        }

        if(empty($row['F'])) 
            $errors[] = 'At least one response type must be selected'; 
        else if(strtoupper($row['F']) == UsageReport::GENUINE || 
                strtoupper($row['F']) == UsageReport::FAKE || 
                strtoupper($row['F']) == UsageReport::INVALID
               ) {
               $this->_rowCoreValues['response'] = $row['N'];
        } else{
            $errors[] = 'Incorrect value for Request Response';
        }
        
        //echo 'date: ' . $row['D'] . '<br><br>';
        //echo 'response: ' . $row['F'] . '<br><br>'; exit;
        
        /**
         * LOCATION
        if(empty($row['M'])) $errors[] = 'Location cannot be blank';     //location
        $location = Location::find()->where(['UPPER(location_name)' => strtoupper($row['M'])])->one();
        if($location === null) {
            $errors[] = 'Location does not exist'; 
        } else {
            $this->_rowCoreValues['location_name'] = $location->location_name;
            $row['M'] = $location->location_name;
        }
         * 
         */
        
          
        return $errors;
        
    }

    
    function validateProvider($providerName){
        $errors = [];
        if(empty($providerName)) 
            return $errors[] = 'Provider cannot be blank'; 
        else {
            $provider = Provider::find()->where(['UPPER(provider_name)' => strtoupper($providerName)])->one();
            if($provider == null) {
                return $errors[] = 'Provider <strong>' . $providerName . '</strong> does not exist'; 
            }
        } 
        
        return '';
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
    
    
    private function validateProductAndProvider($productName, $providerName){      
        return Product::find()
                ->innerJoinWith(['provider'])
                ->where([
                    'UPPER(product_name)' => strtoupper($productName) . '',
                    'UPPER(provider_name)' => strtoupper($providerName)
                ])->one();  
    }
}
