<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "usage_report".
 *
 * @property string $id
 * @property integer $batch_number
 * @property string $mas_code
 * @property string $phone
 * @property string $response
 * @property integer $location_id
 * @property string $date_reported
 * @property string $pin_4_digits
 * @property string $created_date
 * @property integer $created_by
 * @property string $modified_date
 * @property integer $modified_by
 *
 * @property Complaint $complaint
 * @property Location $location
 * @property Batch $batch
 */
class UsageReport extends \yii\db\ActiveRecord
{
    public $geozone_id;
    public $state_id;
    public $lga_id;
    
    const GENUINE = 'GENUINE';
    const FAKE = 'FAKE';
    const INVALID = 'INVALID';
    
    const GENUINE_DBVALUE = 1;
    const FAKE_DBVALUE = 2;
    const INVALID_DBVALUE = 3;
                
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'usage_report';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['batch_number', 'phone', 'response', 'date_reported', 'pin_4_digits'], 'required'],
            [['response', 'location_id', 'created_by', 'modified_by'], 'integer'],
            [['response'], 'integer', 'min' => 1],
            [['date_reported', 'created_date', 'modified_date'], 'safe'],
            [['phone'], 'string', 'max' => 11],
            [['batch_number'], 'string', 'max' => 12],
            [['phone'], 'match', 'pattern' => '/^[0-9]+$/'],
            [['phone'], 'string', 'min' => 11],
            [['pin_4_digits'], 'string', 'max' => 4, 'min' => 4],
            [['location_id'], 'exist', 'skipOnError' => true, 'targetClass' => Location::className(), 'targetAttribute' => ['location_id' => 'id']],
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
            'phone' => 'Phone',
            'response' => 'Response',
            'location_id' => 'Location ID',
            'date_reported' => 'Date Reported',
            'pin_4_digits' => 'Pin Last 4 Digits',
            'created_date' => 'Created Date',
            'created_by' => 'Created By',
            'modified_date' => 'Modified Date',
            'modified_by' => 'Modified By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComplaint()
    {
        return $this->hasOne(Complaint::className(), ['report_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLocation()
    {
        unset($this->complaint);
        return $this->hasOne(Location::className(), ['id' => 'location_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBatch()
    {
        return $this->hasOne(Batch::className(), ['batch_number' => 'batch_number']);
    }
    
    
    /**
     * get all reports that are fake
     * @return array of reports 
     */
    public static function getFakeReports()
    {
        return UsageReport::find()->where(['response' => 2])->all();
    }
    
    /**
     * get all reports that are fake or invalid
     * @return array of reports 
     */
    public static function getFalseReports()
    {
        return UsageReport::find()->where(['>', 'response', 1])->all();
    }
    
    public function getResponseAsText(){
        switch($this->response){
            case 1: return self::GENUINE;
            case 2: return self::FAKE;
            case 3: return self::INVALID;
        }
    }
    
    public function getComplaintResultAsText(){
        return is_object($this->complaint) ? $this->complaint->getResultAsText() : Complaint::UNRESOLVED;
    }
            
    public function getEarliestReported(){
        return UsageReport::find()->orderBy(['date_reported'=>SORT_ASC])->limit(1)->one();
    }
    
    public function getLastReported(){
        return UsageReport::find()->orderBy(['date_reported'=>SORT_DESC])->limit(1)->one();
    }
    
    
    public static function myRoleACL() {
       $userId = Yii::$app->user->id;
       $user = User::find()->with(['role', 'provider'])->where(['id' => $userId])->one();
        
       if(strtoupper($user->role->title) ==  'MAS PROVIDER'){
            return ['provider_id' => $user->provider->id];
       }
       
       return [];
   }
    
   
    public function isMyReport() {
       $userId = Yii::$app->user->id;
       $user = User::find()->with(['role', 'provider'])->where(['id' => $userId])->one();
       
       if(strtoupper($user->role->title) ==  'MAS PROVIDER') {
           $report = UsageReport::find()->with('batch.product.provider')->where(['id'=> $this->id])->one();
            $reportProductProviderId = $report->batch->product->provider->id;
            return $reportProductProviderId == $user->provider->id;
       }
       
       return true;
   }
}