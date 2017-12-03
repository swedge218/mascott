<?php
use yii\widgets\ActiveForm;
use app\views\helpers\Alert;
use yii\helpers\Html;
use yii\web\View;

//$this->title = 'Import ' . $productModel->product_name . ' Batches';
?>

<div class="row content-header">
    <h1>
        Import <em><?= $productModel->product_name ?></em> Batches
        <?= Html::a('<span class="glyphicon glyphicon-chevron-left"></span>Back', 
                    ['update', 'id' => $productModel->id], ['class'=>'btn btn-mas pull-right margintop5']) ?>
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

<?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'],
        //'action' => 'import-batch-data',
        'id' => 'import-batch-form',
        
    ]) 
?>
        <div class="row">
            <div clas="col-md-6 text-center">
                <?= $form->field($model, 'excelFile')
                        ->fileInput()
                        ->label('Upload Excel File') ?>
            </div>
        </div>
        <div class="row">
            <div clas="col-md-12">
                <?= Html::submitButton('Upload', ['class' => 'btn btn-mas']) ?>
                <!--<? Html::button('Upload', ['id'=>'uploadButton', 'class' => 'btn btn-mas']); ?>-->
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
            foreach($errors as $k=>$error){ 
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


<?php
    $this->registerJs("
              
            $('#uploadButton').on('click', function(){
                var formData = new FormData();
                fileInputElement = document.getElementById('uploader-excelfile');
                //console.log(fileInputElement.files[0]); return;
                formData.append('excelFile', fileInputElement.files[0]);
                //formData.append('batchTest', 'tested');
                
                //var request = new XMLHttpRequest();
                //request.open('POST', 'import-batch-data', true);
                //var result = request.send({energy: 'water'});
                //console.log(result); return;
                
                //var params = 'good=best&bad=worst';
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                       // Typical action to be performed when the document is ready:
                       console.log(xhttp.responseText);
                    }
                };
                xhttp.open('POST', 'import-batch-data', true);
                //xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhttp.setRequestHeader('Content-type', 'multipart/form-data');
                xhttp.send(formData);
                return;
    
                $('#loading-div2 span').removeClass('hidden');
                $.ajax({
                    url: 'import-batch-data',
                    type: 'POST',
                    data: {excelFile: fileInputElement.files[0].serialize()},
                    success: function(jsonResponse){
                        console.log('jsonResponse: '+JSON.stringify(jsonResponse));
                        //updateTable(jsonResponse);
                        $('#loading-div2 span').addClass('hidden');
                    },
                    error: function(jqXHR, textStatus, errorThrown ){
                        console.log('jqXHR: '+JSON.stringify(jqXHR));
                        console.log('textStatus: '+textStatus);
                        console.log('errorThrown: '+errorThrown);
                    }
                });
                return false;
            });
        ",
        View::POS_READY,
        'batches-upload-form'
    );    
?>

