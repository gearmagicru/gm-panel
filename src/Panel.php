<?php
/**
 * GM Panel.
 * 
 * @link https://gearmagic.ru/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel;

 use Gm;
 use Gm\Debug\Dumper;
 use Gm\Panel\Controller\BaseController;
 use Gm\Panel\Http\Response;

/**
 * Panel - это основной вспомогательный класс для Панели управления.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel
 * @since 1.0
 */
class Panel
{
    /**
     * Отладка переменной c использованием окна отладки Этот файл является частью 
     * пакета GM Panel.
     * 
     * Отладка исключительно для Этот файл является частью пакета GM Panel с 
     * HTTP-ответом {@see \Gm\Panel\Http\JsongResponse}.
     * 
     * @param mixed $var Переменная.
     * @param array $config Конфигурация окна отладки.
     * @param bool|BaseController|null $controller Контролер.
     *    Если значение `true`, то определяет последний контролер созданный модулем для получения 
     *    ответа {@see \Gm\Panel\Controller\BaseController::getResponse()}. 
     *    Если контролер не указан или ещё не создан, использует ответ приложения `Gm::$app->response`.
     *    Если указан контролер {@see \Gm\Panel\Controller\BaseController}, использует его метод `getResponse()`.
     * 
     * @return void
     */
    public static function dump(mixed $var, array $config = [], bool|BaseController|null $controller = true): void
    {
        $view = [
            'xtype'       => 'window',
            'title'       => 'Dump',
            'iconCls'     => 'far fa-bug',
            'cls'         => 'g-window-info',
            'width'       => 500,
            'height'      => 500,
            'maximizable' => true,
            'autoScroll'  => true,
            'bodyPadding' => 10,
            'html'        => Dumper::dumpAsString($var, true)
        ];
        if ($config) {
            $view = array_merge($view, $config);
        }

        if ($controller instanceof BaseController)
            $response = $controller->getResponse();
        else
        if ($controller === true)
            $response = Gm::$app->module->controller?->getResponse();
        else
            $response = Gm::$app->response;

        if ($response instanceof Response) {
            $response
                ->setFormat(Response::FORMAT_JSONG)
                ->meta
                    ->cmdCreate($view);
        }
    }

    /**
     * Выводит сообщения, содержащие некоторую информацию, в консоль браузера.
     * 
     * @see \Gm\Panel\Http\Response\JsongMetadata::cmdConsole()
     * @see \Gm\View\Helper\Script::console()
     * 
     * @param string $type Тип сообщения, например: 'log', 'error', 'warn', 'table', 'dir'.
     * @param string $message Сообщение.
     * @param array $vars Список объектов JavaScript для вывода.
     * 
     * @return void
     */
    public static function console(string $type, string $message, array $vars): void
    {
        /** @var Response $response */
        $response = self::getResponse();
        switch ($response->format) {
            case Response::FORMAT_JSONG:
                $response
                    ->meta
                        ->cmdConsole($type, $message, $vars);
                break;

            case Response::FORMAT_HTML:
                Gm::$app->clientScript->js->console($type, $message, $vars);
                break;
        }
    }

    /**
     * Выводит сообщение в консоль браузера.
     * 
     * @link https://developer.mozilla.org/ru/docs/Web/API/console/log_static
     * 
     * @param string $message Сообщение.
     * @param mixed ...$vars Список объектов JavaScript для вывода.
     * 
     * @return $this
     */
    public static function consoleLog(string $message, mixed ...$vars): void
    {
        static::console('log', $message, $vars);
    }

    /**
     * Выводит сообщения, содержащие некоторую информацию, в консоль браузера.
     * 
     * @link https://developer.mozilla.org/ru/docs/Web/API/console/info_static
     * 
     * @param string $message Сообщение.
     * @param mixed ...$vars Список объектов JavaScript для вывода.
     * 
     * @return $this
     */
    public static function consoleInfo(string $message, mixed ...$vars): void
    {
        static::console('info', $message, $vars);
    }

    /**
     * Выводит ошибку в консоль браузера.
     * 
     * @link https://developer.mozilla.org/ru/docs/Web/API/console/error_static
     * 
     * @param string $message Сообщение.
     * @param mixed ...$vars Список объектов JavaScript для вывода.
     * 
     * @return $this
     */
    public static function consoleError(string $message, mixed ...$vars): void
    {
        static::console('error', $message, $vars);
    }

    /**
     * Выводит предупреждение в консоль браузера.
     * 
     * @link https://developer.mozilla.org/ru/docs/Web/API/console/warn_static
     * 
     * @param string $message Сообщение.
     * @param mixed ...$vars Список объектов JavaScript для вывода.
     * 
     * @return $this
     */
    public static function consoleWarn(string $message, mixed ...$vars): void
    {
        static::console('warn', $message, $vars);
    }

    /**
     * Выводит в консоли браузера все свойства JavaScript объекта.
     * 
     * @link https://developer.mozilla.org/ru/docs/Web/API/console/dir_static
     * 
     * @param mixed $object JavaScript-объект, свойства которого нужно вывести.
     * @param array<string, mixed> $options Настройки вывода.
     * 
     * @return $this
     */
    public static function consoleDir(mixed $object, array $options = []): void
    {
        $args = [$object];
        if ($options) {
            $args[] = $options;
        }
        static::console('dir', '', $args);
    }

    /**
     * Выводит в консоль браузера набор данных в виде таблицы.
     * 
     * @link https://developer.mozilla.org/ru/docs/Web/API/console/table_static
     * 
     * @param array<int, array<string, mixed>> $rows Набор данных.
     * 
     * @return $this
     */
    public static function consoleTable(array $rows): void
    {
        static::console('table', '', [$rows]);
    }

    /**
     * Возвращает HTTP-ответ текущего модуля и контроллера.
     * 
     * @return Response
     */
    protected static function getResponse(): Response
    {
        static $response = null;

        if ($response === null) {
            $module = Gm::module();
            if ($module) {
                $controller = $module->controller();
                if ($controller) {
                    $response = $controller->getResponse();
                } else
                    $response = Gm::$app->response->setFormat(Response::FORMAT_JSONG);
            } else
                $response = Gm::$app->response->setFormat(Response::FORMAT_JSONG);
        }
        return $response;
    }
}
