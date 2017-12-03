<?php
use yii\widgets\ActiveForm;
use app\views\helpers\Alert;
use yii\helpers\Html;

$this->title = 'Import Products';
?>

<div class="row content-header">
        <h1>
            <?= $this->title; ?>
            <?= Html::a('<span class="glyphicon glyphicon-chevron-left"></span>Back', 
                    ['index'], ['class'=>'btn btn-mas pull-right margintop5']) ?>
        </h1>
</div>

<?php if(empty($uploadErrors) && empty($excelErrors) && Yii::$app->request->isPost) { ?>
    <div class="row paddingbottom20">
        <div clas="col-md-12">
            <div class=" no-print">
                <div class="callout callout-success margintop10 marginbottom10">
                    <span class="">
                        <i class="glyphicon glyphicon-ok" aria-hidden="true"></i>
                        File data saved successfully
                    </span>
                </div>
            </div>
       </div>
    </div>
<?php } ?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>
        <div class="row">
            <div clas="col-md-12">
                <?= $form->field($model, 'excelFile')->fileInput()->label('Upload Excel File') ?>
            </div>
        </div>
        <div class="row">
            <div clas="col-md-12">
                <?= Html::submitButton('Upload', ['class' => 'btn btn-mas']) ?>
            </div>
        </div>

    <!--<button>Submit</button>-->

<?php ActiveForm::end() ?>

<!--ERRORS DISPLAY-->
<!--UPLOAD ERRORS-->
<?php
    if(!empty($uploadErrors)){
?>
        <div class="row bg-danger margintop20">
            <div class="col-md-12">
                Errors occurred while uploading the file. Please check that your file is right size and try again<br/>
<?php                
        foreach($uploadErrors as $error){
?>
                <p>$error</p>
<?php
        }
?>
            </div>
        </div>
<?php
    }
?>        


<!--EXCEL ERRORS-->
<?php
    if(!empty($excelErrors)){
?>
        <div class="row bg-danger margintop20 paddingbottom20">
            <div class="col-md-12">
                <h4 class="marginbottom10 bold">
                    Errors found on file. Please fix the following rows and try again.
                </h4>
<?php                
        foreach($excelErrors as $rowNumber=>$errors){
?>
                <div class="borderallccc bgeee margintop10 padding5 paddingleft10">
                    <h6 class="bold">Row: <?= $rowNumber; ?></h6>
<?php
            foreach($errors as $error){ 
?>
                <p><?= is_array($error) ? 
                        str_replace(['[',']', '"'],'',json_encode($error)) : 
                        $error; ?>
                </p>
<?php       
            }
?>
                </div>
<?php                
        }
?>
                
            </div>
        </div>
<?php
    }
?>      