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
 * Вспомогательный класс HtmlNavigator, предоставляет набор статических методов для 
 * вывода информации в Панель навигации рабочего пространства пользователя.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Helper
 * @since 1.0
 */
class HtmlNavigator extends Html
{
    /**
     * Создаёт основной заголовок.
     * 
     * Тег заголовка H1.
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
        return static::tag('h1', $content, $attributes);
    }

    /**
     * Создаёт тег метки для вывода пары "имя: значение".
     *
     * @param string $name Имя (текст) метки.
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
    public static function fieldLabel(string $name, string $value = '', ?string $for = null, array $attributes = []): string
    {
        $attributes['for'] = $for;
        return '<div>' . static::tag('label', $name . ':', $attributes) . ' ' . $value . '</div>';
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
     * Создаёт тег изображение.
     * 
     * В большинстве случаев изображение используется перед основным заголовком в Панели навигации.
     * 
     * Пример:
     * ```php
     * HtmlNavigator::tags([
     *     HtmlNavigator::image('/images/icon.svg', ['width' => 100], false),
     *     HtmlNavigator::header('Заголовок'),
     *     // ...
     * ]);
     * ```
     * 
     * @param string $src URL-адрес изображения.
     * @param array $options Параметры настройки изображения:
     *     - bool "tag", замена URL-адреса изображения на указанный тег или текст;
     *     - bool "border", добавляет рамку к изображению;
     *     - bool "round", изображение будт иметь, форму круга.
     * @param bool $defineUrl Если значение `true`, URL не будет обработа через {@see \Gm\Helper\Url::to()}.
     * 
     * @return string Сгенерированный тег изображения.
     */
    public static function image(string $src, array $options = [], bool $defineUrl = true): string
    {
        $class = 'g-navigator__image';
        if ($options['round'] ?? false) {
            $class .= ' g-navigator__image_round';
            unset($options['round']);
        }
        if ($options['border'] ?? false) {
            $class .= ' g-navigator__image_border';
            unset($options['border']);
        }
        if ($options['tag'] ?? false) {
            $img = $src;
        } else
            $img = static::img($src, $options, $defineUrl);
        return static::tag('div', '<span>' . $img . '</span>', ['class' => $class]);
    }

   /**
     * Создаёт тег кнопки вызова виджета.
     * 
     * Вызов виджета (модуля) с помощью JS: `Gm.getApp().widget.load(route)`, где
     * route - маршрут к модулю, который определяется параметром аргумента $options.
     *
     * @param string $content Содержание (текс) кнопки (по умолчанию 'Button').
     * @param array Настройки кнопки:
     *     - string "route", маршрут модуля;
     *     - string "color", цвет заливки кнопки (по умолчанию 'default');
     *     - string "icon", URL-адрес значка кнопки;
     *     - bool "long", если значение `true`, ширина кнопки будет равна ширине Панели навигации.
     * @param array $attributes Атрибуты тега HTML (параметры HTML) в виде пар "имя - значение".
     *    Они будут отображаться как атрибуты результирующего тега. Значения будут закодированы 
     *    в HTML с использованием {@see Html::encode()}. Если значение `null`, соответствующий атрибут 
     *    не будет отображаться (по умолчанию `[]`).
     * 
     * @return string Сгенерированный тег кнопки.
     */
    public static function widgetButton(string $content = 'Button', array $options = [], array $attributes = []): string
    {
        if (!isset($attributes['type'])) {
            $attributes['type'] = 'button';
        }
        $class = $attributes['class'] ?? 'g-navigator__button';
        $route = $options['route'] ?? '';
        $color = $options['color'] ?? 'default';
        $icon  = $options['icon'] ?? null;
        $long  = $options['long'] ?? null;
       if ($icon) {
            $iconAlign = $options['iconAlign'] ?? 'left';
            $content .= static::tag('img', '', [
                'alt'   => $content,
                'align' => 'absmiddle',
                'class' => 'g-navigator__icon_' . $iconAlign,
                'src'   => $icon
            ]);
        }
        $attributes['class'] = $class . ($long ? ' ' . $class . '_long' : '') . ' '. $class . '_' . $color;
        $attributes['onclick'] = "Gm.getApp().widget.load('" . $route . "')";
        return static::tag('button', $content, $attributes);
    }

   /**
     * Создаёт тег кнопки с гиперссылкой.
     * 
     * @param string $content Содержание (текс) кнопки (по умолчанию 'Link').
     * @param array Настройки кнопки:
     *     - string "color", цвет заливки кнопки (по умолчанию 'default');
     *     - string "icon", URL-адрес значка кнопки;
     *     - bool "long", если значение `true`, ширина кнопки будет равна ширине Панели навигации.
     * @param array $attributes Атрибуты тега HTML (параметры HTML) в виде пар "имя - значение".
     *    Они будут отображаться как атрибуты результирующего тега. Значения будут закодированы 
     *    в HTML с использованием {@see Html::encode()}. Если значение `null`, соответствующий атрибут 
     *    не будет отображаться (по умолчанию `[]`).
     * 
     * @return string Сгенерированный тег кнопки.
     */
    public static function linkButton(string $content = 'Link', array $options = [], array $attributes = []): string
    {
        $class = $attributes['class'] ?? 'g-navigator__button';
        $color = $options['color'] ?? 'default';
        $icon  = $options['icon'] ?? null;
        $long  = $options['long'] ?? null;
       if ($icon) {
            $iconAlign = $options['iconAlign'] ?? 'left';
            $content .= static::tag('img', '', [
                'alt'   => $content,
                'align' => 'absmiddle',
                'class' => 'g-navigator__icon_' . $iconAlign,
                'src'   => $icon
            ]);
        }
        $attributes['class'] = $class . ($long ? ' ' . $class . '_long' : '') . ' '. $class . '_' . $color;
        return static::tag('a', $content, $attributes);
    }

    /**
     * Возвращает значок с тегом гиперссылки на указанный ресурс.
     * 
     * Пример перехода к модулю Панели управления по указанному маршруту: 
     * ```php
     * HtmlNavigator::linkIcon('link', 19, 
     *     [
     *         'onclick' => Ext::jsAppWidgetLoad('@backend/post/view'),
     *          'title'  => 'Просмотреть'
     *     ]
     * );
     * ```
     * 
     * @param string $icon Значок (см. "\assets\icons\icons.css" текущей темы).
     * @param int $iconSize Размер значка, допускаются значения: 14, 15, 16, 17, 
     *     18, 19, 20, 32, 64 (по умолчанию 14).
     * @param array $attributes Атрибуты тега гиперссылки в виде пар "имя => значение".
     *    Они будут отображаться как атрибуты результирующего тега. Значения будут закодированы 
     *    в HTML с использованием {@see Html::encode()}. Если значение `null`, соответствующий атрибут 
     *    не будет отображаться.
     * 
     * @return string
     */
    public static function linkIcon(string $icon, int $iconSize = 14, array $attributes = []): string
    {
        $attributes['class'] = 'g-icon g-icon-svg g-icon_size_' . $iconSize . ' g-icon-m_' . $icon . ' g-icon-m_color_default g-icon-m_is-hover';
        if (!isset($attributes['href'])) {
            $attributes['href'] = '#';
        }
        return static::tag('a', '', $attributes);
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
