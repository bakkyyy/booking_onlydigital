<?php

namespace OnlyDigital\Mvc\Controller;

use Bitrix\Main\Engine\Response\Json;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;
use OnlyDigital\Iblock\Booking;


class Drives extends Prototype
{
    public function getDrivesInfoAction()
    {
        global $USER;
        if (!$USER->IsAuthorized()) {
            return $this->jsonError('Необходима авторизация');
        }

        $start = $this->getParam('start');
        $end = $this->getParam('end');

        if (empty($start) || empty($end)) {
            return $this->jsonError('Не указано время начала поездки');
        }

        $startDate = DateTime::createFromPhp(\DateTime::createFromFormat('Y-m-d\TH:i', $start));
        $endDate = DateTime::createFromPhp(\DateTime::createFromFormat('Y-m-d\TH:i', $end));

        try {
            $booking = new Booking((int)$USER->GetID(), $startDate, $endDate);
            $cars = $booking->getCars();
        } catch (\Throwable $e) {
            $cars = [];
            //Лог
        }


        return new Json([
            'success' => true,
            'data' => ['cars' => $cars]
        ]);
    }

    private function jsonError($message)
    {
        return new Json(['status' => 'error', 'message' => $message]);
    }
}
