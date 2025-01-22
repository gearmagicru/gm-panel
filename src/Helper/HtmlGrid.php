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
 * Вспомогательный класс HtmlGrid, предоставляет набор статических методов для 
 * генерации часто используемых элементов шаблона сетки.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Helper
 * @since 1.0
 */
class HtmlGrid extends Html
{
    /**
     * Создаёт тег заголовка.
     * 
     * @param string|array $content Cодержание тега.
     * @param array $attributes Атрибуты тега HTML (параметры HTML) в виде пар "имя - значение".
     *    Они будут отображаться как атрибуты результирующего тега. Значения будут закодированы 
     *    в HTML с использованием {@see Html::encode()}. Если значение `null`, соответствующий атрибут 
     *    не будет отображаться (по умолчанию `[]`).
     * 
     * @return string Сгенерированный тег заголовка.
     */
    public static function header(string|array $content, array $attributes = []): string
    {
        return static::tag('header', $content, $attributes);
    }

    /**
     * Создаёт тег метки для вывода пары "содержимое: значение".
     *
     * @param string $content Содержимое метки.
     * @param string $value Значение метки (по умолчанию '').
     * @param null|string $for Идентификатор элемента (тега), с которым следует установить 
     *    связь (по умолчанию `null`).
     * @param array $attributes Атрибуты тега HTML (параметры HTML) в виде пар "имя - значение".
     *    Они будут отображаться как атрибуты результирующего тега. Значения будут закодированы 
     *    в HTML с использованием {@see Html::encode()}. Если значение `null`, соответствующий атрибут 
     *    не будет отображаться (по умолчанию `[]`).
     * 
     * @return string Сгенерированный тег метки.
     */
    public static function fieldLabel(string $content, string $title = '', ?string $for = null, array $attributes = []): string
    {
        $attributes['for'] = $for;
        return '<div>' . static::tag('label', $content . ':', $attributes) . ' ' . $title . '</div>';
    }

   /**
     * Создаёт заголовок группы тегов.
     * 
     * Группа тегов определяется с помощью <fieldset>.
     *
     * @param string $content Текст заголовка.
     * @param array $attributes Атрибуты тега HTML (параметры HTML) в виде пар "имя - значение".
     *    Они будут отображаться как атрибуты результирующего тега. Значения будут закодированы 
     *    в HTML с использованием {@see Html::encode()}. Если значение `null`, соответствующий атрибут 
     *    не будет отображаться (по умолчанию `[]`).
     * 
     * @return string Сгенерированный тег метки.
     */
    public static function legend(string $content, array $attributes = []): string
    {
        return static::tag('legend', $content, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public static function a(string $text, string|array|null $url = null, array $attributes = []): string
    {
        if ($url !== null) {
            $attributes['href'] = $url;
        }
        return static::tag('a', $text, $attributes);
    }

    /**
     * Возвращает значок для значения (true, false).
     * 
     * @param mixed $value Значение.
     * @param int $iconSize Размер значка, допускаются значения: 14, 15, 16, 17, 
     *     18, 19, 20, 32, 64 (по умолчанию 17).
     * 
     * @return string
     */
    public static function checkIcon(mixed $value, int $iconSize = 17): string
    {
        return static::tag('span', '', [
            'class' => 'g-icon g-icon-svg g-icon_size_' . $iconSize .  ($value ? ' g-icon-m_check g-icon-m_color_base' : ' g-icon-m_xmark g-icon-m_color_error')
        ]);
    }

    /**
     * Создаёт тег шаблона "tpl" с выражением для вывода значка (true, false).
     * 
     * Пример: 
     * ```
     * <?php tplChecked('foobar==1') ?>
     * ```
     * Результат рендеринга: ```<tpl if="foobar==1"><span class="..."></span><tpl else><span class="..."></span></tpl>```
     * 
     * @param string $if Выражение.
     * @param int $iconSize Размер значка, допускаются значения: 14, 15, 16, 17, 
     *     18, 19, 20, 32, 64 (по умолчанию 17).
     * 
     * @return string Сгенерированный тег шаблона "tpl" с выражением.
     */
    public static function tplChecked(string $if, int $iconSize = 17): string
    {
        return static::tplIf(
            $if,
            static::checkIcon(1, $iconSize),
            static::checkIcon(0, $iconSize)
        );
    }
}
