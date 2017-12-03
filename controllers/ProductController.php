<?php

namespace app\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use yii\web\Response;

use app\models\Product;
use app\models\ProductType;
use app\models\Batch;
use app\models\Location;
use app\models\utils\Trailable;
use app\controllers\services\HCRService;
use app\controllers\services\CountryService;
use app\controllers\services\ProviderService;
use app\controllers\services\ProductTypeService;
use app\controllers\services\ProductService;
use app\controllers\services\LocationService;
use app\models\utils\Uploader;
use app\models\service\ProductExcelParser;
use app\models\service\BatchExcelParser;


/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Product models.
     * @return mixed
     */
    public function actionIndex()
    {
        $this->checkPermission(['view_edit_form_a']); 
        
        $roleConditionArray = Product::myRoleACL();
        $products = Product::find()->where($roleConditionArray)->all();

        return $this->render('index', [
            'products' => $products,
        ]);
    }

    public function actionReport()
    {
        $this->checkPermission(['view_edit_form_a']); 
        
        $roleConditionArray = Product::myRoleACL();
        $products = Product::find()->where($roleConditionArray)->all();
        
        $model = new Product();
        $hcrService = new HCRService();
        $countryService = new CountryService();
        $providerService = new ProviderService();
        $ptService = new ProductTypeService();
        $productService = new ProductService();
        $locationService = new LocationService();
        
        $hcrMap = $hcrService->getHCRMap(); $hcrMap[0] = '--Select HCR--'; ksort($hcrMap);
        $countryMap = $countryService->getCountryMap(); $countryMap[0] = '--Select Country--'; ksort($countryMap);
        $providerMap = $providerService->getProviderMap(); $providerMap[0] = '--Select Provider--'; ksort($providerMap);
        $ptMap = $ptService->getProductTypesMap(); $ptMap[0] = '--Select Product Type--'; ksort($ptMap);
        $productMap = $productService->getProductMap(); $productMap[0] = '--Select Product--'; ksort($productMap);
        $locationsHieJson = $locationService->getLocationsHierachyAsJson();
        
        if ($model->load(Yii::$app->request->post())) {
            $filtersArray = array_merge($model->attributes, $roleConditionArray);
            
            $reportArray = $model->getProductReport($filtersArray);
            echo json_encode($reportArray);
            exit;
        }
        
        return $this->render('report', [
            'products' => $products,
            'model' => $model,
            'hcrMap' => $hcrMap,
            'countryMap' => $countryMap,
            'providerMap' => $providerMap,
            'ptMap' => $ptMap,
            'productMap' => $productMap,
            'lh'=> $locationsHieJson
        ]);
    }

    /**
     * Creates a new Product model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $this->checkPermission(['view_edit_form_a']); 
        
        $model = new Product();
        $uploaderModel = new Uploader(['scenario' => Uploader::SCENARIO_EXCEL]);
        $hcrService = new HCRService();
        $countryService = new CountryService();
        $providerService = new ProviderService();
        $ptService = new ProductTypeService();

        $hcrMap = $hcrService->getHCRMap(); $hcrMap[0] = '--Select HCR--'; ksort($hcrMap);
        $countryMap = $countryService->getCountryMap(); $countryMap[0] = '--Select Country--'; ksort($countryMap);
        $providerMap = $providerService->getProviderMap(); $providerMap[0] = '--Select Provider--'; ksort($providerMap);
        $ptMap = $ptService->getProductTypesMap(); $ptMap[0] = '--Select Product Type--'; ksort($ptMap);
        
        if ($model->load(Yii::$app->request->post())) {
            (new Trailable($model))->registerInsert();
            if ($model->save()) {
                Yii::$app->session->setFlash('saved', 'CREATED');
                return $this->redirect(['update', 'id' => $model->id, 'new' => true]);                
            }
        }
                
        return $this->render('create', [
            'model' => $model,
            'uploaderModel' => $uploaderModel,
            'hcrMap' => $hcrMap,
            'countryMap' => $countryMap,
            'providerMap' => $providerMap,
            'ptMap' => $ptMap
        ]);
    }

    /**
     * Updates an existing Product model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id, $new = false)
    {
        $this->checkPermission(['view_edit_form_a']); 
        
        $model = $this->findModel($id);
        $uploaderModel = new Uploader(['scenario' => Uploader::SCENARIO_EXCEL]);
        
        $roleTitle = Yii::$app->session['user_role_title'];
        $providerId = Yii::$app->session['user_provider_id'];
         if(!$model->isMyProduct()) 
                throw new \yii\web\ForbiddenHttpException();
        
        $batchModel = new Batch();
        $batches = $model->batches;
        
        $hcrService = new HCRService();
        $countryService = new CountryService();
        $providerService = new ProviderService();
        $ptService = new ProductTypeService();

        $hcrMap = $hcrService->getHCRMap(); $hcrMap[0] = '--Select HCR--'; ksort($hcrMap);
        $countryMap = $countryService->getCountryMap(); $countryMap[0] = '--Select Country--'; ksort($countryMap);
        $providerMap = $providerService->getProviderMap(); $providerMap[0] = '--Select Provider--'; ksort($providerMap);
        $ptMap = $ptService->getProductTypesMap(); $ptMap[0] = '--Select Product Type--'; ksort($ptMap);
        
        $new == true ?  Yii::$app->session->setFlash('saved', Yii::$app->session->getFlash('saved')) : '';
        
        if ($model->load(Yii::$app->request->post())) {
            //if($model->validate()) echo 'validated'; else var_dump($model->getErrors()); exit;
            (new Trailable($model))->registerUpdate();
            if($model->save()) {
                //echo 'saved'; exit;
                Yii::$app->session->setFlash('saved', 'UPDATED');
            }
        }
        
        return $this->render('update', [
            'model' => $model,
            'uploaderModel' => $uploaderModel,
            'hcrMap' => $hcrMap,
            'countryMap' => $countryMap,
            'providerMap' => $providerMap,
            'ptMap' => $ptMap,
            'batchModel' => $batchModel,
            'batches' => $batches
        ]);
        
    }

    /**
     * Deletes an existing Product model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->checkPermission(['view_edit_form_a', 'delete_product']); 
        
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Product model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Product the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Product::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    
    
    /*************************************************
     * BATCH ACTIONS
     ************************************************/
    
    /**
     * AJAX ACTION
     * Creates a new Batch model.
     * If creation is successful, echo batch JSON of model attributes as return
     */
    public function actionCreateBatch()
    {
        $this->checkPermission(['view_edit_form_a']); 
        
        $model = new Batch(['scenario' => Batch::SCENARIO_AJAX]);
        
        (new Trailable($model))->registerInsert();

        if ($model->load(Yii::$app->request->post())) {
            $model->manufacturing_date = date('Y-m-d', strtotime($model->manufacturing_date));
            $model->expiry_date = date('Y-m-d', strtotime($model->expiry_date));
            if($model->save())
                echo json_encode([
                    'result' => json_encode($model->attributes),
                    'status' => 'OK'
                ]);
            else
                echo json_encode([
                    'result' => json_encode($model->getErrors ()),
                    'status' => 'ERROR'
                ]);
        } else {
            echo json_encode($model->attributes); 
        }
        exit;
    }

    /**
     * AJAX
     * Updates an existing Batch model.
     * @return JSON
     */
    public function actionUpdateBatch()
    {
        $this->checkPermission(['view_edit_form_a']); 
        
        $id = $_POST['Batch']['id'];
        $model = $this->findBatchModel($id);
        //$model->scenario = Batch::SCENARIO_AJAX;
        
        (new Trailable($model))->registerUpdate();
        
        if ($model->load(Yii::$app->request->post())) {
            $model->scenario = Batch::SCENARIO_AJAX;
            $model->manufacturing_date = date('Y-m-d', strtotime($model->manufacturing_date));
            $model->expiry_date = date('Y-m-d', strtotime($model->expiry_date));
            if($model->save())
                echo json_encode([
                    'result' => json_encode($model->attributes),
                    'status' => 'OK'
                ]);
            else
                echo json_encode([
                    'result' => json_encode($model->getErrors ()),
                    'status' => 'ERROR'
                ]);
        } else {
            echo json_encode($model->attributes); 
        }
    }

    /**
     * Deletes an existing Batch model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDeleteBatch()
    {
        $this->checkPermission(['view_edit_form_a']); 
        
        $id = $_POST['id'];
        $this->findBatchModel($id)->delete();
        echo 'OK';
    }
    
    
    protected function findBatchModel($id)
    {
        if (($model = Batch::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    public function actionExpiring()
    {
       $this->checkPermission(['view_edit_form_a']); 
        
       $model = new Batch();
       $productService = new ProductService();
       $ptService = new ProductTypeService();
       
       $productMap = $productService->getProductMap(); $productMap[0] = '--Select Product--'; ksort($productMap);
       $ptMap = $ptService->getProductTypesMap(); $ptMap[0] = '--Select Product Type--'; ksort($ptMap);
       
       $session = Yii::$app->session;
       $roleConditionArray = Product::myRoleACL(); //RBAC
       
        if ($model->load(Yii::$app->request->post())) {
            $filtersArray = array();

            if($_POST['Batch']['product_id'] > 0) $filtersArray['product_id'] = $_POST['Batch']['product_id'];
            if($_POST['Product']['product_type'] > 0) $filtersArray['product_type'] = $_POST['Product']['product_type'];
            if($_POST['Batch']['batch_number'] != '') $filtersArray['batch_number'] = $_POST['Batch']['batch_number'];

            echo json_encode($model->getExpiringBatches(array_merge($filtersArray, $roleConditionArray), true));
            exit;
        }
        
        return $this->render('expiry', [
            'batches' => $model->getExpiringBatches($roleConditionArray),
            'model' => $model,
            'product' => new Product(),
            'productType' => new ProductType(),
            'productMap' => $productMap,
            'ptMap' => $ptMap
        ]);
       
    }
    
    
    public function actionDownloadSample(){
        $file = 'uploads/templates/MAS Registration form - Form A.xlsx';

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($file).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        } else {
            echo 'no file'; 
        }
    }
    
    
    public function actionDownloadBatchesSample(){
        $file = 'uploads/templates/MAS Batch Registration Form - Form C.xlsx';

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($file).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        } else {
            echo 'no file'; 
        }
    }
    
    public function actionImportProductData(){
        $model = new Uploader(['scenario' => Uploader::SCENARIO_EXCEL]);
        $startRow = 19; $providerRow = 13;
        
        $uploadErrors = array(); $parseResponse = array(); 
        
        if (Yii::$app->request->isPost) {
            $model->excelFile = UploadedFile::getInstance($model, 'excelFile');
            if ($model->uploadExcelFile()) {
                /**
                 * HURRAY!
                 * file is uploaded successfully
                 */
                
                $fileName = $model->excelFile->baseName . '.' . $model->excelFile->extension;
                $fileName = 'uploads' . DIRECTORY_SEPARATOR . $fileName;
                
                /**
                * $parseResponse is an array.
                * Will contain errors if any
                * Will be empty of no errors.
                */
                $parseResponse = (new ProductExcelParser($startRow, $providerRow, $fileName))->run();
                
                //Yii::$app->session->setFlash('uploaded', 'UPLOADED');
                
            } else {
                $uploadErrors[] = $model->getErrors();
            }
            
        }

        return $this->render('import-product-data', [
            'model' => $model,
            'uploadErrors' => $uploadErrors,
            'excelErrors' => $parseResponse
        ]);
        
    }
    
    
    public function actionImportBatchData($productId){
        $model = new Uploader(['scenario' => Uploader::SCENARIO_EXCEL]);
        $productModel = Product::findOne($productId);
        $startRow = 20; $productRow = 14; $nrnRow = 15;
        $uploadErrors = array(); $parseResponse = array(); 
        
        if (Yii::$app->request->isPost) {            
            $model->excelFile = UploadedFile::getInstance($model, 'excelFile');
            if ($model->uploadExcelFile()) {
                /**
                 * HURRAY!
                 * file is uploaded successfully
                 */
                
                $fileName = $model->excelFile->baseName . '.' . $model->excelFile->extension;
                $fileName = 'uploads' . DIRECTORY_SEPARATOR . $fileName;
                
                /**
                * $parseResponse is an array.
                * Will contain errors if any
                * Will be empty of no errors.
                */
                $parseResponse = (new BatchExcelParser($startRow, $productRow, $nrnRow, $fileName))->run();
                
            } else {
                $uploadErrors[] = $model->getErrors();
            }
            
        }

        return $this->render('import-batch-data', [
            'model' => $model,
            'productModel' => $productModel,
            'uploadErrors' => $uploadErrors,
            'excelErrors' => $parseResponse
        ]);
        
    }
}
