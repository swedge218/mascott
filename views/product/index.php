<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\View;
use app\views\helpers\ActionButton;
use app\models\ProductType;
use app\models\HCR;

$this->title = 'Products';
//$this->params['breadcrumbs'][] = $this->title;
?>

<section class="content-header">
    <h1><?= $this->title; ?></h1>
</section>

<div class="row">
    <div class="col-md-12">
        <p class="text-right">
            <?= Html::a('Create Product', ['create'], ['class' => 'btn btn-success']) ?>
        </p>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <table id="productsList" class="table table-striped table-bordered dataTable" role="grid" style="width: 100%;">
            <thead>
                <tr>
                    <th class="sorting">SN</th>
                    <th class="sorting">Product Name</th>
                    <th class="sorting">Product Type</th>
                    <th class="sorting">Certificate Holder</th>
                    <th class="sorting">Brand Name</th>
                    <th class="sorting">Generic Name</th>
                    <th class="sorting">NAFDAC Reg. Number</th>                    
                    <th>Actions</th>
                </tr>
            </thead>
           
            
                <tbody>
        <?php
            //echo is_array($roles) ? 'array' : 'scalar'; exit;
            $count=0;
            foreach($products as $product){
        ?>
                    <tr>
                        <td><?php echo ++$count; ?></td>
                        <td><?php echo $product->product_name; ?></td>
                        <td><?php echo ProductType::findOne($product->product_type)->title; ?></td>
                        <td><?php echo HCR::findOne($product->certificate_holder)->name;?></td>
                        <td><?php echo $product->brand_name; ?></td>
                        <td><?php echo $product->generic_name; ?></td>
                        <td><?php echo $product->nrn; ?></td>
                        <td>
                            <span>
                                <?php echo ActionButton::updateButton('product', $product->id); ?>
                            </span>
                            <span class="marginleft10">
                                <?php echo ActionButton::deleteButton('product', $product->id); ?>
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
    $this->registerJs("
            $('#productsList').DataTable();",
        View::POS_READY,
        'product-list-data-table'
    );
    
?>

<?php
    $this->registerJs(
            "$('#reg-menu, #reg_product-menu').addClass('active');"
        ,View::POS_LOAD,
        'products-menu'
    );
?>