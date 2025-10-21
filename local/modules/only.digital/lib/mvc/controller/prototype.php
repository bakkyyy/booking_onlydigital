<?php

namespace OnlyDigital\Mvc\Controller;

use Bitrix\Main\Application;
use RuntimeException;

/**
 * Прототип AJAX контроллера
 */
class Prototype
{
    /**
     * Request
     */
    protected $request = null;

    /**
     * View
     *
     */
    protected $view = null;

    /**
     * Параметры
     *
     * @var array
     */
    protected $params = [];

    /**
     * Создает новый контроллер
     *
     */
    public function __construct()
    {
        $this->request = Application::getInstance()->getContext()->getRequest();
    }

    /**
     * Фабрика контроллеров
     *
     * @param string $name
     * @throws RuntimeException
     */
    public static function factory(string $name)
    {
        $name = preg_replace('/[^A-z0-9_]/', '', $name);
        $className = '\\' . __NAMESPACE__ . '\\' . ucfirst($name);

        if (!class_exists($className)) {
            throw new RuntimeException(sprintf('Controller "%s" doesn\'t exists.', $name));
        }

        return new $className();
    }

    /**
     * Выполняет экшн контроллера
     *
     * @param string $name Имя экшена
     * @return void
     * @throws RuntimeException
     */
    public function doAction(string $name): void
    {
        $name = preg_replace('/[^A-z0-9_]/', '', $name);
        $methodName = $name . 'Action';

        if (!method_exists($this, $methodName)) {
            throw new RuntimeException(sprintf('Action "%s" doesn\'t exists.', $name));
        }

        $response = new \stdClass();
        $response->success = false;
        try {
            $response->data = $this->$methodName();
            $response->success = true;
        } catch (\Exception $e) {
            $response->code = $e->getCode();
            $response->message = $e->getMessage();
        }

        try {
            header('Content-type: application/json');
            print $response->data->getContent();
        } catch (\Exception $e) {
            print $e->getMessage();
        }
    }

    /**
     * Возвращает значение входного параметра
     *
     * @param string $name Имя параметра
     * @param mixed|string $default Значение по умолчанию
     * @return mixed
     */
    protected function getParam(string $name, ?string $default = ''): mixed
    {
        $result = array_key_exists($name, $this->params)
            ? $this->params[$name]
            : $this->request->get($name);

        return $result ?? $default;
    }
}
