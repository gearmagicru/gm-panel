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
use Gm\Helper\Html;
use Gm\Theme\Info\ViewsInfo;
use Gm\Filesystem\Filesystem;

/**
 * Класс ExtCombo является вспомогательным классом Ext и предоставляет набор статических 
 * методов для генерации часто используемых конфигураций компонентов Sencha ExtJS, таких 
 * как выпадающий список (Ext.form.field.ComboBox Sencha ExtJS).
 * 
 * @link https://docs.sencha.com/extjs/5.1.3/api/Ext.form.field.ComboBox.html
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Helper
 * @since 1.0
 */
class ExtCombo extends Ext
{
    /**
     * Возвращает конфигурацию выпадающего списка (Ext.form.field.ComboBox Sencha ExtJS) 
     * с локальной загрузкой данных.
     * 
     * Если необходимо задать шаблон элементу списка, то параметр 'listConfig' 
     * инициализации компонента должен иметь вид:
     * ```php
     * [
     *     'listConfig' => [
     *         'itemTpl' => 'шаблон элемента',
     *         //...
     *     ]
     * ]
     * ```
     * 
     * @param string $label Метка поля.
     * @param string $name Имя поля в параметрах `name` и `hiddenName` конфигурации компонента.
     * @param array $store Локальное хранилище элементов компонента, имеет параметры:
     *     - `fields` string[] имена полей;
     *     - `data` string|int[][] массивы значений. 
     * @param array $initialConfig Конфигурация компонента, передаваемая в конструктор 
     *     во время его создания (по умолчанию `[]`).
     * 
     * @return array
     */
    public static function local(string $label,  string $name, array $store, array $initialConfig = []): array 
    {
        $config = [
            'xtype'          => 'combobox',
            'fieldLabel'     => $label,
            'name'           => $name,
            'hiddenName'     => $name,
            'store'          => $store,
            'forceSelection' => true,
            'displayField'   => 'name',
            'valueField'     => 'id',
            'minChars'       => 0,
            'queryParam'     => 'q',
            'queryMode'      => 'local',
            'editable'       => false,
            'anchor'         => '100%',
            'labelAlign'     => 'right',
            'allowBlank'     => true
        ];

        if (isset($initialConfig['width'])) {
            unset($config['anchor']);
        }
        return static::component($config, $initialConfig);
    }

    /**
     * Возвращает конфигурацию компонента выпадающего списка с удаленной загрузкой данных.
     * 
     * @param string $label Метка поля.
     * @param string $name Имя поля в параметрах `name` и `hiddenName` конфигурации компонента.
     * @param array $store Хранилище элементов компонента, параметры соответствуют Ext.data.Store Sencha ExtJS.
     * @param array $initialConfig Конфигурация компонента, передаваемая в конструктор 
     *     во время его создания (по умолчанию `[]`).
     * 
     * @return array
     */
    public static function remote(string $label, string $name, array $store, array $initialConfig = []): array
    {
        if (!isset($store['fields'])) {
            $store['fields'] = ['id', 'name'];
        }
        $proxy = &$store['proxy'];
        if (!isset($proxy['type'])) {
            $proxy['type'] = 'ajax';
        }
        if (!isset($proxy['url'])) {
            $proxy['url'] = Url::toMatch('trigger/combo');
        } else
            if (is_array($proxy['url'])) {
                $call = 'to' . $proxy['url'][1];
                $proxy['url'] = Url::$call($proxy['url'][0]);
            }
        if (!isset($proxy['reader'])) {
            $proxy['reader'] = ['type' => 'json', 'rootProperty' => 'data'];
        }
        return static::component([
            'xtype'          => 'combobox',
            'fieldLabel'     => $label,
            'name'           => $name,
            'hiddenName'     => $name,
            'store'          => $store,
            'forceSelection' => true,
            'displayField'   => 'name',
            'valueField'     => 'id',
            'minChars'       => 3,
            'queryParam'     => 'q',
            'queryMode'      => 'remote',
            'editable'       => true,
            'anchor'         => '100%',
            'labelAlign'     => 'right',
            'allowBlank'     => true
        ], $initialConfig);
    }

    /**
     * Возвращает конфигурацию компонента выпадающего списка с удаленной загрузкой данных.
     * 
     * @param string $label Метка поля.
     * @param string $name Имя поля в параметрах `name` и `hiddenName` конфигурации компонента.
     * @param array $store Хранилище элементов компонента, параметры соответствуют Ext.data.Store Sencha ExtJS.
     * @param array $initialConfig Конфигурация компонента, передаваемая в конструктор 
     *     во время его создания (по умолчанию `[]`).
     * 
     * @return array
     */
    public static function treeRemote(string $label, string $name, array $store, array $initialConfig = []): array
    {
        $valueField   = 'id';
        $displayField = 'name';

        if (!isset($store['fields'])) {
            $store['fields'] = [$valueField, $displayField];
        } else {
            $valueField   = $store['fields'][0] ?? $valueField;
            $displayField = $store['fields'][1] ?? $displayField;
        }

        $proxy = &$store['proxy'];
        if (!isset($proxy['type'])) {
            $proxy['type'] = 'ajax';
        }
        if (!isset($proxy['url'])) {
            $proxy['url'] = Url::toMatch('trigger/combo');
        } else
            if (is_array($proxy['url'])) {
                $call = 'to' . $proxy['url'][1];
                $proxy['url'] = Url::$call($proxy['url'][0]);
            }
        if (!isset($proxy['reader'])) {
            $proxy['reader'] = ['type' => 'json', 'rootProperty' => 'data', 'successProperty' => 'success'];
        }

        $initialConfig['treeConfig']['store'] = $store;

        return static::component([
            'xtype'          => 'g-field-treecombobox',
            'fieldLabel'     => $label,
            'name'           => $name,
            'hiddenName'     => $name,
            'displayField'   => $displayField,
            'valueField'     => $valueField,
            'queryParam'     => 'q',
            'queryMode'      => 'remote'
        ], $initialConfig);
    }

    /**
     * Возвращает конфигурацию компонента выпадающего списка c удаленной загрузкой 
     * данных и указанием имени триггера выпадающего списка.
     * 
     * @param string $label Метка поля.
     * @param string $name Имя поля в параметрах `name` и `hiddenName` конфигурации компонента.
     * @param string $triggerName Имя триггера.
     * @param bool $noneRow В первой строке выпадающего списка будет элемент "[ без выбора ]" 
     *     (по умолчанию `true`).
     * @param string|array|null $url URL-адрес источника данных (по умолчанию `null`).  
     *     Имеет вид:
     *     - [$route, $call], где: 
     *         - $route string маршрут запроса;
     *         - $call string имя вызываемого метода {@see \Gm\Helper\Url};
     *     - $url string, полный URL-адрес.
     * @param array $initialConfig Конфигурация компонента, передаваемая в конструктор 
     *     во время его создания (по умолчанию `[]`).
     * 
     * @return array
     */
    public static function trigger(
        string $label, 
        string $name, 
        string $triggerName, 
        bool $noneRow = true, 
        string|array|null $url = null,  
        array $initialConfig = []
    ): array
    {
        $store = [
            'proxy' => [
                'url'         => $url ?: ['trigger/combo', 'match'],
                'extraParams' => ['combo' => $triggerName, 'noneRow' => $noneRow ? 1 : 0]
            ]
        ];
        return static::remote($label, $name, $store, $initialConfig);
    }

    /**
     * Возвращает конфигурацию компонента выпадающего списка c удаленной загрузкой 
     * данных и указанием имени триггера выпадающего списка изображений.
     * 
     * @param string $label Метка поля.
     * @param string $name Имя поля в параметрах `name` и `hiddenName` конфигурации компонента.
     * @param string $triggerName Имя триггера.
     * @param string|array|null $url URL-адрес источника данных (по умолчанию `null`).  
     *     Имеет вид:
     *     - [$route, $call], где: 
     *         - $route string маршрут запроса;
     *         - $call string имя вызываемого метода {@see \Gm\Helper\Url};
     *     - $url string, полный URL-адрес.
     * @param array $initialConfig Конфигурация компонента, передаваемая в конструктор 
     *     во время его создания (по умолчанию `[]`).
     * 
     * @return array
     */
    public static function imageTrigger(
        string $label, 
        string $name, 
        string $triggerName, 
        string|array|null $url = null, 
        array $initialConfig = []
    ): array
    {
        $store = [
            'fields' => ['id', 'name', 'img'],
            'proxy'  => [
                'url'         => $url ?: ['trigger/combo', 'match'],
                'extraParams' => ['combo' => $triggerName]
            ]
        ];
        $initialConfig['listConfig']['itemTpl'] = ['<div class="g-boundlist-item g-boundlist-item_offset" data-qtip="{name}">{img}{name}</div>'];
        return static::remote($label, $name, $store, $initialConfig);
    }

    /**
     * Возвращает массив элементов компонента выпадающего списка.
     * 
     * @param array $arr Массив элементов вида: ["key" => "value",...].
     * @param bool $withNoneSelect Если значение `true`, 1-й элемент списка будет 
     *     "[ без выбора ]" (по умолчанию `false`).
     * @param bool $indexed Если значение `true`, массив элементов индексированный, 
     *     иначе - ассоциативный (по умолчанию `false`).
     * @param string $id Идентификатор элемента массива (по умолчанию 'id').
     * @param string $name Имя элемента массива (по умолчанию 'name').
     * 
     * @return array
     */
    public static function store(
        array $arr, 
        bool $withNoneSelect = false, 
        bool $indexed = false, 
        string $id = 'id', 
        string $name = 'name'
    ): array
    {
        $rows = $withNoneSelect ? static::noneItem(true) : [];
        if ($arr) {
            if ($indexed)
                foreach ($arr as $key => $value) {
                    $rows[] = [$key, $value];
                }
            else
                foreach ($arr as $key => $value) {
                    $rows[] = [$id => $key, $name => $value];
                }
        }
        return $rows;
    }

    /**
     * Возвращает конфигурацию выпадающего списка выбора сторон (FRONTEND, BACKEND) 
     * приложения.
     * 
     * @param string $label Метка поля.
     * @param string $name Имя поля в параметрах `name` и `hiddenName` конфигурации компонента.
     * @param bool $noneRow В первой строке выпадающего списка будет элемент "[ без выбора ]" 
     *     (по умолчанию `false`).
     * @param array $initialConfig Конфигурация компонента, передаваемая в конструктор 
     *     во время его создания (по умолчанию `[]`).
     * 
     * @return array
     */
    public static function side(
        string $label,  
        string $name, 
        bool $noneRow = false, 
        array $initialConfig = []
    ): array
    {
        $store = [
            'fields' => ['id', 'name'],
            'data'   => [
                ['id' => FRONTEND, 'name' => Gm::t(BACKEND, FRONTEND_NAME)],
                ['id' => BACKEND,  'name' => Gm::t(BACKEND, BACKEND_NAME)]
            ]
        ];

        if ($noneRow) {
            array_unshift($store['data'], ['null', Gm::t(BACKEND, '[None]'), '']);
        }
        return static::local($label,  $name, $store, $initialConfig);
    }

    /**
     * Возвращает конфигурацию выпадающего списка выбора видов шаблонов.
     * 
     * @param string $label Метка поля.
     * @param string $name Имя поля в параметрах `name` и `hiddenName` конфигурации компонента.
     * @param array $initialConfig Конфигурация компонента, передаваемая в конструктор 
     *     во время его создания (по умолчанию `[]`).
     * 
     * @return array
     */
    public static function viewTypes(string $label, string $name, array $initialConfig = []): array
    {
        $store = [
            'fields' => ['id', 'name'],
            'data'   => [
                ['id' => 'null', 'name' => '#none'],
            ]
        ];
        /** @var \Gm\Theme\Info\ViewsInfo $info */
        $info = Gm::$app->theme->getViewsInfo();
        if ($info) {
            $types = $info->getTypes(true);
            foreach ($types as $type => $typeName) {
                $store['data'][] = ['id' => $type, 'name' => $typeName];
            }
        }
        return static::local($label, $name, $store, $initialConfig);
    }

    /**
     * Описание шаблонов для текущей темы одной из сторон.
     * 
     * @see ExtCombo::getViewsInfo()
     * 
     * @var array
     */
    protected static $viewsInfo = [];

    /**
     * Возвращает описание шаблонов для текущей темы указанной стороны.
     * 
     * @param string $side Сторона: `BACKEND`, `FRONTEND`.
     * 
     * @return ViewsInfo
     */
    protected static function getViewsInfo(string $side): ViewsInfo
    {
        if (!isset(static::$viewsInfo[$side])) {
            /** @var \Gm\Theme\Theme $theme */
            $theme = Gm::$app->createThemeBySide($side);
            /** @var ViewsInfo $viewsInfo */
            $viewsInfo = $theme->getViewsInfo();
            $viewsInfo->load();

            static::$viewsInfo[$side] = $viewsInfo;
        }
        return static::$viewsInfo[$side];
    }

    /**
     * Возвращает конфигурацию выпадающего списка выбора шаблонов темы.
     * 
     * @see ViewsInfo::find()
     * 
     * @param string $label Метка поля.
     * @param string $name Имя поля в параметрах `name` и `hiddenName` конфигурации компонента.
     * @param string $side Сторона: `BACKEND`, `FRONTEND`.
     * @param array $filter Свойства в описании шаблона, которые необходимо найти,
     *     например: `['type' => 'page', ...]`.
     * @param array $append Добавляет в возвращемый результат элементы (по умолчанию `[]`).
     * @param array $initialConfig Конфигурация компонента (выпадающий список), передаваемая в 
     *     конструктор во время его создания (по умолчанию `[]`).
     * 
     * @return array
     */
    public static function themeViews(
        string $label, 
        string $name, 
        string $side, 
        array $filter, 
        array $append = [], 
        array $initialConfig = []
    ): array
    {
        /** @var ViewsInfo $viewsInfo */
        $viewsInfo = self::getViewsInfo($side);

        $data = $viewsInfo->find($filter, true, ['view', 'description'], false);
        if ($append) {
            array_unshift($data, $append);
        }
        $store = [
            'fields' => ['view', 'description'],
            'data'   => $data
        ];

        $initialConfig['forceSelection'] = false;
        $initialConfig['editable'] = true;
        $initialConfig['displayField'] = 'description';
        $initialConfig['valueField'] = 'view';
        return static::local($label, $name, $store, $initialConfig);
    }

    /**
     * Формирует первый элемент компонента выпадающего списка со значением "[ без выбора ]".
     * 
     * @param bool $withArray Если значение `true`, возвращает массив с первым элементом. 
     *     Иначе, первый элемент.
     * 
     * @return array
     */
    public static function noneItem(bool $withArray = false): array
    {
        $item = ['null', Gm::t(BACKEND, '[None]')];
        return $withArray ? [$item] : $item;
    }

    /**
     * Возвращает конфигурацию выпадающего списка установленных модулей.
     * 
     * @param string $label Метка поля.
     * @param string $name Имя поля в параметрах `name` и `hiddenName` конфигурации компонента.
     * @param string $valueField Имя поля возвращающие значение выпадающего списка (по умолчанию 'id').
     * @param array $initialConfig Конфигурация компонента, передаваемая в конструктор 
     *     во время его создания (по умолчанию `[]`).
     * 
     * @return array
     */
    public static function modules(
        string $label,  
        string $name, 
        string $valueField = 'id', 
        array $initialConfig = []
    ): array 
    {
        $store = [
            'fields' => ['name', 'description', 'route', 'id', 'rowId', 'icon'],
            'data'   => []
        ];
        /** @var array $info Конфигурации установленных модулей */
        $info = Gm::$app->modules->getRegistry()->getListInfo();
        if ($info) {
            foreach ($info as $rowId => $params) {
                $store['data'][] = [
                    $params['name'], $params['description'], $params['route'], $params['id'],
                    $params['rowId'], $params['smallIcon']
                ];
            }
        }
        return static::component([
            'xtype'          => 'combobox',
            'fieldLabel'     => $label,
            'name'           => $name,
            'hiddenName'     => $name,
            'store'          => $store,
            'forceSelection' => true,
            'displayField'   => 'name',
            'valueField'     => $valueField,
            'minChars'       => 0,
            'queryParam'     => 'q',
            'queryMode'      => 'local',
            'editable'       => true,
            'anchor'         => '100%',
            'labelAlign'     => 'right',
            'allowBlank'     => true,
            'listConfig' => [
                'itemTpl' => Html::div(
                    Html::img('{icon}', ['align' => 'absmiddle'], false) . ' {name}',
                    ['data-qtip' => '{description}']
                )
            ]
        ], $initialConfig);
    }

    /**
     * Возвращает конфигурацию выпадающего списка установленных расширений модулей.
     * 
     * @param string $label Метка поля.
     * @param string $name Имя поля в параметрах `name` и `hiddenName` конфигурации компонента.
     * @param string $valueField Имя поля возвращающие значение выпадающего списка (по умолчанию 'id').
     * @param array $initialConfig Конфигурация компонента, передаваемая в конструктор 
     *     во время его создания (по умолчанию `[]`).
     * 
     * @return array
     */
    public static function extensions(
        string $label,  
        string $name, 
        string $valueField = 'id', 
        array $initialConfig = []
    ): array 
    {
        $store = [
            'fields' => ['name', 'description', 'route', 'id', 'rowId', 'icon'],
            'data'   => []
        ];
        /** @var array $info конфигурации установленных модулей */
        $info = Gm::$app->extensions->getRegistry()->getListInfo();
        if ($info) {
            foreach ($info as $rowId => $params) {
                $store['data'][] = [
                    $params['name'], $params['description'], $params['route'], $params['id'],
                    $params['rowId'], $params['smallIcon']
                ];
            }
        }
        return static::component([
            'xtype'          => 'combobox',
            'fieldLabel'     => $label,
            'name'           => $name,
            'hiddenName'     => $name,
            'store'          => $store,
            'forceSelection' => true,
            'displayField'   => 'name',
            'valueField'     => $valueField,
            'minChars'       => 0,
            'queryParam'     => 'q',
            'queryMode'      => 'local',
            'editable'       => true,
            'anchor'         => '100%',
            'labelAlign'     => 'right',
            'allowBlank'     => true,
            'listConfig' => [
                'itemTpl' => Html::div(
                    Html::img('{icon}', ['align' => 'absmiddle'], false) . ' {name}',
                    ['data-qtip' => '{description}']
                )
            ]
        ], $initialConfig);
    }

    /**
     * Возвращает конфигурацию выпадающего списка доступных часовых поясов.
     * 
     * @param string $label Метка поля.
     * @param string $name Имя поля в параметрах `name` и `hiddenName` конфигурации.
     * @param bool $noneRow В первой строке выпадающего списка будет элемент "[ без выбора ]" 
     *     (по умолчанию `false`).
     * @param array $initialConfig Конфигурация выпадающего списка, передаваемая в 
     *     конструкторе компонента (по умолчанию `[]`).
     * 
     * @return array
     */
    public static function timezones(
        string $label, 
        string $name, 
        bool $noneRow = false, 
        array $initialConfig = []
    ): array
    {
        $timezones = Gm::$app->formatter->getTimezones();
        // сортировка по городу и смещению в часовом поясе
        array_multisort(
            array_column($timezones, 'timezone'), SORT_ASC, 
            array_column($timezones, 'offset'), SORT_ASC, 
            $timezones
        );

        $data = $noneRow ? [['null', Gm::t(BACKEND, '[None]'), '']] : [];
        foreach ($timezones as $timezone) {
            $data[] = [$timezone['timezone'], $timezone['name'], $timezone['offsetTime']];
        }

        return static::component([
            'xtype'       => 'combobox',
            'fieldLabel'  => $label,
            'name'        => $name,
            'hiddenField' => $name,
            'store'       => [
                'fields' => ['id', 'name', 'offsetTime'],
                'data'   => $data
            ],
            'displayField' => 'id',
            'valueField'   => 'id',
            'queryMode'    => 'local',
            'editable'     => true,
            'listConfig'   => [
                'itemTpl' => [Html::tplIf('id==\'null\'', '<div>{name}</div>', '<div>(UTC {offsetTime}) {id}</div>')]
            ]
        ], $initialConfig);
    }

    /**
     * Возвращает конфигурацию выпадающего списка доступных языков.
     * 
     * @param string $label Метка поля.
     * @param string $name Имя поля в параметрах `name` и `hiddenName` конфигурации.
     * @param bool $noneRow В первой строке выпадающего списка будет элемент "[ без выбора ]" 
     *     (по умолчанию `false`).
     * @param array $initialConfig Конфигурация выпадающего списка, передаваемая в 
     *     конструкторе компонента (по умолчанию `[]`).
     * 
     * @return array
     */
    public static function languages(string $label, string $name, bool $noneRow = false, array $initialConfig = []): array
    {
        $data = $noneRow ? [['null', Gm::t(BACKEND, '[None]'), '']] : [];

        /** @var array $languages Все доступные языки */
        $languages = Gm::$app->language->available->getAll();
        foreach ($languages as $language) {
            $data[] = [
                $language['code'],
                $language['shortName'] . ' (' . $language['slug'] . ')'
            ];
        }
        return static::local(
            $label, 
            $name, 
            [
                'fields' => ['id', 'name'],
                'data'   => $data
            ], 
            $initialConfig
        );
    }

    /**
     * Возвращает конфигурацию выпадающего списка значков.
     * 
     * Внимание: $initialConfig должен иметь следующие ключи:
     * - 'path', абсолютный путь к каталогу с файлами;
     * - 'url', относительный URL-путь (без домена) к файлам значков;
     * - 'mask', маска файлов, которые необходимо найти в каталоге, например: `['*.jpg', '*.ico']`.
     * 
     * @param string $label Метка поля.
     * @param string $name Имя поля в параметрах `name` и `hiddenName` конфигурации.
     * @param bool $noneRow В первой строке выпадающего списка будет элемент "[ без выбора ]" 
     *     (по умолчанию `false`).
     * @param array $initialConfig Конфигурация выпадающего списка, передаваемая в 
     *     конструкторе компонента (по умолчанию `[]`).
     * 
     * @return array
     */
    public static function icons(string $label, string $name, bool $noneRow = false, array $initialConfig = []): ?array
    {
        static $data = [];

        /** @var array $mask Маска файлов */
        $mask = $initialConfig['mask'] ?? [];
        /** @var string $url URL-путь к файлам значков */
        $url = rtrim($initialConfig['url'] ?? '', '/');
        /** @var string $path Путь к файлам значков */
        $path = $initialConfig['path'] ?? '';
        if ($url === '' || $path === '') return null;
        unset($initialConfig['url'], $initialConfig['path']);

        if (!file_exists($path)) return null;

        // если ранее выпадающий список с указанным $path не вызывался
        if (!isset($data[$path])) {
            $rows = $noneRow ? [['null', Gm::t(BACKEND, '[None]'), '']] : [];

            $finder = Filesystem::finder();
            $finder->files()->in($path);
            if ($mask) {
                $finder->name($mask);
            }

            foreach ($finder as $info) {
                $filename = $info->getFilename();
                $rows[] = [$url . '/' . $filename, $filename];
            }
            $data[$path] = $rows;
        }
        return static::component([
            'xtype'          => 'combobox',
            'fieldLabel'     => $label,
            'name'           => $name,
            'hiddenName'     => $name,
            'store'          =>             [
                'fields' => ['id', 'name'],
                'data'   => $data[$path]
            ],
            'forceSelection' => true,
            'displayField'   => 'id',
            'valueField'     => 'id',
            'minChars'       => 0,
            'queryParam'     => 'q',
            'queryMode'      => 'local',
            'editable'       => true,
            'anchor'         => '100%',
            'labelAlign'     => 'right',
            'allowBlank'     => true,
            'listConfig' => [
                'itemTpl' => Html::div(
                    Html::img('{id}', ['align' => 'absmiddle', 'height' => '16px'], false) . ' {name}'
                )
            ]
        ], $initialConfig);
    }
}
