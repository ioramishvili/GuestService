<?php

declare(strict_types=1);

namespace app\models;

use app\helpers\PhoneHelper;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\Expression;

/**
 * Модель для работы с таблицей гостей.
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $email
 * @property string $phone
 * @property string|null $country
 */
class Guest extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'guest';
    }

    public function rules(): array
    {
        return [
            [['first_name', 'last_name', 'phone'], 'required'],
            [['email', 'phone'], 'unique'],
            ['email', 'email'],
            [['first_name', 'last_name', 'country'], 'string', 'max' => 30],
            ['phone', 'match', 'pattern' => '/^\+\d{1,15}$/'],
        ];
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    BaseActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }

    public function fields(): array
    {
        $fields = parent::fields();

        $fields['created_at'] = function () {
            return $this->created_at instanceof Expression ? date('Y-m-d H:i:s') : $this->created_at;
        };
        $fields['updated_at'] = function () {
            return $this->updated_at instanceof Expression ? date('Y-m-d H:i:s') : $this->updated_at;
        };

        return $fields;
    }

    public function beforeSave($insert): bool
    {
        if (parent::beforeSave($insert)) {
            if (empty($this->country)) {
                $this->country = PhoneHelper::getCountryName(phoneNumber: $this->phone);
            }

            $this->country = PhoneHelper::getCountryName(countryCode: $this->country);

            if (empty($this->country)) {
                $this->addError('country', 'Ошибка определения страны гостя.');
                return false;
            }

            return true;
        }
        return false;
    }
}