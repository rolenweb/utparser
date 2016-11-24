<?php

namespace app\controllers;

use Yii;
use app\models\Product;
use app\models\ProductSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Html;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Product models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Product model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $product = $this->findModel($id);

        $properties = $product->properties;
        $prop_grid_view = [];
        $attributes = [
            'id',
            'url:url',
            'art',
            'title',
            'status',
            [
                'label' => 'Catalog',
                'value' => $product->catalog->title,
            ],
                        
            
            
        ];
        if (empty($properties) === false) {
            foreach ($properties as $property) {
                switch ($property->propertyName->title) {
                    case 'description':
                        array_push($attributes,
                            [
                                'label' => 'Description',
                                'value' => $property->value,
                            ]);
                        break;

                    case 'price':
                        array_push($attributes,
                            [
                                'label' => 'Price',
                                'value' => $property->value,
                            ]);
                        break;

                    case 'currency':
                        array_push($attributes,
                            [
                                'label' => 'Currency',
                                'value' => $property->value,
                            ]);
                        break;

                    case 'bigimage':
                        array_push($attributes,
                            [
                                'label' => 'Big image',
                                'format' => 'raw',
                                'value' => Html::img($property->value),
                            ]);
                        break;

                    case 'otherimage':
                        array_push($attributes,
                            [
                                'label' => 'Other image',
                                'format' => 'raw',
                                'value' => Html::img($property->value),
                            ]);
                        break;

                    case 'smallimage':
                        array_push($attributes,
                            [
                                'label' => 'Small image',
                                'format' => 'raw',
                                'value' => Html::img($property->value),
                            ]);
                        break;
                    
                    default:
                        # code...
                        break;
                }
            }
        }

        $full_property = $product->fullProperty();
        if (empty($full_property) === false) {
            foreach ($full_property as $item) {
                array_push($attributes,
                            [
                                'label' => $item['name'],
                                'value' => $item['value'],
                            ]);
            }
        }else{
            $short_property = $product->shortProperty();
            if (empty($short_property) === false) {
                foreach ($short_property as $item) {
                    array_push($attributes,
                                [
                                    'label' => $item['name'],
                                    'value' => $item['value'],
                                ]);
                }
            }    
        }

        

        

        array_push($attributes,[
                'label' => 'Created',
                'value' => date("Y-m-d H:i:s",$product->created_at),
            ],
            [
                'label' => 'Update',
                'value' => date("Y-m-d H:i:s",$product->updated_at),
            ]);
        //var_dump($attributes);
        //die;
        return $this->render('view', [
            'model' => $product,
            'attributes' => $attributes,
        ]);
    }

    /**
     * Creates a new Product model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Product();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Product model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Product model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Product model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Product the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Product::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
