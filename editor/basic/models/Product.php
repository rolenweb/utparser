<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "product".
 *
 * @property integer $id
 * @property string $url
 * @property integer $art
 * @property string $title
 * @property string $status
 * @property integer $catalog_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Product extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['url', 'status'], 'string'],
            [['art', 'catalog_id', 'created_at', 'updated_at'], 'integer'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'url' => 'Url',
            'art' => 'Art',
            'title' => 'Title',
            'status' => 'Status',
            'catalog_id' => 'Catalog ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
