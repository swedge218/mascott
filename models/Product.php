<?php

namespace app\models;

use Yii;
use app\models\reports\ProductReport;

/**
 * This is the model class for table "product".
 *
 * @property integer $id
 * @property string $product_name
 * @property integer $product_type
 * @property string $dosage_form
 * @property integer $certificate_holder
 * @property integer $production_country
 * @property string $brand_name
 * @property string $generic_name
 * @property string $nrn
 * @property string $manufacturing_date
 * @property string $expiry_date
 * @property string $batch_number
 * @property integer $provider_id
 * @property integer $deleted
 * @property string $created_date
 * @property integer $created_by
 * @property string $modified_date
 * @property integer $modified_by
 * 
 * @property Batch[] $batches
 * @property Country $productionCountry
 * @property HCR $certificateHolder
 * @property Provider $provider
 * @property ProductType $productType
 * 
 */
class Product extends \yii\db\ActiveRecord
{
    const SCENARIO_PRODUCT = 'product';
    const SCENARIO_EXCEL = 'excel';
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_name', 'product_type', 'dosage_form', 'certificate_holder', 'production_country', 'brand_name', 'generic_name', 'nrn', 'provider_id'], 'required'],
            //[['product_name', 'product_type', 'dosage_form', 'certificate_holder', 'production_country', 'brand_name', 'generic_name', 'nrn'], 'required', 'on' => self::SCENARIO_EXCEL],
            [['product_type', 'certificate_holder', 'production_country', 'provider_id'], 'integer'],
            [['deleted', 'created_by', 'modified_by'], 'integer'],
            [['created_date', 'modified_date', 'created_by', 'modified_by'], 'safe'],
            [['product_name', 'dosage_form', 'generic_name'], 'string', 'max' => 100],
            [['brand_name'], 'string', 'max' => 50],
            [['nrn'], 'string', 'max' => 10],
            [['production_country'], 'exist', 'skipOnError' => true, 'targetClass' => Country::className(), 'targetAttribute' => ['production_country' => 'id']],
            [['certificate_holder'], 'exist', 'skipOnError' => true, 'targetClass' => HCR::className(), 'targetAttribute' => ['certificate_holder' => 'id']],
            [['provider_id'], 'exist', 'skipOnError' => true, 'targetClass' => Provider::className(), 'targetAttribute' => ['provider_id' => 'id']],
            [['product_type'], 'exist', 'skipOnError' => true, 'targetClass' => ProductType::className(), 'targetAttribute' => ['product_type' => 'id']],
        ];
    }
    
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_name' => 'Product Name',
            'product_type' => 'Product Type',
            'dosage_form' => 'Dosage Form',
            'certificate_holder' => 'Certificate Holder',
            'production_country' => 'Production Country',
            'brand_name' => 'Brand Name',
            'generic_name' => 'Generic Name',
            'nrn' => 'NAFDAC Reg. Number',
            'provider_id' => 'MAS Provider',
            'deleted' => 'Deleted',
            'created_date' => 'Created Date',
            'created_by' => 'Created By',
            'modified_date' => 'Modified Date',
            'modified_by' => 'Modified By'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBatches()
    {
        return $this->hasMany(Batch::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductionCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'production_country']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCertificateHolder()
    {
        return $this->hasOne(HCR::className(), ['id' => 'certificate_holder']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProvider()
    {
        return $this->hasOne(Provider::className(), ['id' => 'provider_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductType()
    {
        return $this->hasOne(ProductType::className(), ['id' => 'product_type']);
    }
    
    public static function getProductsAsAssocArray(){
        $roleConditionArray = Product::myRoleACL();
        return Product::find()->where($roleConditionArray)->asArray()->all();
    }
    
    public function getUniqueDosageForms(){
        return Product::find()->select('dosage_form')->distinct()->all();
    }
    
    public function getProductReport($filtersArray){
        $pr = new ProductReport();
        $whereArray = $pr->getReportWhereClause($filtersArray);
        return Product::find()
                ->where($whereArray)
                ->with('productType', 'certificateHolder')
                ->asArray()
                ->all();
    }
    
    
   public static function myRoleACL($userId=0) {
        $userId = Yii::$app->user->id;
        $user = User::find()->with('role')->where(['id' => $userId])->one();
        
        if(strtoupper($user->role->title) ==  'MAS PROVIDER'){
             return ['provider_id' => $user->provider->id];
        }
       
      return [];
   }
   
   
   public function isMyProduct($userId=0) {
       $userId = empty($userId) ? Yii::$app->user->id : $userId;
       $user = User::find()->with(['role', 'provider'])->where(['id' => $userId])->one();
       
       if(strtoupper($user->role->title) ==  'MAS PROVIDER') {
            $productProviderId = Product::findOne($this->id)->provider_id;
            return $productProviderId == $user->provider->id;
       }
       
       return true;
   }
}