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
            [['batch_number', 'phone', 'response', 'location_id', 'date_reported', 'pin_4_digits'], 'required'],
            [['response', 'location_id', 'created_by', 'modified_by'], 'integer'],
            [['location_id', 'response'], 'integer', 'min' => 1],
            [['date_reported', 'created_date', 'modified_date'], 'safe'],
            [['phone'], 'string', 'max' => 11],
            [['batch_number'], 'string', 'max' => 12],
            [['phone'], 'match', 'pattern' => '/^[0-9]+$/'],
            [['pin_4_digits'], 'string', 'max' => 4],
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
    
    /**
     * Move to service class later
     */
    public function getUsageRequestsReceived($filtersArray, $asArray=true) {
        $whereArray = array(); $geozoneIds = array(); $location = new Location();
        list($locationIDArray, $tiervalue) = Location::getGeoLevelData(
                array_key_exists('geozones', $filtersArray) ? json_decode($filtersArray['geozones']) : [],
                array_key_exists('states',$filtersArray) ? json_decode($filtersArray['states']) : [],
                array_key_exists('lgas',$filtersArray) ? json_decode($filtersArray['lgas']) : []
        ); 
        
        if(array_key_exists('product_type', $filtersArray) && !empty($filtersArray['product_type'])) 
                $whereArray['product_type'] =  $filtersArray['product_type'];
        
        if(array_key_exists('provider_id', $filtersArray) && !empty($filtersArray['provider_id'])) 
                $whereArray['provider_id'] = $filtersArray['provider_id'];
        
        $fromDate = array_key_exists('from_date', $filtersArray) && !empty($filtersArray['from_date']) ? 
                $filtersArray['from_date'] : 
                $this->getEarliestReported()->date_reported;
        
        $toDate = array_key_exists('to_date', $filtersArray) && !empty($filtersArray['to_date']) ? 
                $filtersArray['to_date'] : 
                $this->getLastReported()->date_reported;
        
        $tierText = $location->getTierText($tiervalue);
        $tierFieldName = $location->getTierFieldName($tiervalue);
        
        return UsageReport::find()
                ->select(['COUNT(*) AS requests', $tierFieldName . ' AS location_name', 'batch.batch_number', 'location_id'])
                ->innerJoinWith(['batch', 'location', 'batch.product.productType', 'batch.product.provider'])
                ->where($whereArray)
                ->andWhere(['in', $tierText, $locationIDArray])
                ->andWhere(['between', 'date_reported', $fromDate, $toDate])
                ->groupBy([$tierText])
                ->asArray($asArray)
                ->all();        
    }
}