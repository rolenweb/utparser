<?php

namespace app\models;

use Yii;

use app\models\Property;

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

    public function getCatalog()
    {
        return $this->hasOne(Catalog::className(), ['id' => 'catalog_id']);
    }

    public function getProperties()
    {
        return $this->hasMany(Property::className(), ['object_id' => 'id']);
    }

    
    public function shortProperty()
    {
        $out = [];
        $list_name = Property::find()->joinWith(['propertyName'])->where([
                'and',
                    [
                        'property.object_id' => $this->id,
                    ],
                    [
                        'property_setting.title' => 'property_title'
                    ],
            ])->all();

        $list_value = Property::find()->joinWith(['propertyName'])->where([
                'and',
                    [
                        'property.object_id' => $this->id,
                    ],
                    [
                        'property_setting.title' => 'property_value'
                    ],
            ])->all();

        if (empty($list_name)) {
            return $out;
        }
        foreach ($list_name as $n_name => $name) {
            $out[] = [
                'name' => $name->value,
                'value' => (empty($list_value[$n_name]->value) === false) ? trim($list_value[$n_name]->value) : null,
                
            ];
        }
        return $out;
    }

    public function fullProperty()
    {
        $out = [];
        $list_name = Property::find()->joinWith(['propertyName'])->where([
                'and',
                    [
                        'property.object_id' => $this->id,
                    ],
                    [
                        'property_setting.title' => 'property_full_title'
                    ],
            ])->all();

        $list_value = Property::find()->joinWith(['propertyName'])->where([
                'and',
                    [
                        'property.object_id' => $this->id,
                    ],
                    [
                        'property_setting.title' => 'property_full_value'
                    ],
            ])->all();

        if (empty($list_name)) {
            return $out;
        }
        foreach ($list_name as $n_name => $name) {
            $out[] = [
                'name' => $name->value,
                'value' => (empty($list_value[$n_name]->value) === false) ? trim($list_value[$n_name]->value) : null,
                
            ];
        }
        return $out;
    }

}
