<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;
use app\views\helpers\Alert;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $model app\models\Product */
/* @var $form yii\widgets\ActiveForm */
?>

<?= Yii::$app->session->hasFlash('saved') ? Alert::showSuccess() : ''; ?>

<div class="product-form x-form-padding">

    <?php $form = ActiveForm::begin([
        'id'=>'update-form'
    ]); ?>
    <div class="row">
       <div class="col-md-4">
            <?= $form->field($model, 'provider_id')->dropDownList(
                   $providerMap, 
                   array('options' => array($model->provider_id=>array('selected'=>true)))
                )
           ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'nrn')->textInput(['maxlength' => true]) ?>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'product_name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'product_type')->dropDownList(
                    $ptMap, 
                    array('options' => array($model->product_type=>array('selected'=>true)))
                 ) 
            ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'dosage_form')->textInput(['maxlength' => true]) ?>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'certificate_holder')->dropDownList(
                    $hcrMap, 
                    array('options' => array($model->certificate_holder=>array('selected'=>true)))
                 ) 
            ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'production_country')->dropDownList(
                   $countryMap, 
                   array('options' => array($model->production_country=>array('selected'=>true)))
                ) 
           ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'brand_name')->textInput(['maxlength' => true]) ?>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'generic_name')->textInput(['maxlength' => true]) ?>
        </div>
    </div>
    <br>    
    <?php if($model->isNewRecord) { ?>
        <div class="text-right">
            <?= Html::submitButton('Create', ['class' => 'btn btn-mas']) ?>
        </div>
    <?php } else { ?>
        <hr>
        <?php require_once 'batch_form.php'; ?>
        <div class="text-right">
            <?= Html::submitButton('Update', ['class' => 'btn btn-mas']) ?>
        </div>
    <?php } ?>

    <?php ActiveForm::end(); ?>
</div>