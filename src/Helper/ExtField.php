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
 * Класс ExtField является вспомогательным классом Ext и предоставляет набор статических 
 * методов для генерации часто используемых конфигураций компонентов Sencha ExtJS, таких 
 * как поля формы.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Helper
 * @since 1.0
 */
class ExtField
{
    /**
     * Заменяет значение флага выбора (Ext.form.field.Checkbox Sencha ExtJS), полученное 
     * при запросе пользователя на указанное.
     * 
     * Значение заменяется на $true или $false.
     * 
     * @param array $fields Массив полей формы с их значениями в виде пары ключ - значение.
     * @param string $fieldName Имя поля, значение, которого необходимо заменить.
     * @param mixed $true Заменяемое на значение (по умолчанию "1").
     * @param $false $false Заменяемое на значение (по умолчанию "0").
     * 
     * @return void
     */
    public static function checkboxValue(array &$fields, string $fieldName, mixed $true = '1', mixed $false = '0'): void
    {
        if (isset($fields[$fieldName])) {
            $value = $fields[$fieldName];
            if (is_numeric($value)) {
                $fields[$fieldName] = (int) $value > 0 ? $true : $false;
            } else
                $fields[$fieldName] = $value === 'on' ? $true : $false;
        } else
             $fields[$fieldName] = $false;
    }
}
