<?php

declare(strict_types=1);

namespace app\models;

use app\helpers\PhoneHelper;
use yii\db\ActiveRecord;

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