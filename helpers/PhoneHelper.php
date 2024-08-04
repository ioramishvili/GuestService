<?php

declare(strict_types=1);

namespace app\helpers;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;
use CommerceGuys\Addressing\Country\CountryRepository;
use Yii;

class PhoneHelper
{
    private const CACHE_DURATION = 24 * 60 * 60;

    /**
     * Получает код страны по номеру телефона.
     *
     * @param string $phoneNumber Номер телефона в международном формате.
     * @return string|null Код страны или null в случае ошибки.
     */
    public static function getCountryCodeByPhone(string $phoneNumber): ?string
    {
        $cacheKey = 'country_code_' . md5($phoneNumber);

        $cachedCountryCode = Yii::$app->cache->get($cacheKey);
        if ($cachedCountryCode !== false) {
            return $cachedCountryCode;
        }

        $phoneNumberUtil = PhoneNumberUtil::getInstance();

        try {
            $numberProto = $phoneNumberUtil->parse($phoneNumber);

            $countryCode = $phoneNumberUtil->getRegionCodeForNumber($numberProto);

            Yii::$app->cache->set($cacheKey, $countryCode, self::CACHE_DURATION);

            return $countryCode;
        } catch (NumberParseException $e) {
            Yii::error('Ошибка разбора номера телефона: ' . $e->getMessage(), __METHOD__);
            return null;
        }
    }

    /**
     * Преобразует код страны в полное название страны.
     *
     * @param string $countryCode Код страны в формате ISO 3166-1 alpha-2.
     * @param string $locale Локаль для отображения названия страны (по умолчанию 'ru').
     * @return string|null Полное название страны или null, если код не найден.
     */
    public static function getCountryNameByCode(string $countryCode, string $locale = 'ru'): ?string
    {
        $cacheKey = 'country_name_' . $countryCode . '_' . $locale;

        $cachedCountryName = Yii::$app->cache->get($cacheKey);
        if ($cachedCountryName !== false) {
            return $cachedCountryName;
        }

        try {
            $countryRepository = new CountryRepository();
            $countryName = $countryRepository->get($countryCode, $locale)->getName();

            Yii::$app->cache->set($cacheKey, $countryName, self::CACHE_DURATION);

            return $countryName;
        } catch (\Exception $e) {
            Yii::error('Ошибка получения названия страны: ' . $e->getMessage(), __METHOD__);
            return null;
        }
    }

    /**
     * Получает полное название страны, используя код страны или номер телефона.
     *
     * @param string|null $countryCode Код страны, заданный вручную (может быть null).
     * @param string|null $phoneNumber Номер телефона в международном формате (может быть null).
     * @return string|null Полное название страны или null, если страна не определена.
     */
    public static function getCountryName(?string $countryCode = null, ?string $phoneNumber = null): ?string
    {
        $cacheKey = 'full_country_name_' . md5($countryCode . $phoneNumber);

        $cachedCountryName = Yii::$app->cache->get($cacheKey);
        if ($cachedCountryName !== false) {
            return $cachedCountryName;
        }

        if (!empty($countryCode)) {
            $countryName = self::getCountryNameByCode($countryCode);
        } elseif (!empty($phoneNumber)) {
            $detectedCountryCode = self::getCountryCodeByPhone($phoneNumber);
            $countryName = $detectedCountryCode !== null ? self::getCountryNameByCode($detectedCountryCode) : null;
        } else {
            $countryName = null;
        }

        Yii::$app->cache->set($cacheKey, $countryName, self::CACHE_DURATION);

        return $countryName;
    }
}