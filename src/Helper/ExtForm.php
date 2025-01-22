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
 * Класс ExtForm является вспомогательным классом Ext и предоставляет набор статических 
 * методов для генерации часто используемых конфигураций компонентов Sencha ExtJS, таких 
 * как: Buttons (кнопки панели инструментов), Tabs (вкладки).
 * 
 * @link https://docs.sencha.com/extjs/5.1.4/api/Ext.button.Button.html
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Helper
 * @since 1.0
 */
class ExtForm extends Ext
{
    /**
     * Возвращает конфигурации кнопок (Ext.button.Button Sencha ExtJS).
     * 
     * @param array<int|string, string|array<string, mixed>> $buttons Названия кнопок: 
     *     'tool', 'action', 'info', 'help', 'cancel', 'close', 'save', 'submit', 
     *     'add', 'delete', 'reset', 'browse'. Каждому названию будет 
     *     соответствовать вызываемый метод, который возвращает конфигурацию. Например:
     *        - `['info', 'add', 'cancel']`;
     *        - `['info', 'add' => ['text' => 'Add record'], 'cancel']`;
     *        - '['info', ['text' => 'Add record'], 'cancel'].
     * 
     * @return array<int, array<string, mixed>>
     */
    public static function buttons(array $buttons): array
    {
        $items = [];
        foreach ($buttons as $index => $button) {
            if (is_string($index) && is_array($button)) {
                $name = $index . 'Button';
                if (method_exists(static::class, $name)) {
                    $items[] = static::$name($button);
                }

            } else
            if (is_string($button)) {
                $name = $button . 'Button';
                if (method_exists(static::class, $name)) {
                    $items[] = static::$name();
                }
            } else
                $items[] = $button;
        }
        return $items;
    }

    /**
     * Возвращает конфигурацию кнопки (Gm.view.grid.button.Button GmJS).
     * 
     * @see Ext::component()
     * 
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function button(array $initialConfig = []): array
    {
        return static::component(
            [
                'xtype' => 'button',
                'ui'    => 'form'
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию кнопки "Инструмент" (Gm.view.grid.button.Button GmJS).
     * 
     * Обработчик кнопки 'loadWidget' вызывает виджет.
     * 
     * Применяется ui 'form-tool'.
     * 
     * @see Ext::component()
     * 
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function toolButton(array $initialConfig = []): array
    {
        return static::component(
            [
                'xtype'    => 'button',
                'ui'       => 'form-tool',
                'minWidth' => 40,
                'handler'  => 'loadWidget'
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию кнопки "Действие" (Gm.view.grid.button.Button GmJS).
     * 
     * Обработчик кнопки 'onFormAction' вызывает действие формы.
     * 
     * Применяется ui 'form'.
     * 
     * @see Ext::component()
     * 
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function actionButton(array $initialConfig = []): array
    {
        return static::component(
            [
                'xtype'   => 'button',
                'ui'      => 'form',
                'handler' => 'onFormAction'
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию кнопки "Информация" (Gm.view.grid.button.Button GmJS).
     * 
     * Обработчик кнопки 'loadWidget' вызывает виджет справочной информации.
     * 
     * Применяет ui 'form-info'.
     * 
     * @see Ext::component()
     * 
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function infoButton(array $initialConfig = []): array
    {
        return static::component(
            [
                'xtype'     => 'button',
                'iconCls'   => 'g-icon-svg g-icon_size_14 g-icon-m_info',
                'ui'        => 'form-info',
                'text'      => Gm::t(BACKEND, 'Help'),
                'handler'   => 'loadWidget',
                'handlerArgs' => [
                    'route' => '@backend/guide/modal/view?name=' . Gm::alias('@match:module')
                ]
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию кнопки "Справка" (Gm.view.grid.button.Button GmJS).
     * 
     * Обработчик кнопки 'loadWidget' вызывает виджет справочной информации текущего 
     * компонента (модуля или его расширения).
     * 
     * Применяет ui 'form-info'. 
     * 
     * @see Ext::component()
     * 
     * @param array<string, mixed>|array{subject:string, component:string} $initialConfig 
     *     Конфигурация кнопки, передаваемая в конструктор во время ёё создания, где
     *     параметры:
     *     - 'subject', тема помощи;
     *     - 'component', сигнатура компонента, например: 'module:gm.foobar', 'extension:gm.foobar'.
     *     Если эти параметры указаны, то они будут определы по умолчанию.
     * 
     * @return array<string, mixed>
     */
    public static function helpButton(array $params = []): array
    {
        // тема помощи
        if (!isset($params['subject']))
            $subject = $params['subject'] = 'form';
        else
            $subject = $params['subject'];
        // сигнатура компонента
        if (!isset($params['component']))
            $component = $params['component'] = Gm::$app->module ? Gm::$app->module->getId(true) : '';
        else
            $component = $params['component'];

        return static::component([
            'xtype'       => 'button',
            'text'        =>  Gm::t(BACKEND, 'Help'),
            'iconCls'     => 'g-icon-svg g-icon_size_14 g-icon-m_info',
            'ui'          => 'form-info',
            'handler'     => 'loadWidget',
            'handlerArgs' => [
                'closeWindow' => false,
                'route'       => Gm::alias('@backend', '/guide/modal/view?component=' . $component . '&subject=' . $subject)
            ]
        ], $params);
    }

    /**
     * Возвращает конфигурацию кнопки "Отмена" (Gm.view.grid.button.Button GmJS).
     * 
     * Обработчик кнопки 'onFormCancel' вызывает закрытие окна формы.
     * 
     * Применяет ui 'form-close'.
     * 
     * @see Ext::component()
     * 
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function cancelButton(array $initialConfig = []): array
    {
        return static::component(
            [
                'xtype'   => 'button',
                'ui'      => 'form-close',
                'text'    => Gm::t(BACKEND, 'Cancel'),
                'handler' => 'onFormCancel'
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию кнопки "Закрыть" (Gm.view.grid.button.Button GmJS).
     * 
     * Обработчик кнопки 'onFormCancel' вызывает закрытие окна формы.
     * 
     * Применяет ui 'form-close'.
     * 
     * @see Ext::component()
     * 
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function closeButton(array $initialConfig = []): array
    {
        return static::component(
            [
                'xtype'   => 'button',
                'ui'      => 'form-close',
                'text'    => 'Ok',
                'handler' => 'onFormCancel'
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию кнопки "Сохраить" (Gm.view.grid.button.Button GmJS).
     * 
     * Обработчик кнопки 'onFormUpdate' вызывает сохранение информации.
     * 
     * Применяет ui 'form'.
     * 
     * @see Ext::component()
     * 
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function saveButton(array $initialConfig = []): array
    {
        return static::component(
            [
                'xtype'   => 'button',
                'iconCls' => 'g-icon-svg g-icon_size_14 g-icon-m_save',
                'ui'      => 'form',
                'text'    => Gm::t(BACKEND, 'Save'),
                'handler' => 'onFormUpdate'
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию кнопки "Отправить" (Gm.view.grid.button.Button GmJS).
     * 
     * Обработчик кнопки 'onFormSubmit' вызывает отправку данных формы.
     * 
     * Применяет ui 'form'.
     * 
     * @see Ext::component()
     * 
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function submitButton(array $initialConfig = []): array
    {
        return static::component(
            [
                'xtype'   => 'button',
                'iconCls' => 'g-icon-svg g-icon_size_14 g-icon-m_save',
                'ui'      => 'form',
                'text'    => Gm::t(BACKEND, 'Submit'),
                'handler' => 'onFormSubmit'
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию кнопки "Добавить" (Gm.view.grid.button.Button GmJS).
     * 
     * Обработчик кнопки 'onFormAdd' вызывает добавление информации.
     * 
     * Применяет ui 'form'.
     * 
     * @see Ext::component()
     * 
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function addButton(array $initialConfig = []): array
    {
        return static::component(
            [
                'xtype'   => 'button',
                'iconCls' => 'g-icon-svg g-icon_size_14 g-icon-m_add',
                'ui'      => 'form',
                'text'    => Gm::t(BACKEND, 'Add'),
                'handler' => 'onFormAdd'
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию кнопки "Удалить" (Gm.view.grid.button.Button GmJS).
     * 
     * Обработчик кнопки 'onFormDelete' вызывает удаление информации.
     * 
     * Применяет ui 'form-notice'.
     * 
     * @see Ext::component()
     * 
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function deleteButton(array $initialConfig = []): array
    {
        return static::component(
            [
                'xtype'   => 'button',
                'iconCls' => 'g-icon-svg g-icon_size_14 g-icon-m_trash',
                'ui'      => 'form-notice',
                'text'    => Gm::t(BACKEND, 'Delete'),
                'handler' => 'onFormDelete'
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию кнопки "Сбросить" (Gm.view.grid.button.Button GmJS).
     * 
     * Обработчик кнопки 'onFormReset' сбрасывает в первоначальное состояние значение 
     * полей формы.
     * 
     * Применяет ui 'form'.
     * 
     * @see Ext::component()
     * 
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function resetButton(array $initialConfig = []): array
    {
        return static::component(
            [
                'xtype'   => 'button',
                'iconCls' => 'g-icon-svg g-icon_size_14 g-icon-m_reset',
                'ui'      => 'form',
                'text'    => Gm::t(BACKEND, 'Reset'),
                'handler' => 'onFormReset'
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию кнопки "Просмотр" (Gm.view.grid.button.Button GmJS).
     * 
     * Обработчик кнопки 'onFormBrowse' вызывает виджет выбора информации.
     * 
     * Применяет ui 'form'.
     * 
     * @see Ext::component()
     * 
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function browseButton(array $initialConfig = []): array
    {
        return static::component(
            [
                'xtype'   => 'button',
                'ui'      => 'form',
                'text'    => Gm::t(BACKEND, 'Browse'),
                'handler' => 'onFormBrowse'
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает языковую панель вкладок с полями.
     * 
     * Каждая языковая вкладка имеет поля соответствующие установленному языку.
     * Набор полей вкладки определяется функцией обратного вызова `$tabItems`.
     * 
     * Пример работы `$tabItems`:
     * ```php
     * [
     *     [
     *         'xtype'      => 'textfield',
     *         'fieldLabel' => '#name',
     *         'name'       => 'locale[ru-RU][name]'
     *     ],
     *     ...
     * ]
     * ```
     * 
     * @param callable $tabItems Функция обратного вызова для формирования полей языковой вкладки.
     *     Конструкция: `function $tabItems(string $tag): array`, где $tag - тег языка вкладки.
     * @param bool $addDefault Если `true`, добавляет вкладку языка "По умолчанию" (по умолчанию `true`). 
     *     Использует callable $tabItems с аргументов `$tag = null`.
     * @param array $tabPanel Параметры панели вкладок Ext.tab.Panel Sencha ExtJS.
     * @param array $tab Параметры вкладки Ext.tab.Tab Sencha ExtJS.
     * 
     * @return array
     */
    public static function languageTabs(callable $tabItems, bool $addDefault = true, array $tabPanel = [], array $tab = []): array
    {
        /** @var int $index порядковый номер вкладки */
        $index = 0;
        /** @var array $tabPanel панель вкладок */
        $tabPanel = static::component([
            'xtype'           => 'tabpanel',
            'activeTab'       => 0,
            'enableTabScroll' => true,
            'anchor'          => '100%',
            'items'           => []
        ], $tabPanel);
        /** @var array $tab вкладка */
        $tab = array_merge([
            'bodyPadding' => '10 10 10 5',
            'iconCls'     => 'g-icon-svg g-icon-m_language g-icon-m_color_white',
            'defaults'    => [
                'labelAlign' => 'right',
                'anchor'     => '100%'
            ]
        ], $tab);
        // добавить вкладку по умолчанию
        if ($addDefault) {
            $tab['title']   = '#Default';
            $tab['tooltip'] = '#Used if there are no other localizations';
            $tab['items']   = $tabItems(null);
            $tabPanel['items'][] = $tab;
        }
        /** @var array $languages Установленные языки */
        $languages = Gm::$services->getAs('language')->available->getAll();
        foreach ($languages as $locale => $language) {
            // активируем вкладку с языком по умолчанию
            if (Gm::$app->language->tag === $locale) {
                $tabs['activeTab'] = $index;
            }
            // заголовок вкладки
            $tab['title']   = $language['shortName'];
            $tab['tooltip'] = $language['shortName'] . ' (' . $language['tag'] . ')';
            $tab['items']   = $tabItems($language['tag']);
            // добавляем вкладку
            $tabPanel['items'][] = $tab;
            $index++;
        }
        return $tabPanel;
    }
}
