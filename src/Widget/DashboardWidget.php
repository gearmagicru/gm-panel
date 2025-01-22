<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Widget;

use Gm;
use Gm\Helper\Url;
use Gm\Stdlib\BaseObject;

/**
 * Класс виджета (Ext.dashboard.Panel Sencha ExtJS) интерактивной панели дашборд.
 * 
 * @see https://docs.sencha.com/extjs/5.1.3/api/Ext.dashboard.Panel.html
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Dashboard\Widget
 * @since 1.0
 */
class DashboardWidget extends BaseObject implements WidgetInterface
{
    /**
     * Имена классов GmJS и ExtJS используемых виджетом.
     * 
     * Будут переданы в качестве метаданных для {@see \Gm\Panel\Http\Response\JsongMetadata}.
     * 
     * Пример, если `['Gm.view.window.Window', 'Gm.view.form.Panel']`, то результатом
     * будет:
     * ```php
     * $response
     *     ->meta
     *         ->add('requires', 'Gm.view.window.Window')
     *         ->add('requires', 'Gm.view.form.Panel');
     * ```
     * @var array
     */
    public array $requires = [];

    /**
     * Имена файлов таблиц стилей виджета.
     * 
     * Будут переданы в качестве метаданных для {@see \Gm\Panel\Http\Response\JsongMetadata}.
     * 
     * Например:
     * - '/foobar.css'  => 'https://domain/modules/gm.wd.foobar/assets/css/foobar.css';
     * - '/foo/bar.css' => 'https://domain/modules/gm.wd.foobar/assets/css/foo/bar.css'.
     * 
     * @var array
     */
    public array $css = [];

    /**
     * Пространство имён JS виджета.
     * 
     * Будет передан в качестве метаданных для {@see \Gm\Panel\Http\Response\JsongMetadata}.
     * 
     * Применяется для получения полного пути к JS скриптам виджета на стороне клиента.
     * 
     * Например, если пространство имён виджета 'Gm.wd.foobar', то подключаемый JS скрипт
     * должен иметь имя класса 'Gm.wd.foobar.Name' ('https://domain/modules/gm.wd.foobar/assets/js/Name.js').
     * 
     * @var string
     */
    public string $namespaceJs = '';

    /**
     * Показать инструмент "информация виджета".
     *
     * @var bool
     */
    public bool $useToolInfo = true;

    /**
     * Показать инструмент "настроить виджет".
     *
     * @var bool
     */
    public bool $useToolSettings =  false;

    /**
     * Показать инструмент "обновить виджет".
     *
     * @var bool
     */
    public bool $useToolRefresh = false;

    /**
     * Показать инструмент "закрыть виджет".
     *
     * @var bool
     */
    public bool $useToolClose = true;

    /**
     * Цвет панели виджета.
     * 
     * Например: 'none', 'white', 'green', 'red', 'blue', 'yellow'.
     * 
     * @var string
     */
    public string $color = 'white';

    /**
     * Цвет состояния панели виджета.
     * 
     * Например: 'green', 'red', 'blue', 'yellow'.
     * 
     * @var string
     */
    public string $stateColor = '';

    /**
     * Уберает цвет заголовка панели виджета.
     *
     * @var bool
     */
    public bool $headerNoColor = false;

    /**
     * Класс стиля виджета.
     * 
     * @see Widget::getCls()
     * 
     * @var string
     */
    public string $cls = '';

    /**
     * Панель инструментов виджета.
     * 
     * Доступны типы (type) инструментов для вывода: close, minimize, maximize, restore, 
     * toggle, gear, prev, next, pin, unpin, right, left, down, up, refresh, plus, 
     * minus, search, save, help, print, expand, collapse.
     * 
     * Пример объявления инструмента:
     * ```php
     * $tools[] = [
     *     'type'        => 'print', // тип инструмента
     *     'handler'     => 'onWidgetPrint', // событие инструмента
     *     'handlerArgs' => ['id' => $this->id] // параметры передаваемые в событие
     * ];
     * ```
     * Событие 'onWidgetPrint' объявляется в скрипте виджета.
     * 
     * @see Widget::getTools()
     * 
     * @var array
     */
    public array $tools = [];

    /**
     * Идентификатор виджета в базе данных.
     *
     * @var int
     */
    public int $rowId = 0;

    /**
     * Локальный путь виджета.
     * 
     * Устанавливается параметром конфигурации в конструкторе виджета.
     * Устанавливает Менеджер виджетов {@see \Gm\WidgetManager\WidgetManager::create()}.
     * 
     * Пример: '/gm/gm.wd.foobar'.
     * 
     * @var string
     */
    public string $path;

    /**
     * Абсолютный (полный) путь виджета.
     * 
     * @see DashboardWidget::getBasePath()
     * 
     * @var string
     */
    protected string $basePath;

    /**
     * Абсолютный (базовый) URL-адрес виджета.
     * 
     * @see DashboardWidget::getBaseUrl()
     * 
     * @var string
     */
    protected string $baseUrl;

    /**
     * Абсолютный (базовый) URL-адрес ресурса виджета.
     * 
     * @see DashboardWidget::getAssetsUrl()
     * 
     * @var string
     */
    protected string $assetsUrl;

    /**
     * URL-путь виджета.
     * 
     * @see DashboardWidget::getUrlPath()
     * 
     * @var string
     */
    protected string $urlPath;

    /**
     * Абсолютный (базовый) путь к ресурсам виджета.
     * 
     * @see getAssetsPath()
     * 
     * @var string
     */
    protected string $assetsPath;

    /**
     * URL-путь к подключению скриптов виджета.
     * 
     * @see DashboardWidget::getRequireUrl()
     * 
     * @var string
     */
    protected string $requireUrl;

    /**
     * Тип возвращаемого содержимого виджета.
     * 
     * Тип:
     * - html,
     * - items,
     * - store
     * - template, .
     * 
     * @var string
     */
    protected string $contentType = 'html';

    /**
     * Автозагрузка контента виджета.
     * 
     * Если значение `true`, будет выполняться загрузка контента виджета, сразу после 
     * его вывода. Выполняется на стороне клиента AJAX-запросом. Таким способом,
     * виджет возвращает свой контент методом {@see Widget::getData()}.
     * 
     * @var bool
     */
    protected bool $autoload = false;

    /**
     * Уникальный идентификатор виджета.
     * 
     * Например, 'gm.ds.foobar'.
     * 
     * @var string
     */
    protected string $id = '';

    /**
     * Параметры используемые для создания виджета. 
     *
     * @var array
     */
    protected array $options = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->init();
    }

    /**
     * Инициализация компонента.
     * 
     * Этот метод вызывается в конце конструктора после инициализации компонента 
     * заданной конфигурацией.
     * 
     * @return void
     */
    public function init(): void
    {
        $this->initTranslations();
    }

    /**
     * Выполняет подготовку к переводу сообщений модуля.
     * 
     * В качестве перевода приминяется транслятор (локализатор сообщений)
     * {@see \Gm\I18n\Translator}.
     * 
     * @return void
     */
    protected function initTranslations(): void
    {
        Gm::$app->translator
            ->addCategory($this->id, [
                'locale'   => 'auto',
                'patterns' => [
                    'text' => [
                        'basePath' => $this->getBasePath() . DS .'lang',
                        'pattern'  => 'text-%s.php'
                    ]
                ],
                'autoload' => ['text']
            ]);
    }

    /**
     * Возвращает абсолютный (базовый) URL-адрес ресурса виджета.
     * 
     * Имеет вид: "</абсолютный (базовый) URL-адрес> </assets>".  
     * Пример: 'http://domain/modules/gm/gm.wd.foobar/assets'.
     * 
     * @return string
     */
    public function getAssetsUrl(): string
    {
        if (!isset($this->assetsUrl)) {
            $this->assetsUrl = $this->getBaseUrl() . '/assets';
        }
        return $this->assetsUrl;
    }

    /**
     * Возвращает абсолютный (базовый) URL-адрес виджета.
     * 
     * Имеет вид: "<адрес хоста> </абсолютный URL-адрес модулей> </локальный путь>".  
     * Пример: 'http://domain/modules/gm/gm.wd.foobar'.
     * 
     * @return string
     */
    public function getBaseUrl(): string
    {
        if (!isset($this->baseUrl)) {
            $this->baseUrl = Gm::$app->moduleUrl . $this->getUrlPath();
        }
        return $this->baseUrl;
    }

    /**
     * Возвращает URL-путь для подключения скриптов виджета.
     * 
     * Имеет вид: "</URL-путь корня хоста> </локальный URL-путь модулей> </локальный путь> </assets>".
     * Пример: '/modules/gm/gm.wd.foobar/assets'.
     * 
     * @return string
     */
    public function getRequireUrl(): string
    {
        if (!isset($this->requireUrl)) {
            $this->requireUrl = Url::home(false) . MODULE_BASE_URL . $this->getUrlPath() . '/assets';
        }
        return $this->requireUrl;
    }

    /**
     * Возвращает URL-путь из локального пути виджета.
     *
     * Пример: '\gm\gm.wd.foobar' => '/gm/gm.wd.foobar'.
     * 
     * @return string
     */
    public function getUrlPath(): string
    {
        if (!isset($this->urlPath)) {
            $this->urlPath = OS_WINDOWS ? str_replace(DS, '/', $this->path) : $this->path;
        }
        return $this->urlPath;
    }

    /**
     * Возвращает абсолютный (базовый) путь к ресурсам виджета.
     * 
     * Имеет вид: "</абсолютный путь> </assets>".
     * Пример: '/home/host/public_html/modules/gm/gm.wd.foobar'.
     * 
     * @return string
     */
    public function getAssetsPath(): string
    {
        if (!isset($this->assetsPath)) {
            $this->assetsPath = $this->getBasePath() . DS . 'assets';
        }
        return $this->assetsPath;
    }

    /**
     * Возвращает абсолютный (полный) путь виджета.
     * 
     * Имеет вид: "</абсолютный путь к модулям> </локальный путь>".
     * Пример: '/home/host/public_html/modules/gm/gm.wd.foobar'.
     * 
     * @return string
     */
    public function getBasePath(): string
    {
        if (!isset($this->basePath)) {
            $this->basePath = Gm::$app->modulePath . $this->path;
        }
        return $this->basePath;
    }

    /**
     * Возвращает уникальный идентификатор виджета.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Перевод (локализация) сообщения.
     * 
     * @param string|array<string> $message Текст сообщения (сообщений).
     * @param array<string, mixed> $params Параметры перевода.
     * @param string $locale Код локали (на которую осуществляется перевод).
     * 
     * @return string|array<string> Локализованные сообщения или сообщение.
     */
    public function t($message, array $params = [], string $locale = '')
    {
        static $translator = null;

        if ($translator === null) {
            $translator = Gm::$services->getAs('translator');
        }
        return $translator->translate($this->id, $message, $params, $locale);
    }

    /**
     * Возвращает содержимое виджета при его автозагрузке.
     * 
     * Автозагрузка виджета выполняется на стороне клиента AJAX-запросом. Для выполнения 
     * запроса необходимо, чтобы `$autoload = true`.
     * 
     * @see Widget::$autoload
     * 
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->getContent();
    }

    /**
     * Возвращает содержимое виджета или его шаблон.
     *
     * @return mixed
     */
    public function getContent(): mixed
    {
        return '';
    }

    /**
     * Возвращает класс стиля виджета.
     *
     * @return string
     */
    protected function getCls(): string
    {
        $cls = 'g-portlet';
        if ($this->color) {
            $cls .= ' color-' . $this->color;
        }
        if ($this->headerNoColor) {
            $cls .= ' color-noheader';
        }
        if ($this->stateColor) {
            $cls .= ' color-state-' . $this->stateColor;
        }
        if ($this->cls) {
            $cls .= ' ' . $this->cls;
        }
        return $cls;
    }

    /**
     * Возвращает инструменты заголовка виджета.
     *
     * @return array
     */
    protected function getTools(): array
    {
        // использование инструментов заголовка панели
        $tools = $this->tools ?: [];
        // показать инструмент "информация виджета"
        if ($this->useToolInfo) {
            $tools[] = [
                    'type'        => 'help',
                    'handler'     => 'onToolWidgetInfo',
                    'handlerArgs' => ['id' => $this->id]
            ];
        }
        // показать инструмент "обновить виджет"
        if ($this->useToolRefresh) {
            $tools[] = [
                    'type'        => 'refresh',
                    'handler'     => 'onToolWidgetRefresh',
                    'handlerArgs' => ['id' => $this->id]
            ];
        }
        // показать инструмент "настройка виджета"
        if ($this->useToolSettings) {
            $tools[] = [
                    'type'        => 'gear',
                    'handler'     => 'onToolWidgetSettings',
                    'handlerArgs' => ['id' => $this->id]
            ];
        }
        // показать инструмент "закрыть виджета"
        if ($this->useToolClose) {
            $tools[] = [
                    'type'        => 'close',
                    'handler'     => 'onToolWidgetClose',
                    'handlerArgs' => ['id' => $this->id]
            ];
        }
        return $tools;
    }

    /**
     * Формирует содержимое виджета.
     *
     * @return array
     */
    public function run(): array
    {
        $this->options['autoload'] = $this->autoload;
        $this->options['contentType'] = $this->contentType;
        $this->options['rowId'] = $this->rowId;
        $this->options['cls'] = $this->getCls();
        $this->options['tools'] = $this->getTools();
        // переопределяем закрытие виджета через {@see Widget::$useToolClose}
        $this->options['closable'] = false;
        // если указана автозагрука контента
        if ($this->autoload) {
            if (!isset($this->listeners)) {
                $this->listeners = [];
            }
            $this->listeners['afterrender'] = 'onWidgetRender';
        }

        /** @var array|string $content */
        $content = $this->getContent();
        switch ($this->contentType) {
            case 'html':
                $this->html = $content;
                break;

            case 'template':
                $this->tpl = $content;
                break;

            default:
                $this->items = $content;
        }
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function addRequire(string $name): static
    {
        $this->requires[] = $name;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addCss(string $filename): static
    {
        $this->css[] = $filename;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setNamespaceJS(string $namespace): static
    {
        $this->namespaceJs = [$namespace, $this->jsPath()];
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function imageSrc(string $filename): string
    {
        return $this->getAssetsUrl() . '/images' . $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function cssSrc(string $filename): string
    {
        return $this->getAssetsUrl() . '/css' . $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function jsSrc(string $filename): string
    {
        return $this->getAssetsUrl() . '/js' . $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function jsPath(string $name = ''): string
    {
        return $this->getRequireUrl() . '/js' . $name;
    }

    /**
     * Проверяет, существует ли параметр виджета, когда к параметру обращаются как 
     * к свойству объекта. 
     * 
     * @param string $name Название параметра виджета.
     * 
     * @return bool Если значение `false`, параметр не существует.
     */
    public function __isset(string $name)
    {
        return isset($this->options[$name]);
    }

    /**
     * Устанавливает значение параметру виджета, когда к параметру обращаются как к 
     * свойству объекта.
     *
     * @param string $name Название параметра виджета.
     * @param mixed $value Значение параметра виджета.
     * 
     * @return void
     */
    public function __set(string $name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * Удаляет параметр виджета, когда к параметр обращаются как к свойству объекта. 
     *
     * @param string $name Название параметра виджета.
     * 
     * @return void
     */
    public function __unset(string $name)
    {
        unset($this->options[$name]);
    }

    /**
     * Возращает значение по указанному ключу элемента коллекции.
     *
     * @param string $name Название параметра виджета.
     * 
     * @return mixed Если значение `false`, параметр не существует.
     */
    public function &__get(string $name)
    {
        // чтобы не было: "Only variable references should be returned by reference"
        if (array_key_exists($name, $this->options))
            $_value = &$this->options[$name];
        else
            $_value = null;
        return $_value;
    }
}
