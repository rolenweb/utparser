<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "property".
 *
 * @property integer $id
 * @property integer $object_id
 * @property integer $property_id
 * @property string $value
 * @property integer $created_at
 * @property integer $updated_at
 */
class Property extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'property';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id', 'property_id', 'created_at', 'updated_at'], 'integer'],
            [['value'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'object_id' => 'Object ID',
            'property_id' => 'Property ID',
            'value' => 'Value',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
