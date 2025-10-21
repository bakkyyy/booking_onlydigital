<?php

namespace OnlyDigital\Iblock;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use CIBlockElement;
use CUser;
use Bitrix\Main\Type\DateTime;

class Booking
{
    private int $userId;
    private DateTime $start;
    private DateTime $end;

    /**
     * @param int $userId
     * @param DateTime $start
     * @param DateTime $end
     * @throws LoaderException
     */
    public function __construct(int $userId, DateTime $start, DateTime $end)
    {
        Loader::includeModule('highloadblock');
        Loader::includeModule('iblock');

        $this->userId = $userId;
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * Возвращает незанятые автомобили для пользователя, в зависимости от комфорта
     * @return array
     */
    public function getCars(): array
    {
        $cars = $this->getCarsByComfort();

        if (empty($cars)) {
            return [];
        }

        $busyCars = $this->getBusyCars();

        $cars = array_diff_key($cars, array_flip($busyCars));

        $formattedData = [];

        foreach ($cars as $car) {
            $formattedData[] = [
                'model' => $car['UF_NAME'],
                'comfort' => $car['UF_COMFORT'],
                'driver' => $car['UF_DRIVER_FIO'],
            ];
        }

        return $formattedData ?? [];
    }

    /**
     * Возвращает доступные автомобили для пользователя, в зависимости от комфорта
     * @return array
     */
    private function getCarsByComfort(): array
    {
        try {
            $cache = Cache::createInstance();
            $groups = CUser::GetUserGroup($this->userId);
            sort($groups, SORT_NUMERIC);

            $cacheParams = [
                3600,
                md5(implode(',', $groups)),
                '/comfortscars'
            ];
            // В идеале, нужно написать отдельный абстрактный класс для работы с HL-блоками, хранение инстасов, добавить тегированный кеш для авто-сброса
            if ($cache->initCache(...$cacheParams)) {
                $cars = $cache->getVars();
            } elseif ($cache->startDataCache()) {
                $carsDataClass = HighloadBlockTable::compileEntity('Cars')->getDataClass();
                $cars = $carsDataClass::getList([
                    'runtime' => [
                        new ReferenceField(
                            'COMFORT_RULES',
                            HighloadBlockTable::compileEntity('ComfortRules'),
                            ['this.UF_COMFORT' => 'ref.UF_ALLOWED_COMFORT'],
                            ['join_type' => 'inner']
                        ),
                        new ReferenceField(
                            'DRIVER',
                            HighloadBlockTable::compileEntity('Drivers'),
                            ['this.UF_DRIVER' => 'ref.ID'],
                            ['join_type' => 'left']
                        ),
                    ],
                    'filter' => [
                        'COMFORT_RULES.UF_GROUP_ID' => $groups,
                    ],
                    'select' => [
                        'UF_NAME',
                        'UF_COMFORT',
                        'UF_XML_ID',
                        'UF_DRIVER_FIO' => 'DRIVER.UF_FIO',
                    ],
                ])->fetchAll();

                $cars = array_column($cars, null, 'UF_XML_ID');
                $cache->endDataCache($cars);
            }
        } catch (\Throwable $e) {
            // Лог
            $cars = [];
        }
        return $cars ?? [];
    }

    /**
     * Возвращает занятые автомобили
     * @return array
     */
    private function getBusyCars(): array
    {
        $cacheParams = [
            300, //Кратковременный кеш 300
            'busy_cars_' . md5($this->start->getTimestamp() . '_' . $this->end->getTimestamp()),
            '/busycars'
        ];
        $cache = Cache::createInstance();
        if ($cache->initCache(...$cacheParams)) {
            $busyCars = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $rsElements = CIBlockElement::GetList(
                [],
                [
                    'IBLOCK_CODE' => 'Drives',
                    'ACTIVE' => 'Y',
                    [
                        'LOGIC' => 'AND',
                        ['<=PROPERTY_DATE_START' => ConvertDateTime($this->end, 'YYYY-MM-DD HH:MI:SS')],
                        ['>=PROPERTY_DATE_END' => ConvertDateTime($this->start, 'YYYY-MM-DD HH:MI:SS')],
                    ],
                ],
                false,
                false,
                [
                    'PROPERTY_CAR',
                ]
            );

            $busyCars = [];
            while ($arElement = $rsElements->GetNext()) {
                $busyCars[] = $arElement['PROPERTY_CAR_VALUE'];
            }

            $busyCars = array_unique($busyCars);
            $cache->endDataCache($busyCars);
        }

        return $busyCars ?? [];
    }
}
