<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Helper;

use Gm\Helper\Html;

/**
 * Вспомогательный класс RowExpander, предоставляет набор статических методов для 
 * генерации часто используемых элементов шаблона расширяемой строки сетки  
 * Gm.view.grid.plugin.RowExpander GmJS.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Helper
 * @since 1.0
 */
class HtmlGridRowExpander extends Html
{
    /**
     * Возвращает содержимое расширенной строки сетки.
     * 
     * @param array|string $content Содержимое строки.
     * 
     * @return string
     */
    public static function rowBody(array|string $content): string
    {
        return static::tag('div', $content, ['class' => 'g-grid__rowbody']);
    }

    /**
     * Возвращает cообщение или текст если отсутствует содержимое строки.
     * 
     * @param array|string $content Сообщение или текст.
     * 
     * @return string
     */
    public static function rowBodyNone(array|string $content): string
    {
        return static::tag('div', $content, ['class' => 'g-grid__rowbody-none']);
    }

    /**
     * Возвращает заголовок расширенной строки сетки.
     * 
     * @param string $title Текст заголовка.
     * 
     * @return string
     */
    public static function rowTitle(string $text): string
    {
        return static::tag('div', $text, ['class' => 'g-grid__rowbody-title']);
    }

    /**
     * Возвращает содержимое поля в виде пары "имя: значение".
     * 
     * @param string $name Имя поля.
     * @param mixed $value Значение поля.
     * @param int $colLabel Количество позиций для имени.
     * @param int $colText Количество позиций для значения поля.
     * 
     * @return string
     */
    public static function rowField(string $name, array|string $value, int $colLabel = 6, int $colText = 6): string
    {
        if ($value === null) return '';
        return
            static::tag('div',
                array(
                    static::tag('div', '<label>' . $name . ':</label>', ['class' => 'col-md-' . $colLabel . ' g-grid__rowbody-label']),
                    static::tag('div', $value, ['class' => 'col-md-' . $colText . ' g-grid__rowbody-text']),
                ),
                ['class' => 'row g-grid__rowbody-field']
            );
    }

    /**
     * Возвращает содержимое наборов полей.
     * 
     * @see HtmlGridRowExpander::rowField()
     * 
     * @param array $items Содержимое полей в виде: `[['name', 'value'], ...]`.
     * @param int $columns Количество столбцов c набором полей (по умолчанию 4).
     * 
     * @return string
     */
    public static function rowFields(array $items, int $columns = 4): string
    {
        $part = ceil(sizeof($items) / $columns);
        $index = 0;
        $columnIndex = 1;
        $columnPart = round(12 / $columns);
        $content = $column = '';
        foreach ($items as $item) {
            $column .= static::rowField($item[0], $item[1]);
            $index++;
            if ($index > ($part - 1)) {
                if ($columnIndex != $columns) {
                    $content .= static::tag('div', $column, ['class' => 'col align-self-start col-md-' . $columnPart]);
                    $column = '';
                } 
                $columnIndex++;
                $index = 0;
            }
        }
        if ($column) {
            $content .= static::tag('div', $column, ['class' => 'col-md-' . $columnPart]);
        }
        return static::tag('div', $content, ['class' => 'row g-grid__rowbody-fields']);
    }

    /**
     * Возвращает тег кнопки вызова виджета.
     * 
     * Вызов виджета (модуля) с помощью JS: `Gm.getApp().widget.load(route)`, где
     * route - маршрут к модулю, который определяется аргументом $route.
     * 
     * @param string $content Содержание (текст) кнопки.
     * @param string $route Маршрут вызова виджета.
     * @param array $attributes Атрибуты тега HTML (параметры HTML) в виде пар "имя - значение".
     *    Они будут отображаться как атрибуты результирующего тега. Значения будут закодированы 
     *    в HTML с использованием {@see Html::encode()}. Если значение `null`, соответствующий атрибут 
     *    не будет отображаться.
     * 
     * @return string
     */
    public static function rowButton(string $content, string $route, array $attributes = []): string
    {
        if (isset($attributes['fa'])) {
            $content = '<i class="fa fa-' . $attributes['fa'] . '" aria-hidden="true"></i> ' . $content;
        }
        if (isset($attributes['icon'])) {
            $content = '<img src="' . $attributes['icon'] . '" align="absmiddle" alt=""> ' . $content;
        }
        if (isset($attributes['iconCls'])) {
            $content = '<span class="' . $attributes['iconCls'] . '"></span> ' . $content;
        }
        $attributes['onclick'] = 'Gm.getApp().widget.load("' . $route .'")';
        return static::button($content, $attributes);
    }

    /**
     * Возвращает контейнер кнопок.
     * 
     * @param string|array $items Содержимое контейнера.
     * 
     * @return string
     */
    public static function rowButtons(array|string $items): string
    {
        return static::tag('div', $items, ['class' => 'g-grid__rowbody-buttons']);
    }
}
