<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Helper;

/**
 * Вспомогательный класс Ext, предоставляет набор статических 
 * методов для генерации часто используемых компонентов Sencha ExtJS.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Helper
 * @since 1.0
 */
class ExtGridTree extends ExtGrid
{
    /**
     * {@inheritdoc}
     */
    public static string $selector = 'treepanel';
}
