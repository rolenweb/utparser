<?php
use yii\helpers\Html;
use yii\bootstrap\Dropdown;
use yii\bootstrap\Nav;
/* @var $this yii\web\View */

$this->title = 'Parser Ulmart';


echo Nav::widget([
    'items' => $menu,
    'options' => ['class' =>'nav-pills'], // set this to nav-tab to get tab-styled navigation
]);


?>

