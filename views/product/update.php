<?php

use yii\helpers\Html;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $model app\models\Product */

$this->title = 'Products';
$subtitle = 'Edit Product: ' . $model->product_name;
//$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = 'Update';
?>

<div class="product-update">

    <p>
        <h1>
            <?= Html::a(Html::encode($this->title), ['index'], ['class' => '']); ?> <small>(<?= $subtitle; ?>)</small>
            <?= Html::a('Create New Product', ['create'], ['class' => 'btn btn-mas pull-right margintop5 marginleft5']); ?>
            <?= Html::a('<span class="glyphicon glyphicon-chevron-left"></span>Back', 
                    ['index'], ['class'=>'btn btn-mas pull-right margintop5']) ?>
        </h1>
    </p>

    <?= $this->render('_form', [
        'model' => $model,
        'uploaderModel' => $uploaderModel,
        'hcrMap' => $hcrMap,
        'countryMap' => $countryMap,
        'providerMap' => $providerMap,
        'ptMap' => $ptMap,
        'batchModel' => $batchModel,
        'batches' => $batches
//        'selectedHolderId' => $selectedHolderId,
//        'selectedProviderId' => $selectedProviderId,
//        'selectedCountryId' => $selectedCountryId,
//        'selectedPTId' => $selectedPTId,
//        'selectedMASCodeAssgn' => $selectedMASCodeAssgn,
//        'selectedMASCodeStatus' => $selectedMASCodeStatus
    ]) ?>

</div>


<?php
    $this->registerJs(
            "$('#reg-menu, #reg_product-menu').addClass('active');"
        ,View::POS_LOAD,
        'products-update-menu'
    );
?>