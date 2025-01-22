<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Helper;

use Gm;

/**
 * Вспомогательный класс Ext, предоставляет набор статических методов для генерации 
 * конфигурации часто используемых компонентов Sencha ExtJS.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Helper
 * @since 1.0
 */
class Ext
{
    /**
     * Возвращает конфигурацию компонента Sencha ExtJS.
     * 
     * @param array $defaults Параметры конфигурации компонента по умолчанию.
     * @param array $initialConfig Конфигурация компонента, передаваемая в конструктор 
     *     во время его создания (по умолчанию `[]`).
     * 
     * @return array
     */
    public static function component(array $defaults, array $initialConfig = []): array
    {
        if ($initialConfig) {
            return array_merge($defaults, $initialConfig);
        }
        return $defaults;
    }

    /**
     * Возвращает конфигурацию текстового поля формы (Ext.form.field.Text Sencha ExtJS).
     * 
     * @param string $label Текстовая метка поля.
     * @param string $name Имя поля формы.
     * @param string $value Значение поля формы.
     * @param array $initialConfig Конфигурация компонента, передаваемая в конструктор 
     *     во время его создания (по умолчанию `[]`).
     * 
     * @return array
     */
    public static function textField(string $label, string $name, string $value, array $initialConfig = []): array
    {
        return static::component(
            [
                'xtype'      => 'textfield', 
                'fieldLabel' => $label, 
                'name'       => $name, 
                'value'      => $value
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию текстового поля формы (Ext.form.field.Number Sencha ExtJS).
     * 
     * @param string $label Текстовая метка поля.
     * @param string $name Имя поля формы.
     * @param mixed $value Значение поля формы.
     * @param array $initialConfig Конфигурация компонента, передаваемая в конструктор 
     *     во время его создания (по умолчанию `[]`).
     * 
     * @return array
     */
    public static function numberField(string $label, string $name, $value, array $initialConfig  = []): array
    {
        return static::component(
            [
                'xtype'      => 'numberfield', 
                'fieldLabel' => $label, 
                'name'       => $name, 
                'value'      => $value
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию набора полей (Ext.form.FieldSet Sencha ExtJS).
     * 
     * @param string $title Загаловок.
     * @param array $items Компоненты (конфигурации) набора полей.
     * @param array $initialConfig Конфигурация компонента, передаваемая в конструктор 
     *     во время его создания (по умолчанию `[]`).
     * 
     * @return array
     */
    public static function fieldset(string $title, array $items, array $initialConfig = []): array
    {
        return static::component(
            [
                'xtype' => 'fieldset', 
                'title' => $title, 
                'items' => $items
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию набора полей аудита записей.
     * 
     * Используется при фильтрации списка записей.
     * 
     * @return array
     */
    public static function fieldsetAudit(): array
    {
        return static::fieldset(
            Gm::t(BACKEND, 'edit / add record'),
            [
                ExtCombo::local(
                    Gm::t(BACKEND, 'Date'),
                    'logDate',
                    [
                        'fields' => ['id', 'name'],
                        'data'   => [
                            ['null',  Gm::t(BACKEND, '[None]')],
                            ['lt-1d', Gm::t(BACKEND, 'today')],
                            ['lt-2d', Gm::t(BACKEND, 'yesterday')],
                            ['lt-1w', Gm::t(BACKEND, 'during the week')],
                            ['lt-1m', Gm::t(BACKEND, 'per month')],
                            ['lt-1y', Gm::t(BACKEND, 'in a year')]
                        ]
                    ]
                ),
                ExtCombo::imageTrigger(Gm::t(BACKEND, 'User'), 'logUser', 'users', ['users/trigger/combo', 'backend'])
            ],
            ['labelWidth' => 80]
        );
    }

    /**
     * Возвращает конфигурацию поля флага выбора (Ext.form.field.Checkbox Sencha ExtJS).
     * 
     * @param string $label Текстовая метка поля.
     * @param string $name Имя поля формы.
     * @param bool $checked Значение поля формы. Если флаг установлен `true` (по умолчанию `false`).
     * @param array $initialConfig Конфигурация компонента, передаваемая в конструктор 
     *     во время его создания (по умолчанию `[]`).
     * 
     * @return array
     */
    public static function checkbox(string $label, string $name, bool $checked = false, array $initialConfig = []): array
    {
        return static::component(
            [
                'xtype'      => 'checkbox', 
                'fieldLabel' => $label, 
                'name'       => $name, 
                'checked'    => $checked
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию поля переключателя (Ext.form.field.Checkbox Sencha ExtJS).
     * 
     * Где, ui = 'switch'.
     * 
     * @param string $label Текстовая метка поля.
     * @param string $name Имя поля формы.
     * @param bool $checked Значение поля формы. Если флаг установлен `true` (по умолчанию `false`).
     * @param array $initialConfig Конфигурация компонента, передаваемая в конструктор 
     *     во время его создания (по умолчанию `[]`).
     * 
     * @return array
     */
    public static function switch(string $label, string $name, bool $checked = false, array $initialConfig = []): array
    {
        $initialConfig['ui'] = 'switch';
        $initialConfig['inputValue'] = 1;
        return static::checkbox($label, $name, $checked, $initialConfig);
    }

    /**
     * Устанавливает элементу кнопки, значок соответствующего типа.
     * 
     * @param array $button Параметры кнопки.
     * @param string $type Тип значка, например: 'font', 'glyph', 'icon', 'css', 'fcss'.
     * @param string $value Значок.
     * @param string $text Текст кнопки после значка (по умолчанию '').
     * @param string $inside Текст внутри тега значка (по умолчанию '').
     * 
     * @return void
     */
    public static function buttonIcon(array &$button, string $type, string $value, string $text = '', $inside = ''): void
    {
        switch ($type) {
            // класс css подключаемого шрифта
            case 'font':
                $button['text'] = '<i class="icon-font ' . $value . '">' . $inside . '</i> ' . $text;
                break;

            // числовой код Unicode для использования в качестве значка кнопки
            case 'glyph':
                $button['glyph'] = $value;
                break;

            // путь к изображению, отображаемому в кнопке
            case 'icon':
                $button['icon'] = $value;
                break;

            // класс css, который устанавливает фоновое изображение в качестве значка кнопки
            case 'css':
                $button['iconCls'] = $value;
                break;

            // класс css, который устанавливает фоновое изображение в качестве значка кнопки
            case 'fcss':
                $button['iconCls'] = 'g-icon-fcss ' . $value;
                break;
        }
    }

    /**
     * Возвращает значок соответствующего типа.
     * 
     * @param string $value Значок.
     * @param string $type Тип значка, например: 'font', 'glyph', 'icon', 'css', 'fcss'.
     * 
     * @return string
     */
    public static function renderIcon(string $value, string $type): string
    {
        switch ($type) {
            // CSS-класс глифа шрифта (Font Awesome...)
            case 'font': return '<i class="icon-font ' . $value . '"></i> ';

            // числовой код Unicode (в качестве значка элемента управления: кнопки, пункта меню...)
            case 'glyph': return "<span>&#$value;</span>";

            // путь к изображению (отображаемому в элементе управления: кнопка, пункт меню...)
            case 'icon': return '<img src="' . $value . '">';

            // CSS-класс глифа шрифта (Font Awesome...) в заливке тега
            case 'css': return '<span class="' . $value . '"></span>';

            // CSS-класс глифа шрифта (Font Awesome...) в элементе управления 
            case 'fcss': return '<span class="g-icon-fcss ' . $value . '"></span>';

            // CSS-класс SVG значка в элементе управления 
            case 'svg': return '<span class="g-icon g-icon-svg ' . $value . '"></span>';

            default: return '';
        }
    }

    /**
     * Возвращает метод 'Gm.app' для загрузки виджета по указанному маршруту.
     * 
     * @param string $route Маршрут загрузки виджета, например, '@backend/foo/bar'. 
     * @param array $params Параметры загрузки виджета (по умолчанию `[]`).
     * @param bool $doubleQuote Замена двойных кавычек на одинарные (по умолчанию `false`).
     * 
     * @return string|null
     */
    public static function jsAppWidgetLoad(?string $route, array $params = [], bool $doubleQuote = false): ?string
    {
        if ($route) {
            $params = $params ? json_encode($params) : '';
            $str = 'Gm.getApp().widget.load("' . $route . '"' . ($params ? ', ' . $params : '') . ')';
            return $doubleQuote ? $str : strtr($str, '"', '\'');
        }
        return null;
    }
}
