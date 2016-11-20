<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\PropertySetting */

$this->title = 'Update Property Setting: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Property Settings', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="property-setting-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
