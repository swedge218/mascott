<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;
use app\views\helpers\Alert;
use yii\web\View;
    
/* @var $this yii\web\View */
/* @var $model app\models\Product */
/* @var $form yii\widgets\ActiveForm */
/* @var $batchModel app\models\Batch */
/* @var $batch app\models\Batch */
?>

    <div class="row batch-form">       
        <div class="col-md-3">
            <?= $form->field($batchModel, 'batch_number')->textInput(['maxlength' => true]) ?>
        </div>        
        <div class="col-md-3">
            <?= $form->field($batchModel, 'manufacturing_date')->widget(DatePicker::classname(), [
                'language' => 'en',
                'dateFormat' => 'dd-MM-yyyy',
            ])->textInput(['placeholder' => 'dd-MM-yyyy']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($batchModel, 'expiry_date')->widget(DatePicker::classname(), [
                'language' => 'en',
                'dateFormat' => 'dd-MM-yyyy',
            ])->textInput(['placeholder' => 'dd-MM-yyyy']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($batchModel, 'quantity')->textInput() ?>
            <?= $form->field($batchModel, 'product_id')->hiddenInput(['value'=>$model->id])->label(false) ?>
            <?= $form->field($batchModel, 'id')->hiddenInput()->label(false) ?>
        </div>
    </div>

    
    <div class="row batch-form">               
            <div class="col-md-3 col-md-offset-3">
                <?= $form->field($batchModel, 'mas_code_assigned')->dropDownList(
                        ['0'=>'-- Select --', '1'=>'No', '2'=>'Yes'], 
                        array('options' => array($batchModel->mas_code_assigned=>array('selected'=>true)))
                     ) 
                ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($batchModel, 'mas_code_status')->dropDownList(
                        ['0'=>'-- Select --', '1'=>'Not Activated', '2'=>'Activated'],
                        array('options' => array($batchModel->mas_code_status=>array('selected'=>true)))
                     ) 
                ?>
            </div>
    </div>


    <div class="row batch-form" style="min-height: 200px">       
        <div class="col-md-3 col-md-offset-4" style="margin-top: 25px;">
            <?= Html::button('Add', ['id'=>'add-batch','class'=>'btn btn-mas']) ?>
            <?= Html::button('Clear', ['id'=>'clear-form','class'=>'btn btn-default marginleft10']) ?>
        </div>
    
        <div class="col-md-2" style="margin-top: 25px;">
             <div class="btn-group">
                <button type="button" class="btn btn-mas dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                   Batches Data File <span class="caret"></span>
                 </button>
                 <ul class="dropdown-menu">
                   <li><?= Html::a('<i class="fa fa-download" aria-hidden="true"></i> '
                                  . 'Download Sample Template', 
                                  ['download-batches-sample'], ['class' => '']) ?>
                   </li>
                   <li><?= Html::a('<i class="fa fa-download" aria-hidden="true"></i> '
                                     . 'Upload Batches Data File', 
                                     ['import-batch-data',  'productId' => $model->id], ['class' => '']) ?>
                   </li>
                 </ul>
            </div>
         </div>
        
        <div class="col-md-12">
            <div class="row">
                <div id="loading-div" class="col-md-12 loading-gif-wrapper text-center margintop20 hidden">
                    <?= Html::img('@web/images/loading.gif', ['alt' => 'Loading Image', 'class'=>'']) ?>
                    <br/>
                    <div class="">Loading...</div>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <table id="batchList" class="table table-striped table-bordered dataTable" role="grid" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="">SN</th>
                        <th class="">Batch Number</th>
                        <th class="">Manufacturing Date</th>
                        <th class="">Expiry Date</th>
                        <th class="">Quantity</th>
                        <th class="">MAS Code Assigned</th>
                        <th class="">MAS Code Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th class="">SN</th>
                        <th class="">Batch Number</th>
                        <th class="">Manufacturing Date</th>
                        <th class="">Expiry Date</th>
                        <th class="">Quantity</th>
                        <th class="">MAS Code Assigned</th>
                        <th class="">MAS Code Status</th>
                        <th>Actions</th>
                    </tr>
                </tfoot>
              
                    <tbody>
            <?php
                //echo is_array($roles) ? 'array' : 'scalar'; exit;
                $count=0;
                foreach($batches as $batch){
            ?>
                        <tr>
                            <td><?php echo ++$count; ?></td>
                            <td><?php echo $batch->batch_number; ?></td>
                            <td><?php echo date('d-m-Y',  strtotime($batch->manufacturing_date)) ?></td>
                            <td><?php echo date('d-m-Y',  strtotime($batch->expiry_date)) ?></td>
                            <td><?php echo $batch->quantity ?></td>
                            <td><?php echo $batch->mas_code_assigned == 1 ? 'No' : 'Yes'; ?></td>
                            <td><?php echo $batch->mas_code_status == 1 ? 'Not Activated' : 'Activated'; ?></td>
                            <td>
                                <span>
                                    <a id="u<?= $batch->id; ?>" href="" onclick="return updateBatch(<?= $batch->id; ?>)" >
                                        <i class="glyphicon glyphicon-pencil" aria-hidden="true"></i>
                                    </a>
                                </span>
                                <span class="marginleft10">
                                    <a id="d<?= $batch->id; ?>" href="" onclick="return deleteBatch(<?= $batch->id; ?>)" >
                                        <i class="glyphicon glyphicon-trash" aria-hidden="true"></i>
                                    </a>
                                </span>
                            </td>
                        </tr>
            <?php
                }
            ?>
                    </tbody>
            </table>
        </div>
    </div>


<?php
    $this->registerJs(
            "$('#batchList').DataTable({
                'columnDefs': [
                    {'className': 'text-center', 'targets': [5,6]}
                  ]
              });",
        View::POS_READY,
        'batch-list-data-table'
    );
    
    
    
    $this->registerJsFile(
        '@web/js/batch-ops.js',
        ['depends' => [\yii\web\JqueryAsset::className()]]
    );
    
?>