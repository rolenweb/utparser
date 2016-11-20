<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\PropertySetting */

$this->title = 'Create Property Setting';
$this->params['breadcrumbs'][] = ['label' => 'Property Settings', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="property-setting-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
