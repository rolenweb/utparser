<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "property_setting".
 *
 * @property integer $id
 * @property string $title
 * @property string $value
 * @property string $type
 * @property integer $created_at
 * @property integer $updated_at
 */
class PropertySetting extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'property_setting';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['title', 'value'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'value' => 'Value',
            'type' => 'Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
