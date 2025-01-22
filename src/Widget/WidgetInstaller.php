<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Widget;

use Gm;
use Gm\Mvc\Module\BaseModule;

/**
 * Установщик виджета.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class WidgetInstaller extends \Gm\WidgetManager\WidgetInstaller
{
    /**
     * Модуль, контроллер которого выполняет установку.
     * 
     * @see ModuleInstaller::configure()
     * 
     * @var BaseModule
     */
    public BaseModule $module;

    /**
     * Перевод (локализация) сообщения или сообщений.
     * 
     * Перевод сообщений выполняет модуль {@see WidgetInstaller::$module}, которому 
     * передали управление.
     * 
     * @param string|array<int, string> $message Текст сообщения (сообщений).
     * @param array<int, string> $params Параметры локализация (по умолчанию `[]`).
     * @param string $locale Код локализации, например: 'ru_RU', 'en_GB'. Если значение '', 
     *     то применяется текущая локализация (по умолчанию '').
     * 
     * @return string|array<int, string> Локализованное сообщение (сообщения).
     */
    public function t($message, array $params = [], string $locale = '')
    {
        return Gm::$services->getAs('translator')->translate($this->module->id, $message, $params, $locale);
    }
}
