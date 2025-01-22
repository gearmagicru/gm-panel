<?php
/**
 * Этот файл является частью пакета GM Framework.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Widget;

/**
 * WidgetInterface - это интерфейс, который должен быть реализован классом, 
 * формирующий элемент интерфейса Панели управления.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
interface WidgetInterface
{
    /**
     * Добавляет имена классов JS виджета.
     * 
     * @see Widget::$requires
     * 
     * @param string $name Имя класса JS виджета, например: 'Gm.wd.foobar.Name'.
     * 
     * @return $this
     */
    public function addRequire(string $name): static;

    /**
     * Добавляет имя файлов таблицы стилей виджета.
     * 
     * @see Widget::$css
     * 
     * @param string $filename Имя файла таблицы стилей, например: '/foobar.css', '/foo/bar.css'.
     * 
     * @return $this
     */
    public function addCss(string $filename): static;

    /**
     * Устанавливает пространство имён JS виджета.
     * 
     * @see Widget::$namespaceJs
     * 
     * @param string $namespace Пространство имён, например 'Gm.wd.foobar'.
     * 
     * @return $this
     */
    public function setNamespaceJS(string $namespace): static;

    /**
     * Возвращает URL-адрес изображения.
     * 
     * @param string $filename Имя файла изображения (может включать путь). 
     *     Всегда начинается с символа "/".
     * 
     * @return string
     */
    public function imageSrc(string $filename): string;

    /**
     * Возвращает URL-адрес файла каскадной таблицы стилей.
     * 
     * @param string $filename Имя CSS-файла (может включать путь). Всегда начинается 
     *     с символа "/".
     * 
     * @return string
     */
    public function cssSrc(string $filename): string;

    /**
     * Возвращает URL-адрес файла скриптов.
     * 
     * @param string $filename Имя JS-файла (может включать путь). Всегда начинается 
     *     с символа "/".
     * 
     * @return string
     */
    public function jsSrc(string $filename): string;

    /**
     * Возвращает URL-путь к файлу скрипта модуля.
     * 
     * Используется для подключения скрипта с помощью GmJS.
     * 
     * Например: '/Controller' => '/modules/foo/bar/assets/js/Controller'
     * 
     * @param string $name Базовое имя JS-файла (может включать путь). 
     *     Всегда начинается с символа "/".
     *     Например, для файла "/Controller.js" это "/Controller".
     * 
     * @return string
     */
    public function jsPath(string $name = ''): string;
}
