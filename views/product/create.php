<?php

use yii\helpers\Html;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $model app\models\Product */

$this->title = 'Products';
$subtitle = 'Create New Product';
//$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-create x-create-padding">

      <p>
          <?= Html::a('<span class="glyphicon glyphicon-chevron-left"></span>Back', 
                  ['index'], ['class'=>'btn btn-mas pull-right amrgintop5']) ?>  
        <h1>
            <?= Html::a(Html::encode($this->title), ['index'], ['class' => '']); ?> <small>(<?= $subtitle; ?>)</small>
        </h1>
    </p>

    <?= $this->render('_form', [
        'model' => $model,
        'uploaderModel' => $uploaderModel,
        'hcrMap' => $hcrMap,
        'countryMap' => $countryMap,
        'providerMap' => $providerMap,
        'ptMap' => $ptMap,
    ]) ?>

</div>
<?php
    $this->registerJs(
            "$('#reg-menu, #reg_product-menu').addClass('active');"
        ,View::POS_LOAD,
        'products-create-menu'
    );
?>