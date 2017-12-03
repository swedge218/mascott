<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "batch".
 *
 * @property integer $id
 * @property string $batch_number
 * @property string $manufacturing_date
 * @property string $expiry_date
 * @property string $quantity
 * @property integer $mas_code_assigned
 * @property integer $mas_code_status
 * @property integer $product_id
 * @property integer $created_by
 * @property string $created_date
 * @property integer $modified_by
 * @property string $modified_date
 *
 * @property Product $product
 */
class Batch extends \yii\db\ActiveRecord
{
    const SCENARIO_AJAX = 'ajax';
    const SCENARIO_PRODUCT2 = 'product2';
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'batch';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //[['batch_number', 'manufacturing_date', 'expiry_date', 'quantity', 'product_id'], 'safe'],
            [['batch_number', 'manufacturing_date', 'expiry_date', 'quantity', 'mas_code_assigned', 'mas_code_status' , 'product_id'], 'required', 'on' => self::SCENARIO_AJAX],
            [['manufacturing_date', 'expiry_date'], 'date', 'format'=>'php:Y-m-d'],
            [['created_date', 'modified_date'], 'safe'],
            [['quantity', 'mas_code_assigned', 'mas_code_status', 'product_id', 'created_by', 'modified_by'], 'integer'],
            [['batch_number'], 'string', 'max' => 12],
            [['batch_number'], 'unique', 'message'=>'This batch number already exists'],
            [['quantity'], 'match', 'pattern' => '/^[0-9]+$/'],
            [['quantity'], 'integer', 'min' => 1],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'batch_number' => 'Batch Number',
            'manufacturing_date' => 'Manufacturing Date',
            'expiry_date' => 'Expiry Date',
            'quantity' => 'Quantity',
            'mas_code_assigned' => 'MAS Code Assigned', 
            'mas_code_status' => 'MAS Code Status',
            'product_id' => 'Product ID',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
            'modified_by' => 'Modified By',
            'modified_date' => 'Modified Date',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }
    
    /**
     * Get the expiring batches in the system
     * A batch is in this category if it 
     */   
    public function getExpiringBatches($filtersArray, $asArray=false){
        
        $date = date('Y-m-d');
        return Batch::find()
                ->innerJoinWith(['product.productType','product.certificateHolder'])
                ->where("DATEDIFF(expiry_date, '$date') <= 90")
                ->andWhere($filtersArray)
                ->asArray($asArray)
                ->all();
    }
    
    /**
     * Returns for all products if products list array is empty
     * 
     * @param type $productsList
     * @return type array
     */
    public function getBatchesCountByproduct(){
        $productIDs = Product::find()->select('id')->asArray()->all();
        $productIDsArray = array();
        foreach($productIDs as $productID)
            $productIDsArray[] = $productID['id'];
        
        $roleConditionArray = self::myRoleACL();
        return Batch::find()
                ->select(['count(batch.id) AS batchCount', 'product_name', 'product_id'])
                ->innerJoinWith('product')
                ->where(['in', 'product.id', $productIDsArray])
                ->andWhere($roleConditionArray)
                ->asArray()
                ->indexBy('product_name')
                ->orderBy('product_name')
                ->groupBy('product_id')
                ->all();
    }
    
    /**
     * Check if a matching record can be found.
     * for a product name and type
     */
    public function hasMatch($pname, $batchnum){
        $obj = Batch::find()
                ->innerJoinWith(['product'])
                ->where([
                    'UPPER(product_name)' => strtoupper($pname) . '',
                    'UPPER(batch_number)' => strtoupper($batchnum)
                ])->one();        
        
        //echo is_object($obj) ? 'OBJECT' : 'SCALAR'; exit;

        return is_object($obj);
    }
    
    
    public static function myRoleACL() {
        $userId = Yii::$app->user->id;
       $user = User::find()->with(['role', 'provider'])->where(['id' => $userId])->one();
       
        if(strtoupper($user->role->title) ==  'MAS PROVIDER'){
             return ['provider_id' => $user->provider->id];
        }

       return [];
   }
   
   
   public function isMyBatch($roleTitle, $providerId) {
       $userId = Yii::$app->user->id;
       $user = User::find()->with(['role', 'provider'])->where(['id' => $userId])->one();
       
       if(strtoupper($user->role->title) ==  'MAS PROVIDER') {
            $productProviderId = Product::findOne($this->id)->provider_id;
            return $productProviderId == $user->provider->id;
       }
       
       return true;
   }
}