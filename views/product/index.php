<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\View;
use app\views\helpers\ActionButton;
use app\models\ProductType;
use app\models\HCR;

$this->title = 'Products';
//$this->params['breadcrumbs'][] = $this->title;

$permissions = Yii::$app->session['user_permissions'];
?>

<section class="content-header">
    <h1><?= $this->title; ?></h1>
</section>


<div class="row marginbottom15">
        <?php if(in_array('view_edit_form_a', $permissions)) { ?>
            <div class="col-md-12">
                <button type="button" class="btn btn-mas dropdown-toggle pull-right" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  Actions <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                  <li><?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i> '
                                    . 'Create New Product', 
                                    ['create'], ['class' => '']) ?>
                  </li>
                   
                  <li role="separator" class="divider"></li>
                   <li class="dropdown-header">REGISTRATION TEMPLATE</li>
                   <li role="separator" class="divider"></li>
                   
                  <li><?= Html::a('<i class="fa fa-download" aria-hidden="true"></i> '
                                    . 'Download Sample Template', 
                                    ['download-sample'], ['class' => '']) ?>
                  </li>
                  <li><?= Html::a('<i class="fa fa-download" aria-hidden="true"></i> '
                                    . 'Upload Product Data File', 
                                    ['import-product-data'], ['class' => '']) ?>
                  </li>
                </ul>
            </div>

            
            
        <?php }  ?>    
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
                    <?php if(in_array('view_edit_form_a', $permissions)) { ?>
                    <th>Actions</th>
                    <?php } ?>
                </tr>
            </thead>
           <tfoot>
                <tr>
                    <th class="sorting">SN</th>
                    <th class="sorting">Product Name</th>
                    <th class="sorting">Product Type</th>
                    <th class="sorting">Certificate Holder</th>
                    <th class="sorting">Brand Name</th>
                    <th class="sorting">Generic Name</th>
                    <th class="sorting">NAFDAC Reg. Number</th>    
                    <?php if(in_array('view_edit_form_a', $permissions)) { ?>
                    <th>Actions</th>
                    <?php } ?>
                </tr>
            </tfoot>
            
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
                        <?php if(in_array('view_edit_form_a', $permissions)) { ?>
                        <td class="text-center">
                            <span>
                                <?php echo ActionButton::updateButton('product', $product->id); ?>
                            </span>
                            <?php if(in_array('delete_product', $permissions)) { ?>
                            <span class="marginleft10">
                                <?php echo ActionButton::deleteButton('product', $product->id); ?>
                            </span>
                            <?php }  ?>
                        </td>
                        <?php } ?>
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