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
use Gm\Helper\Url;

/**
 * Класс ExtGrid является вспомогательным классом Ext и предоставляет набор статических 
 * методов для генерации часто используемых конфигураций компонентов Sencha ExtJS, таких 
 * как: Buttons (кнопки панели инструментов), Button groups (группы кнопок панели инструментов), 
 * Grid columns (столбцы сетки данных), Grid filter (фильтр сетки данных).
 * 
 * @link https://docs.sencha.com/extjs/5.1.4/api/Ext.button.Button.html
 * @link https://docs.sencha.com/extjs/5.1.4/api/Ext.grid.column.Column.html
 * @link https://docs.sencha.com/extjs/5.1.4/api/Ext.container.ButtonGroup.html
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Helper
 * @since 1.0
 */
class ExtGrid extends Ext
{
    /**
     * Базовый маршрут для вызова виджета обработчиками кнопок.
     * 
     * Если значение `null`, то будет указываться значение из
     * `Gm::alias('@match')`.
     * 
     * @var string
     */
    public static ?string $route = null;

    /**
     * Селектор компонента кнопки: 'gridpanel', 'treepanel'.
     * 
     * Селектор определяет, какой обработчик вызывать кнопки 
     * {@see ExtGrid::button()}.
     * 
     * @var string
     */
    public static string $selector = 'gridpanel';

    /**
     * Масштаб кнопок: 'small', 'medium', 'large'.
     * 
     * Масштаб применяется для определения возвращаемой конфигурации кнопки 
     * {@see ExtGrid::button()}.
     * 
     * @var string
     */
    public static string  $buttonScale = 'medium';

    /**
     * Возвращает конфигурацию групп кнопок (Ext.container.ButtonGroup Sencha ExtJS).
     * 
     * @param array<int|string, mixed> $groups Имена групп кнопок с параметрами.
     * @param array{route:string} $options Глобальные параметры для всех групп кнопок 
     *     (по умолчанию `[]`).
     * 
     * @return array<int, array<string, mixed>>
     */
    public static function buttonGroups(array $groups, array $options = []): array
    {
        if (isset($options['route']))
            static::$route = $options['route'];
        else {
            if (static::$route === null)
                static::$route = Gm::alias('@match');
        }

        $items = [];
        foreach ($groups as $index => $group) {
            if (is_string($index) && is_array($group)) {
                $name = $index . 'ButtonGroup';
                if (method_exists(static::class, $name)) {
                    $items[] = static::$name($group);
                } else
                    $items[] = $group;
            } else
            if (is_string($index) && is_string($group)) {
                $name = $index . 'ButtonGroup';
                if (method_exists(static::class, $name)) {
                    $items[] = static::$name(['items' => explode(',', $group)]);
                }
            } else
            if (is_string($group)) {
                $name = $group . 'ButtonGroup';
                if (method_exists(static::class, $name)) {
                    $items[] = static::$name();
                }
            } else
                $items[] = $group;
        }
        return $items;
    }

    /**
     * Возвращает конфигурацию группы кнопок (Ext.container.ButtonGroup Sencha ExtJS).
     * 
     * @see Ext::component()
     * 
     * @param array<string, mxied> $group Параметры конфигурации группы кнопок по 
     *     умолчанию (по умолчанию `[]`).
     * @param array $initialConfig Конфигурация группы кнопок, передаваемая в конструктор 
     *     во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>|null Возвращает значение `null`, если конфигурация группы кнопок не 
     *     имеет параметр 'items', указывающий на ёё элементы.
     */
    public static function buttonGroup(array $group = [], array $initialConfig = []): ?array
    {
        if (empty($group)) {
            $group = [
                'xtype'          => 'buttongroup',
                'columns'        => 0,
                'headerPosition' => 'bottom',
                'margin'         => '0 1 0 0',
                'bodyPadding'    => 0
            ];
        }
        $group = static::component($group, $initialConfig);
        if (isset($group['items'])) {
            $group['items']   = static::buttons($group['items']);
            $group['columns'] = sizeof($group['items']);
            return $group;
        } else
            return null;
    }

    /**
     * Возвращает конфигурацию группы кнопок "Редактирование" (Ext.container.ButtonGroup Sencha ExtJS).
     * 
     * @see Ext::buttonGroup()
     * 
     * @param array<string, mixed> $initialConfig Конфигурация группы кнопок, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>|null Возвращает значение `null`, если конфигурация группы кнопок не 
     *     имеет параметр 'items', указывающий на ёё элементы.
     */
    public static function editButtonGroup(array $initialConfig = []): ?array
    {
        return static::buttonGroup(
            [
                'xtype'          => 'buttongroup',
                'columns'        => 0,
                'title'          => Gm::t(BACKEND, 'Editing'),
                'headerPosition' => 'bottom',
                'margin'         => '0 1 0 0',
                'bodyPadding'    => 0,
                'items'          => [
                    'add' => ['caching' => true], 
                    'delete', 
                    'cleanup', 
                    'separator',
                    'edit',
                    'select',
                    'separator',
                    'refresh'
                ]
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию группы кнопок "Поиск" (Ext.container.ButtonGroup Sencha ExtJS).
     * 
     * @param array<string, mixed> $initialConfig Конфигурация группы кнопок, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>|null Возвращает значение `null`, если конфигурация группы кнопок не 
     *     имеет параметр 'items', указывающий на ёё элементы.
     */
    public static function searchButtonGroup(array $initialConfig = []): ?array
    {
        return static::buttonGroup(
            [
                'xtype'          => 'buttongroup',
                'columns'        => 0,
                'title'          => Gm::t(BACKEND, 'Search'),
                'headerPosition' => 'bottom',
                'margin'         => '0 1 0 0', 
                'bodyPadding'    => 0,
                'items'          => ['help', 'search']
            ],
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию группы кнопок "Столбцы" (Ext.container.ButtonGroup Sencha ExtJS).
     * 
     * @param array<string, mixed> $initialConfig Конфигурация группы кнопок, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>|null Возвращает значение `null`, если конфигурация группы кнопок не 
     *     имеет параметр 'items', указывающий на ёё элементы.
     */
    public static function columnsButtonGroup(array $initialConfig = []): ?array
    {
        return static::buttonGroup(
            [
                'xtype'          => 'buttongroup',
                'columns'        => 0,
                'title'          => Gm::t(BACKEND, 'Columns'),
                'headerPosition' => 'bottom',
                'margin'         => '0 1 0 0', 
                'bodyPadding'    => 0,
                'items'          => ['profiling', 'toggleColumns', 'columns']
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурации кнопок (Ext.button.Button Sencha ExtJS).
     * 
     * @param array<int|string, string|array<string, mixed>> $buttons Названия кнопок: 
     *     'separator', 'add', 'delete', 'edit', 'cleanup', 'select', 'refresh', 'help', 
     *     'search', 'profiling', 'toggleColumns','filter'. Каждому названию будет 
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
                if ($index === '|' || $index === '-')
                    $index = 'separator';
                $name = $index . 'Button';
                if (method_exists(static::class, $name)) {
                    $items[] = static::$name($button);
                } else
                    $items[] = $button;
            } else
            if (is_string($button)) {
                if ($button === '|' || $button === '-')
                    $button = 'separator';
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
     * @param array<string, mxied> $button Параметры конфигурации кнопки по умолчанию.
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function button(array $button, array $initialConfig = []): array
    {
        $button = static::component($button, $initialConfig);

        // если не указан xtype, то xtype = "g-gridbutton"
        if (!isset($button['xtype'])) {
            $button['xtype'] = 'g-gridbutton';
        }

        // если не указан селектор (действие кнопки относительно компонента - списка, дерева и т.д.)
        if (!isset($button['selector'])) {
            $button['selector'] = static::$selector;
        }

        // если необходимо кэшировать результат запроса (параметры конфигурации компонента)
        if (isset($initialConfig['caching'])) {
            $button['handlerArgs']['caching'] = $initialConfig['caching'];
            unset($initialConfig['caching']);
        }

        // масштаб кнопки
        switch (static::$buttonScale) {
            case 'large':

            case 'medium':
                $button['scale']      = 'small';
                $button['minHeight']  = 80;
                $button['width']      = 63;
                $button['iconAlign']  = 'top';
                $button['arrowAlign'] = 'bottom';
                if (isset($button['iconCls'])) {
                    $button['iconCls'] = $button['iconCls'] . '_medium g-icon_size_button_medium';
                }
                if (isset($button['activeIconCls'])) {
                    $button['activeIconCls'] = $button['activeIconCls'] . '_medium g-icon_size_button_medium';
                }
                if (isset($button['inactiveIconCls'])) {
                    $button['inactiveIconCls'] = $button['inactiveIconCls'] . '_medium g-icon_size_button_medium';
                }
                break;

            case 'small':
                $button['scale']       = 'small';
                $button['ui']          = 'form-tool';
                $button['margin']      = '0 1 0 1';
                $button['iconAlign']   = 'left';
                $button['arrowAlign']  = 'right';
                $button['tooltipType'] = 'title';
                unset($button['text']);
                if (isset($button['iconCls'])) {
                    $button['iconCls'] = $button['iconCls'] . '_small g-icon_size_button_small';
                }
                if (isset($button['activeIconCls'])) {
                    $button['activeIconCls'] = $button['activeIconCls'] . '_small g-icon_size_button_small';
                }
                if (isset($button['inactiveIconCls'])) {
                    $button['inactiveIconCls'] = $button['inactiveIconCls'] . '_small g-icon_size_button_small';
                }
                break;
        }
        return $button;
    }

    /**
     * Возвращает конфигурацию разделителя кнопок (Ext.menu.Separator Sencha ExtJS).
     * 
     * @see Ext::component()
     * @link https://docs.sencha.com/extjs/5.1.4/api/Ext.menu.Separator.html
     * 
     * @param array<string, mixed> $initialConfig Конфигурация разделителя кнопки, 
     *     передаваемая в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function separatorButton(array $initialConfig = []): array
    {
        return static::component(
            [
                'xtype'   => 'menuseparator',
                'baseCls' => 'g-grid__toolbar-separator g-grid__toolbar-separator_' . static::$buttonScale
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию кнопки "Добавить" (Gm.view.grid.button.Button GmJS).
     * 
     * Вызывает виджет для добавления информации. Базовый маршрут виджета определяется из 
     * {@see ExtGrid::$route}.
     * 
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function addButton(array $initialConfig = []): array
    {
        return static::button(
            [
                'text'        => Gm::t(BACKEND, 'Add'),
                'tooltip'     => Gm::t(BACKEND, 'Adding a new record'),
                'iconCls'     => 'g-icon-svg g-icon_grid-add',
                'handlerArgs' => ['route' => static::$route . '/form'],
                'handler'     => 'loadWidget'
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию кнопки "Удалить" (Gm.view.grid.button.Button GmJS).
     * 
     * Вызывает обработчик сетки 'onDeleteRecords' для удаления записей.
     * 
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function deleteButton(array $initialConfig = []): array
    {
        return static::button(
            [
                'text'          =>  Gm::t(BACKEND, 'Delete'),
                'tooltip'       =>  Gm::t(BACKEND, 'Delete selected records'),
                'iconCls'       => 'g-icon-svg g-icon_grid-delete',
                'confirm'       => true,
                'handler'       => 'onDeleteRecords',
                'msgConfirm'    => Gm::t(BACKEND, 'Are you sure you want to delete posts'),
                'msgMustSelect' => Gm::t(BACKEND, 'You need to select records')
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию кнопки "Редактировать" (Gm.view.grid.button.Button GmJS).
     * 
     * Вызывает виджет для редактировнаия выбранной записи из сетки данных.
     * 
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function editButton(array $initialConfig = []): array
    {
        return static::button(
            [
                'xtype'    => 'g-gridbutton-edit',
                'iconCls'  => 'g-icon-svg g-icon_grid-edit',
                'text'     =>  Gm::t(BACKEND, 'Edit')
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию кнопки "Очистить" (Gm.view.grid.button.Button GmJS).
     * 
     * Вызывает обработчик сетки 'onDeleteAllRecords' для удаления всех записей.
     * 
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function cleanupButton(array $initialConfig = []): array
    {
        return static::button(
            [
                'iconCls'         => 'g-icon-svg g-icon_grid-cleanup',
                'text'            =>  Gm::t(BACKEND, 'Clear'),
                'tooltip'         =>  Gm::t(BACKEND, 'Deleting all records'),
                'confirm'         => true,
                'twiceConfirm'    => true,
                'msgConfirm'      =>  Gm::t(BACKEND, 'Are you sure you want to delete all records'),
                'msgTwiceConfirm' =>  Gm::t(BACKEND, 'You are sure of this'),
                'handler'         => 'onDeleteAllRecords'
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию кнопки "Выделить" (Gm.view.grid.button.Button GmJS).
     * 
     * Вызывает обработчик сетки 'onSelection' для выделения записей.
     * 
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function selectButton(array $initialConfig = []): array
    {
        return static::button(
            [
                'xtype'    => 'g-gridbutton-split',
                'text'     =>  Gm::t(BACKEND, 'Select'),
                'tooltip'  =>  Gm::t(BACKEND, 'Selecting entries in the list'),
                'iconCls'  => 'g-icon-svg g-icon_grid-select',
                'menu'     => [
                    'mouseLeaveDelay' => 0,
                    'items' => [
                        [
                            'text'    =>  Gm::t(BACKEND, 'Select all'),
                            'handler' => 'onSelection',
                            'iconCls' => 'g-icon-svg g-icon_grid-select-all_small'
                        ],
                        [
                            'text'    =>  Gm::t(BACKEND, 'Remove selection'),
                            'handler' => 'onRemoveSelection',
                            'iconCls' => 'g-icon-svg g-icon_grid-select-remove_small'
                        ],
                        [
                            'text'    =>  Gm::t(BACKEND, 'Invert Selection'),
                            'handler' => 'onInvertSelection',
                            'iconCls' => 'g-icon-svg g-icon_grid-select-invert_small'
                        ]
                    ]
                ],
                'handler' => 'onSelection'
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию кнопки "Обновить" (Gm.view.grid.button.Button GmJS).
     * 
     * Вызывает обработчик сетки 'onReload' для обновления записей.
     * 
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function refreshButton(array $initialConfig = []): array
    {
        return static::button(
            [
                'text'     =>  Gm::t(BACKEND, 'Refresh'),
                'iconCls'  => 'g-icon-svg g-icon_grid-refresh',
                'handler'  => 'onReload'
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию кнопки "Справка" (Gm.view.grid.button.Button GmJS).
     * 
     * Вызывает обработчик сетки 'loadWidget' для загрузки виджета справки.
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
            $subject = $params['subject'] = 'grid';
        else
            $subject = $params['subject'];
        // сигнатура компонента
        if (!isset($params['component']))
            $component = $params['component'] = Gm::$app->module ? Gm::$app->module->getId(true) : '';
        else
            $component = $params['component'];

        return static::button(
            [
                'text'        =>  Gm::t(BACKEND, 'Reference'),
                'iconCls'     => 'g-icon-svg g-icon_grid-help',
                'handlerArgs' => [
                    'route' => Gm::alias('@backend', '/guide/modal/view?component=' . $component . '&subject=' . $subject)
                ],
                'handler'     => 'loadWidget'
            ], 
            $params
        );
    }

    /**
     * Возвращает конфигурацию кнопки "Поиск" (Gm.view.grid.button.Button GmJS) панели 
     * инструментов сетки.
     * 
     * Вызывает обработчик сетки 'loadWidget' для вызова видежта поиска записей.
     * 
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function searchButton(array $initialConfig = []): array
    {
        return static::button(
            [
                'text'        => Gm::t(BACKEND, 'Search'),
                'iconCls'     => 'g-icon-svg g-icon-svg g-icon_grid-search',
                'handlerArgs' => ['route' => static::$route . '/search'],
                'handler'     => 'loadWidget'
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию кнопки "Профилирование" (Gm.view.grid.button.Button GmJS) панели 
     * инструментов сетки.
     * 
     * Вызывает обработчик сетки 'onProfilingRecord' для вызова навигации по записям.
     * 
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function profilingButton(array $initialConfig = []): array
    {
        return static::button(
            [
                'text'            => Gm::t(BACKEND, 'Profiling'),
                'tooltip'         => Gm::t(BACKEND, 'Record profiling'),
                'iconCls'         => 'g-icon-svg g-icon_grid-profiling-off',
                'activeIconCls'   => 'g-icon-svg g-icon_grid-profiling-on',
                'inactiveIconCls' => 'g-icon-svg g-icon_grid-profiling-off',
                'enableToggle'    => true,
                'toggleHandler'   => 'onProfilingRecord'
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию кнопки "Скрыть / показать столбцы" (Gm.view.grid.button.Button GmJS) 
     * панели инструментов сетки.
     * 
     * Вызывает обработчик сетки 'onToggleColumns' для просмотра столбцов.
     * 
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function toggleColumnsButton(array $initialConfig = []): array
    {
        return static::button(
            [
                'textShow'        => Gm::t(BACKEND, 'Show'),
                'textHide'        => Gm::t(BACKEND, 'Hide'),
                'text'            => Gm::t(BACKEND, 'Show'),
                'tooltip'         => Gm::t(BACKEND, 'Toggle columns'),
                'iconCls'         => 'g-icon-svg g-icon_grid-togglec-off',
                'activeIconCls'   => 'g-icon-svg g-icon_grid-togglec-on',
                'inactiveIconCls' => 'g-icon-svg g-icon_grid-togglec-off',
                'enableToggle'    => true,
                'toggleHandler'   => 'onToggleColumns'
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию кнопки "Фильтр" (Gm.view.grid.button.Button GmJS) 
     * панели инструментов сетки.
     * 
     * Вызывает виджет для фильтрации записей.
     * 
     * @param array<string, mixed> $initialConfig Конфигурация кнопки, передаваемая 
     *     в конструктор во время ёё создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function filterButton(array $initialConfig = []): array
    {
        return static::button(
            [
                'xtype'           => 'g-gridbutton-filter',
                'iconCls'         => 'g-icon-svg g-icon_grid-filter-off',
                'activeIconCls'   => 'g-icon-svg g-icon_grid-filter-on',
                'inactiveIconCls' => 'g-icon-svg g-icon_grid-filter-off',
                'text'            =>  Gm::t(BACKEND, 'Filter'),
                'tooltip'         =>  Gm::t(BACKEND, 'Filtering records')
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию столбца (Ext.grid.column.Column Sencha ExtJS) сетки
     * данных.
     * 
     * @see Ext::component()
     * @link https://docs.sencha.com/extjs/5.1.3/api/Ext.grid.column.Column.html
     * 
     * @param string $text Заголовок столбца.
     * @param string $dataIndex Название поля из источника данных.
     * @param array<string, mixed> $initialConfig Конфигурация столбца, передаваемая 
     *     в конструктор во время его создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function column(string $text, string $dataIndex, array $initialConfig =[]): array
    {
        return static::component(
            [
                'text'      => $text,
                'dataIndex' => $dataIndex
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию столбца "Номер" (Ext.grid.column.RowNumberer Sencha ExtJS) сетки
     * данных.
     * 
     * @link https://docs.sencha.com/extjs/5.1.3/api/Ext.grid.column.RowNumberer.html
     * 
     * @param array<string, mixed> $initialConfig Конфигурация столбца, передаваемая 
     *     в конструктор во время его создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function columnNumberer(array $initialConfig = [])
    {
        return static::component(
            [
                'xtype' => 'rownumberer',
                'width' => 50
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию столбца "Действие" (Gm.view.grid.column.MenuAction GmJS) сетки
     * данных.
     * 
     * Применяется для вывода контекстного меню управления записью.
     * 
     * @param array<string, mixed> $initialConfig Конфигурация столбца, передаваемая 
     *     в конструктор во время его создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function columnAction(array $initialConfig = [])
    {
        return static::component(['xtype' => 'g-gridcolumn-menuaction'], $initialConfig);
    }

    /**
     * Возвращает значок столбцу.
     * 
     * @see Ext::renderIcon()
     * 
     * @param string $icon Ресурс значка.
     * @param string $type Тип значка {@see Ext::renderIcon()} (по умолчанмю 'svg').
     * @param int $iconSize Размер значка в пкс (по умолчанмю '16').
     * @param string $iconColor Цвет значка CSS, определяется текущей темой (по умолчанмю 'default').
     * 
     * @return string
     */
    public static function columnIcon(string $icon, string  $type = 'svg', int $iconSize = 16, string $iconColor = 'default'): string
    {
        if ($type === 'svg') {
            if ($iconColor === '')
                $icon = " g-icon_size_$iconSize $icon";
            else
                $icon = " g-icon_size_$iconSize $icon g-icon-m_color_$iconColor";
        }
        return static::renderIcon($icon, $type);
    }

    /**
     * Возвращает значок "информация" столбцу.
     * 
     * @see Ext::renderIcon()
     * 
     * @param string $title Заголовок столбца.
     * 
     * @return string
     */
    public static function columnInfoIcon(string $title = ''): string
    {
        return static::renderIcon(' g-icon_size_14 g-icon_gridcolumn-info', 'svg') . ' ' . $title;
    }

    /**
     * Возвращает конфигурацию виджета для фильтрации сетки данных.
     * 
     * @param array<int, array> $items Конфигурации элементов формы для указания 
     *     значений фильтру.
     * @param array<string, mixed> $initialConfig Конфигурация фильтра, передаваемая 
     *     в конструктор во время его создания (по умолчанию `[]`).
     * 
     * @return array<string, mixed>
     */
    public static function popupFilter(array $items, array $initialConfig = []): array
    {
        $filter = [
            'cls'      => 'g-popupform-filter',
            'width'    => 400,
            'height'   => 'auto',
            'action'   =>  Url::toMatch('grid/filter'),
            'defaults' => ['labelWidth' => 100],
            'items'    => $items
        ];
        return [
            'form' => $initialConfig ? array_merge($filter, $initialConfig) : $filter
        ];
    }
}
