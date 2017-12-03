<?php

namespace app\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;
use app\models\UsageReport;
use app\models\service\UsageReportService;
use app\models\Location;
use app\models\Product;
use app\controllers\services\LocationService;
use app\controllers\services\ProviderService;
use app\controllers\services\ProductTypeService;
use app\controllers\services\ProductService;
use app\controllers\services\AlertsService;
use app\models\service\RequestExcelParser;
use app\models\utils\Trailable;
use app\models\Permission;
use app\models\utils\Uploader;


/**
 * UsageReportController implements the CRUD actions for UsageReport model.
 */
class UsageReportController extends BaseController
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
     * Lists all UsageReport models.
     * @return mixed
     */
    public function actionIndex()
    {        
        $this->checkPermission(['view_edit_form_b']);
        
        $session = Yii::$app->session;
        $roleConditionArray = UsageReport::myRoleACL();
        $reports = UsageReport::find()
                ->innerJoinWith(['batch.product'])
                ->where($roleConditionArray)
                ->all();    

        return $this->render('index', [
            'reports' => $reports,
        ]);
    }

    /**
     * Creates a new UsageReport model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        //ini_set('display_errors', 'On');
        $this->checkPermission(['view_edit_form_b']);
        
        $model = new UsageReport();
        $locationService = new LocationService();
        $locationsHieJson = $locationService->getLocationsHierachyAsJson();
        
        $productService = new ProductService();
        $productMap = $productService->getProductMap(); $productMap[0] = '--Select Product--'; ksort($productMap);
        
        if ($model->load(Yii::$app->request->post())) {
            $model->location_id = $locationService->getSelectedLocationId(
                    $_POST['UsageReport']['geozone_id'],
                    $_POST['UsageReport']['state_id'],
                    $_POST['UsageReport']['lga_id']
            );
            (new Trailable($model))->registerInsert(); //audit trail
            if($model->save()){
                //send email if response is NOT Genuine i.e. fake or invalid
                if($model->getResponseAsText() != UsageReport::GENUINE){
                    $permission = Permission::find()->where(['alias'=>'resolution_reminder'])->one();
                    $permissionUsers = $permission->getMyUsers();
                    (new AlertsService())->sendResolutionRequestEmail($permissionUsers, $model);
                }
                
                Yii::$app->session->setFlash('saved', 'CREATED');
                return $this->redirect(['update', 'id' => $model->id, 'new' => true]);
            }
        }
        
        return $this->render('create', [
                'model' => $model,
                'lh' => $locationsHieJson,
                'parentsJson' => '0',
                'productMap' => $productMap,
                'product' => new Product()
        ]);
        
    }

    /**
     * Updates an existing UsageReport model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id, $new = false)
    {
        $this->checkPermission(['view_edit_form_b']);
        
        $model = $this->findModel($id);
        $session = Yii::$app->session;
        if(!$model->isMyReport())
                throw new \yii\web\ForbiddenHttpException();
        
        $locationService = new LocationService();
        $productService = new ProductService();
        $locationsHieJson = $locationService->getLocationsHierachyAsJson();
        $productMap = $productService->getProductMap(); $productMap[0] = '--Select Product--'; ksort($productMap);
        
        $new == true ?  Yii::$app->session->setFlash('saved', Yii::$app->session->getFlash('saved')) : '';
        
        if ($model->load(Yii::$app->request->post())) {
            $model->location_id = $locationService->getSelectedLocationId(
                    $_POST['UsageReport']['geozone_id'],
                    $_POST['UsageReport']['state_id'],
                    $_POST['UsageReport']['lga_id']
            );
            (new Trailable($model))->registerUpdate(); //audit trail
            if ($model->save()) {
                Yii::$app->session->setFlash('saved', 'UPDATED');
            }
        } 
        
        //get parents array
        $parentsJson = $locationService->traceSelectedLocationParents($model->location_id);
        return $this->render('update', [
            'model' => $model,
            'lh' => $locationsHieJson,
            'parentsJson' => $parentsJson,
            'productMap' => $productMap,
            'product' => new Product()
        ]);
        
    }

    /**
     * Deletes an existing UsageReport model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->checkPermission(['view_edit_form_b']);
        
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the UsageReport model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return UsageReport the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = UsageReport::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    
    public function actionRequestsReceived(){
        $filtersArray = array();
        $reportService = new UsageReportService();
        
        $ptService = new ProductTypeService();
        $providerService = new ProviderService();
        $locationService = new LocationService();
       
        $ptMap = $ptService->getProductTypesMap(); $ptMap[0] = '--Select Product Type--'; ksort($ptMap);
        $providerMap = $providerService->getProviderMap(); $providerMap[0] = '--Select Provider--'; ksort($providerMap);
                
        if($post = Yii::$app->request->post()){
            
            Yii::$app->response->format = Response::FORMAT_JSON;     
            $filteredReports = $reportService->getUsageRequestsReceived($post, true);
            return $filteredReports;
        }
        
        $filteredReports = $reportService->getUsageRequestsReceived($filtersArray, true);
        
        return $this->render('requests_received', [
            'product' => new Product(),
            'model' => new UsageReport(),
            'location' => new Location(),
            'ptMap' => $ptMap,
            'providerMap' => $providerMap,
            'lh' => $locationService->getLocationsHierachyAsJson(),
            'usageData' => json_encode($filteredReports)
        ]);
    }
    
    
    
    public function actionActivatedUsed(){
        $filtersArray = array();
        $reportService = new UsageReportService();
        
        $productService = new ProductService();
        $ptService = new ProductTypeService();
        $providerService = new ProviderService();
        
        $ptMap = $ptService->getProductTypesMap(); $ptMap[0] = '--Select Product Type--'; ksort($ptMap);
        $productMap = $productService->getProductMap(); $productMap[0] = '--Select Product--'; ksort($productMap);
        $providerMap = $providerService->getProviderMap(); $providerMap[0] = '--Select Provider--'; ksort($providerMap);
               
        if($post = Yii::$app->request->post()){
            Yii::$app->response->format = Response::FORMAT_JSON;     
            $filteredReports = $reportService->getActivatedProductsUsed($post, true);
            return $filteredReports;
        }
        
        $filteredReports = $reportService->getActivatedProductsUsed($filtersArray, true);
        
        return $this->render('activated-used', [
            'product' => new Product(),
            'model' => new UsageReport(),
            'ptMap' => $ptMap,
            'providerMap' => $providerMap,
            'productMap' => $productMap,
            'usageData' => json_encode($filteredReports)
        ]);
    }   
    
    
    public function actionImportUsageData(){
        $model = new Uploader(['scenario' => Uploader::SCENARIO_EXCEL]);
        $startRow = 27; $productRow = 23; $providerRow = 22;
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
                $parseResponse = (new RequestExcelParser($startRow, $productRow, $providerRow, $fileName))->run();
                
                //Yii::$app->session->setFlash('uploaded', 'UPLOADED');
                
            } else {
                $uploadErrors[] = $model->getErrors();
            }
            
        }

        return $this->render('import-usage-data', [
            'model' => $model,
            'uploadErrors' => $uploadErrors,
            'excelErrors' => $parseResponse
        ]);
        
    }
    
    public function actionDownloadSample(){        
        $file = 'uploads/templates/MAS Request Report- Form B.xlsx';
  
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
}