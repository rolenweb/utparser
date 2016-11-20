<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\helpers\Url;

use app\models\Catalog;


class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect('login');
        }

        $catalogs = Catalog::find()->with(['childs.childs.childs.childs'])->where(['parent_id' => null])->all();

        $menu = [];
        if (empty($catalogs) === false) {
            foreach ($catalogs as $c1 => $catalog) {
                    
                if (empty($catalog->childs)) {
                    $menu[$c1]['label'] = $catalog->title;    
                    $menu[$c1]['url'] = Url::to(['site/catalog','id' => $catalog->id]);
                }else{
                    foreach ($catalog->childs as $c2 => $catalog2) {
                        $menu[$c1]['label'] = $catalog->title;
                        if (empty($catalog2->childs)) {
                            $menu[$c1]['items'][$c2]['label'] =  $catalog2->title;
                            $menu[$c1]['items'][$c2]['url'] =  Url::to(['site/catalog','id' => $catalog2->id]);
                        }else{
                            foreach ($catalog2->childs as $c3 => $catalog3) {
                                $menu[$c1]['items'][$c2]['label'] =  $catalog2->title;
                                if (empty($catalog3->childs)) {
                                    $menu[$c1]['items'][$c2]['items'][$c3]['label'] =  $catalog3->title;
                                    $menu[$c1]['items'][$c2]['items'][$c3]['url'] =  Url::to(['site/catalog','id' => $catalog3->id]);
                                }else{
                                    $menu[$c1]['items'][$c2]['items'][$c3]['label'] =  $catalog3->title;
                                    foreach ($catalog3->childs as $c4 => $catalog4) {
                                        $menu[$c1]['items'][$c2]['items'][$c3]['items'][$c4]['label'] =  $catalog4->title;
                                        if (empty($catalog4->childs)) {
                                            $menu[$c1]['items'][$c2]['items'][$c3]['items'][$c4]['label'] =  $catalog4->title;
                                            $menu[$c1]['items'][$c2]['items'][$c3]['items'][$c4]['url'] =  Url::to(['site/catalog','id' => $catalog4->id]);
                                        }else{
                                            foreach ($catalog4->childs as $c5 => $catalog5) {
                                                $menu[$c1]['items'][$c2]['items'][$c3]['items'][$c4]['items'][$c5]['label'] =  $catalog5->title;
                                            }
                                        }   
                                    }
                                }
                            }
                        }
                    }
                }
                

            }
        }

        //var_dump($menu);
        //die;

        return $this->render('index',[
            'menu' => $menu,
            ]);    
        
        
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
