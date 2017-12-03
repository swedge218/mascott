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
use app\models\Location;
use app\models\utils\Trailable;
use app\models\Permission;
use app\controllers\services\AlertsService;
use app\models\HCR;
use app\models\Country;
use \app\models\Product;
use app\models\ProductType;
use app\models\Provider;

/**
 * Description of ExcelParser
 * Used to fetch and parse the usage report excel files
 * Makes use of moonland PHPExcel Yii2 Extension
 *
 * @author Swedge
 */
class ProductExcelParser {
    //put your code here
    private $_startRow = 19;
    private $_providerRow = 0;
    private $_fileName = '';
    private $_rowNumber = 0;
    private $_errors = array();
    private $_rowCoreValues = array();
    
    public function __construct($startRow, $providerRow, $fileName) {
        $this->_startRow = $startRow;
        $this->_fileName = $fileName;
        $this->_rowNumber = $startRow-1;
        $this->_providerRow = $providerRow;
    }
    
    private function fetchFile($fileName){
        $data = \moonland\phpexcel\Excel::import($fileName, [
                'mode' => 'import',
		'setFirstRecordAsKeys' => false, // if you want to set the keys of record column with first record, if it not set, the header with use the alphabet column on excel. 
		'setIndexSheetByName' => true, // set this if your excel data with multiple worksheet, the index of array will be set with the sheet name. If this not set, the index will use numeric. 
		'getOnlySheet' => 'MAS PRODUCT REG FORM', // you can set this property if you want to get the specified sheet from the excel data with multiple worksheet.
	]);
        
        return $data;
    }
    
    public function run(){
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '2048M');
        
        $fileData = $this->fetchFile($this->_fileName);
        
        $provider = '';
        $providerName = $fileData[$this->_providerRow]['C'];
        $providerValidationResult = $this->validateProvider($providerName);
        if(!empty($providerValidationResult))
            $this->_errors[$this->_providerRow] = [$providerValidationResult];
        else 
            $provider = Provider::find()->where(['UPPER(provider_name)' => strtoupper($providerName)])->one();
        
        if(!empty($fileData)){
            //chop off the non-content rows 
            for($i = 0; $i < $this->_startRow-1; $i++){
                array_shift($fileData);
            }
            //var_dump($fileData); exit;
            foreach($fileData as $row){
                $this->_rowNumber++;

                if(empty($row['A'])) continue; //assume the row is not filled with data at all. Just a blank field that excel did not clear
                
                $this->_rowCoreValues = array();
                
                $errorsArray = $this->hasRequiredFields($row);
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
                    //if(empty($row['A'])) continue;
                    $createResult = $this->createProduct($row, $provider->id);
                    if($createResult !== true){
                        $this->_errors[$this->_rowNumber] = $createResult;
                    }
                }
            }
                
                
        }//end if fileData
        
        return $this->_errors;
            
    }
    
    private function createProduct($row, $providerId){     
        $model = new Product(); 
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
            $values[$k] . '<br/>';
        
          switch($k){
              case 1:
                $model->product_name = $values[1];
                break;
              case 2:
                $model->product_type = ProductType::findOne(['UPPER(title)' => strtoupper("$values[2]")])->id;
                break;
              case 3:
                $model->dosage_form = $values[3];
                break;
              case 4:
                $model->certificate_holder = HCR::findOne(['UPPER(name)' => strtoupper($values[4])])->id; //hcr
                break;
              case 5:
                $model->production_country = Country::findOne(['UPPER(name)' => strtoupper($values[5])])->id; //hcr //country
                break;
              case 6:
                $model->brand_name = $values[6]; //brand name
                break;
              case 7:
                $model->generic_name = $values[7]; //generic name
                break;
              case 8;
                $model->nrn = strtoupper($values[8]);
                break;
          }
        }
        
        $model->provider_id = $providerId;
        
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
        
    }
    
    
    /**
     * Check if the row has all the required fields
     * Required Fields: product name, product type, NRN, Batch number, 
     * last 4 digits, location, response
     * 
     * return array (of error messages)
     */
    private function hasRequiredFields($row){
        $errors = array();
        
        if(empty($row['B'])) $errors[] = 'Product name cannot be blank'; 
        $product = Product::find()->where(['UPPER(product_name)' => strtoupper($row['B'])])->one();
        if($product !== null){
            $errors[] = 'Product <strong>' . $row['B'] . '</strong> already exists'; 
        } else {
            $this->_rowCoreValues['product_name'] = $row['B'];     //product name
        }
        
        if(empty($row['C'])) $errors[] = 'Product type cannot be blank'; 
        $productType = ProductType::find()->where(['UPPER(title)' => strtoupper($row['C'])])->one();
        if($productType === null) {
            $errors[] = 'Product type <strong>' . $row['C'] . '</strong> does not exist'; 
        } else {
            $this->_rowCoreValues['product_type'] = $productType->title;
            $row['C'] = $productType->title;
        }
        
        if(empty($row['D'])) $errors[] = 'Dosage Form cannot be blank'; else $this->_rowCoreValues['dosage_form'] = $row['D'];    //dosage form
        
        if(empty($row['E'])) $errors[] = 'Holder of Certificate cannot be blank';     //HCR
        $hcr = HCR::find()->where(['UPPER(name)' => strtoupper($row['E'])])->one();
        if($hcr === null) {
            $errors[] = 'Certificate holder <strong>' . $row['E'] . '</strong> does not exist'; 
        } else {
            $this->_rowCoreValues['certificate_holder'] = $hcr->name;
            $row['E'] = $hcr->name;
        }
        
        if(empty($row['F'])) $errors[] = 'Country cannot be blank';     //HCR
        $country = Country::find()->where(['UPPER(name)' => strtoupper($row['F'])])->one();
        if($country === null) {
            $errors[] = 'Country <strong>' . $row['F'] . '</strong> does not exist'; 
        } else {
            $this->_rowCoreValues['production_country'] = $country->name;
            $row['F'] = $country->name;
        }
        
        if(empty($row['G'])) $errors[] = 'Brand name cannot be blank'; else $this->_rowCoreValues['brand_name'] = $row['G'];    //brand name
        if(empty($row['H'])) $errors[] = 'Generic name number cannot be blank'; else $this->_rowCoreValues['generic_name'] = $row['H'];    //generic name
        
        if(empty($row['I'])) $errors[] = 'NAFDAC Reg. Number cannot be blank'; 
        $product = Product::find()->where(['UPPER(nrn)' => strtoupper($row['I'])])->one();
        if($product != null) {
            $errors[] = 'NAFDAC Reg. Number <strong>' . $row['I'] . '</strong> already exists'; 
        } else {
            $this->_rowCoreValues['nrn'] = $row['I'];
        }
        
//        if(empty($row['O'])) 
//            $errors[] = 'MAS Code Assigned cannot be blank'; 
//        else 
//            $this->_rowCoreValues['mas_code_assigned'] = strtoupper($row['O']) == 'YES' ? 2 : 1;        
//        
//        if(empty($row['P'])) 
//            $errors[] = 'MAS Code Status cannot be blank'; 
//        else 
//            $this->_rowCoreValues['mas_code_status'] = strtoupper($row['P']) == 'ACTIVATED' ? 2 : 1;
        
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
}
