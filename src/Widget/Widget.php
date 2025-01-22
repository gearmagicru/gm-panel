<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Widget;

use Gm;
use Gm\Helper\Str;
use Gm\Helper\Arr;
use Gm\Helper\Json;
use Gm\Theme\Theme;
use Gm\View\ClientScript;
use Gm\Stdlib\Collection;
use Gm\Mvc\Module\Module;
use Gm\Mvc\Plugin\Plugin;
use Gm\Mvc\Extension\Extension;
use Gm\View\Exception\TemplateNotFoundException;

/**
 * Базовый виджета для формирования элементов интерфейса Панели управления.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class Widget extends BaseWidget
{
    /**
     * Модуль, в представление которого, будет выводиться содержимое виджета.
     * 
     * Если значение не установлено, то будет использоваться текущий модуль приложения
     * или его расширение.
     * 
     * @var Module|Extension|Plugin
     */
    public Module|Extension|Plugin $creator;

    /**
     * Тема используемая в шаблоне представления.
     * 
     * Если значение не установлено, то будет использоваться текущая тема.
     * 
     * @var Theme
     */
    public Theme $theme;

    /**
     * Выполнять поиск файла шаблона представления в каталоге темы.
     * 
     * Если значение `false`, поиск файла шаблона представления в каталоге модуля.
     * 
     * @var bool
     */
    public bool $useTheme = true;

    /**
     * Выполнять поиск файла шаблона представления с локализацией.
     * 
     * @var bool
     */
    public bool $useLocalize = false;

    /**
     * Принудительное (строгое) изменение имени шаблона.
     * 
     * В имя шаблона будет подставлен префикс локализации, независимо от того,
     * является ли выбранный язык языком по умолчанию или нет.
     * 
     * Если значение `false`, то для выбранного языка (если он язык по умолчанию) 
     * изменение в имени шаблона не выполняется.
     * 
     * Применяется только при `$useLocalize = true`.
     * 
     * @var bool
     */
    public bool $forceLocalize = false;

    /**
     * Использовать локализацию виджета.
     * 
     * @var bool
     */
    public bool $useTranslate = true;

    /**
     * Расширение файла шаблона представления.
     * 
     * @var string
     */
    public string $defaultExtension = 'json';

    /**
     * Скрипты клиента.
     * 
     * Если значение не установлено, то будет использоваться текущий скрипт клиента.
     * 
     * @var ClientScript
     */
    public ClientScript $script;

    /**
     * Текущий язык приложения, является языком по умолчанию.
     * 
     * @see Language::isDefault()
     * 
     * @var bool
     */
    public bool $isDefaultLanguage = false;

    /**
     * Префикс уникального идентификатора виджета.
     * 
     * Устанавливается для избежания совпадение при создании идентификатора виджета.
     * Применяется при необходимости повторного рендера виджета с разным содержимым 
     * но одинаковым идентификатором.
     * 
     * @see Widget::makeViewID()
     * 
     * @var string
     */
    public string $viewIDPrefix = '';

    /**
     * {@inheritdoc}
     */
    public function __construct(array $params = [], object $owner = null)
    {
        $this->owner = $owner;
        if ($this->owner instanceof BaseWidget) {
            $this->creator  = $this->owner->creator;
            $this->theme  = $this->owner->theme;
            $this->script = $this->owner->script;
        }
    
        $this->configure($params);

        if ($this->owner instanceof BaseWidget) {
            $this->useTranslate = false;
        }

        $this->isDefaultLanguage = Gm::$services->getAs('language')->isDefault();

        $this->init();
    }

    /**
     * {@inheritdoc}
     */
    public function configure(array $params): void
    {
        // если указан компонент
        if (!isset($this->creator)) {
            if (isset($params['creator'])) {
                $this->creator = $params['creator'];
                unset($params['creator']);
            } else
                $this->creator = Gm::module();
        }
        // если указана тема
        if (!isset($this->theme)) {
            if (isset($params['theme'])) {
                $this->theme = $params['theme'];
                unset($params['theme']);
            } else
                $this->theme = Gm::theme();
        }
        // если указан скрипт
        if (!isset($this->script)) {
            if (isset($params['script'])) {
                $this->script = $params['script'];
                unset($params['script']);
            } else
                $this->script = Gm::$services->getAs('clientScript');
        }

        parent::configure($params);
    }

    /**
     * Возвращет все параметры виджета в виде массива пар "ключ - значение".
     * 
     * @return array Массива элементов в виде пар "ключ - значение".
     */
    public function renderWidget(): array
    { 
        $array = $this->params->toArray();
        array_walk_recursive($array, function (&$value, $key) {
            if ($value instanceof BaseWidget) {
                $content = $value->run();
                if ($value->requires) {
                    $this->addRequires($value->requires);
                }
                if ($value->css) {
                    $this->addCsses($value->css);
                }
                $value = $content;
            } else
            if ($value instanceof Collection) {
                $value = $value->toArray(true);
            }
        });
        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public function render(): array
    {
        $result = $this->renderWidget();

        if ($this->useTranslate) {
            $result = $this->creator->t($result);
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     * 
     * В том случае если имя файла содержит псевдоним, например, '@module::/foo.bar/assets/image/img.jpg', 
     * то будет применяться {@see \Gm::getAlias()}. Это необходимо для подключения сторонних ресурсов,
     * отличных от модуля.
     * 
     * @see \Gm\Mvc\Module\Module::getAssetsUrl()
     */
    public function imageSrc(string $filename): string
    {
        // если символ "@" отсутсвует
        if (strncmp($filename, '@', 1))
            return $this->creator->getAssetsUrl() . '/images' . $filename;
        else
            return Gm::getAlias($filename);
    }

    /**
     * {@inheritdoc}
     * 
     * В том случае если имя файла содержит псевдоним, например, '@module::/foo.bar/assets/css/style.css', 
     * то будет применяться {@see \Gm::getAlias()}. Это необходимо для подключения сторонних ресурсов,
     * отличных от модуля.
     * 
     *@see \Gm\Mvc\Module\Module::getAssetsUrl()
     */
    public function cssSrc(string $filename): string
    {
        // если символ "@" отсутсвует
        if (strncmp($filename, '@', 1))
            return $this->creator->getAssetsUrl() . '/css' . $filename;
        else
            return Gm::getAlias($filename);
    }

    /**
     * Возвращает URL-адрес изображения виджета темы.
     * 
     * @param string $filename Имя файла изображения (может включать путь). 
     *     Всегда начинается с символа "/".
     * 
     * @return string
     */
    public function widgetSrc(string $filename): string
    {
        return $this->theme->url . '/widgets/images' . $filename;
    }

    /**
     * {@inheritdoc}
     * 
     * В том случае если имя файла содержит псевдоним, например, '@module::/foo.bar/assets/js/sample.js', 
     * то будет применяться {@see \Gm::getAlias()}. Это необходимо для подключения сторонних ресурсов,
     * отличных от модуля.
     * 
     * @see \Gm\Mvc\Module\Module::getAssetsUrl()
     */
    public function jsSrc(string $filename): string
    {
        // если символ "@" отсутсвует
        if (strncmp($filename, '@', 1))
            return $this->creator->getAssetsUrl() . '/js' . $filename;
        else
            return Gm::getAlias($filename);
    }

    /**
     * {@inheritdoc}
     * 
     * @see \Gm\Mvc\Module\Module::getRequireUrl()
     */
    public function jsPath(string $name = ''): string
    {
        return $this->creator->getRequireUrl() . '/js' . $name;
    }

    /**
     * Возвращает содержимое файла шаблона.
     * 
     * @param string $viewFile Имя шаблона или файла.
     * @param Module|Extension|Plugin $creator Модуль к которому относится шаблон. 
     *     Применяется для получения файла шаблона. Если значение `null`, 
     *     тогда применяется текущий модуль {@see \Gm\Mvc\Application::$module} (по 
     *     умолчанию `null`).
     * 
     * @return string
     * 
     * @throws TemplateNotFoundException Невозможно получить имя файла шаблона.
     */
    public function loadFile(string $viewFile, Module|Extension|Plugin $creator = null): string
    {
        if ($creator) {
            $this->creator = $creator;
        }

        $filename = $this->getViewFile($viewFile);
        if (!$filename) {
            throw new TemplateNotFoundException(
                Gm::t('app', 'Cannot resolve view file for "{0}"', [$viewFile ?: 'unknow']),
                $filename
            );
        }
        $content = file_get_contents($filename, true);
        if ($content === false) {
            throw new TemplateNotFoundException(
                Gm::t('app', 'Could not load template, file is not accessible "{0}"', [$filename]),
                $filename
            );
        }
        return $content;
    }

    /**
     * Возвращает содержимое файла JSON.
     * 
     * @param string $viewFile Имя шаблона или файла.
     * @param string $subject Имя параметра виджета, которому будет установлено значение 
     *     в виде массива полученного из файла JSON.
     * @param array|false $replace Использовать замену значений в результирующем 
     *     массиве JSON. Замена производится с помощью массива пар "ключ - значение" 
     *     (по умолчанию `false`).
     * 
     * @return array
     * 
     * @throws TemplateNotFoundException Невозможно получить имя файла шаблона.
     * @throws \Gm\Exception\JsonFormatException
     */
    public function loadJSONFile(string $viewFile, ?string $subject, array|false $replace = false): array
    {
        $content = $this->loadFile($viewFile);
        $array = Json::tryDecode($content);

        // выполнить замену
        if ($replace) {
            Arr::replaceValues($array, $replace);
        }

        // установить указанному параметру массив JSON
        if ($subject) {
            $this->params->set($subject, $array);
        }
        return $array;
    }

    /**
     * Возвращает имя файла представления (с путём) из по указанному имени.
     * 
     * Если представление имеет параметр:
     * - "useLocalize" со значением `true`, то результатом будет 
     * имя файла с локализацией (если файл существует). Иначе, имя 
     * файла без локализации. Пример: `view.phtml` и `view-ru_RU.phtml`.
     *  - "useTheme" со значением `true`, то результатом будет 
     * имя файла, cодержащий путь к теме. 
     * 
     * Приоритет получения имени файла представления зависит от параметров "useLocalize",
     * "useTheme", всегда выполняется очерёдность:
     * 1. Получение имени файла представления расположенного в теме;
     * 2. Получение имени файла представления из локализации.
     * 
     * @param string $name Имя шаблона или файл шаблона представления.
     *     Пример: '@app/views/backend/module-info.phtml'.
     * 
     * @return string|false Возвращает значение `false`, если невозможно получить имя 
     *     файла представления.
     */
    public function getViewFile(string $viewFile): string|false
    {
        $extension = pathinfo($viewFile, PATHINFO_EXTENSION);
        if ($extension === '') {
            $viewFile = $viewFile . '.' . $this->defaultExtension;
        } else {
            $extension = $this->defaultExtension;
        }

        $moduleThemePath = $this->creator ? $this->creator->getThemePath() : '';

        /**
         * Получение имени файла шаблона из псевдонима "@".
         * 
         * Например, если указано "@app:views/foobar":
         * 1) если useLocalize, то "<path>/views/<side>/foobar-<locale>.json"
         * 2) "<path>/views/<side>/foobar.json"
         */
        if (strncmp($viewFile, '@', 1) === 0) {
            $filename = Gm::getAlias($viewFile);
            if ($filename === false) {
                return false;
            }
            // 1) Получение с локализацией
            if ($this->useLocalize) {
                // если язык не по умолчанию или принудительное изменение имени шаблона
                if (!$this->isDefaultLanguage || $this->forceLocalize) {
                    $filenameLoc = Str::localizeFilename($filename, null, $extension);
                    if (file_exists($filenameLoc)) return $filenameLoc;
                }
            }
            // 2) Получение без локализации
            return $filename;
        }

        /**
         * Получение имени файла шаблона из каталого приложения "//".
         * 
         * Например, если указано "//foobar":
         * 1) если useLocalize, то "<app-path>/views/<side>/foobar-<locale>.json"
         * 2) если useTheme, то:
         * - если useLocalize, то "<theme-path>/views/foobar-<locale>.json"
         * - "<theme-path>/views/foobar.json"
         * 3) "<app-path>/views/<side>/foobar.json"
         */
        if (strncmp($viewFile, '//', 2) === 0) {
            $viewFile =  ltrim($viewFile, '/');
            // 1) Каталог приложения
            // получение с каталогом приложения
            $filename = Gm::$app->getViewPath() . DS . $viewFile;
             // получение с локализацией
             if ($this->useLocalize) {
                // если язык не по умолчанию или принудительное изменение имени шаблона
                if (!$this->isDefaultLanguage || $this->forceLocalize) {
                    $filenameLoc = Str::localizeFilename($filename);
                    if (file_exists($filenameLoc)) return $filenameLoc;
                }
            }
            // 2) Каталог темы
            if ($this->useTheme) {
                // получение с каталогом темы
                $filenameTh =  $this->theme->viewPath . DS . $viewFile;
                // получение с локализацией
                if ($this->useLocalize) {
                    // если язык не по умолчанию или принудительное изменение имени шаблона
                    if (!$this->isDefaultLanguage || $this->forceLocalize) {
                        $filenameLoc = Str::localizeFilename($filenameTh);
                        if (file_exists($filenameLoc)) return $filenameLoc;
                    }
                }
                // без локализации
                if (file_exists($filenameTh)) return $filenameTh;
            }
            // 3) Без локализации
            return $filename;
        }

        // например 'foobar' => '/foobar'
        if (strncmp($viewFile, '/', 1) !== 0) {
            $viewFile = '/' . $viewFile;
        }

        /**
         * Получение имени файла шаблона из каталого модуля "/".
         * 
         * Например, если указано "/foobar":
         * 1) если useLocalize, то:
         * -  если useTheme, то "<theme-path>/views/foobar-<locale>.json"
         * - "<module-path>/views/foobar-<locale>.json"
         * 2) если useTheme, то "<theme-path>/views/foobar.json"
         * 3) "<module-path>/views/foobar.json"
         */
        // 1) Получение с локализацией (если язык не по умолчанию)
        if ($this->useLocalize) {
            // если указан язык отличный от языка по умолчанию или принудительное изменение имени шаблона
            if (!$this->isDefaultLanguage || $this->forceLocalize) {
                $templateLoc = Str::localizeFilename($viewFile, null, $extension);
                if ($this->useTheme) {
                    // получение с темой и локализацией
                    $filenameLoc = $this->theme->viewPath . $moduleThemePath . $templateLoc;
                    if (file_exists($filenameLoc)) return $filenameLoc;
                }
                // получение без темы но с локализацией
                $filenameLoc = $this->creator->getViewPath() . $templateLoc;
                if (file_exists($filenameLoc)) return $filenameLoc;
            }
        }
        // 2) Получение без локализации (с темой)
        // получение с темой
        if ($this->useTheme) {
            $filename = $this->theme->viewPath . $moduleThemePath . $viewFile;
            if (file_exists($filename)) return $filename;
        }
        // 3) Получение без темы и без локализации
        return $this->creator->getViewPath() . $viewFile;
    }

    /**
     * Устанавливает (генерирует) уникальный идентификатор виджету.
     * 
     * @see \Gm\Mvc\Module\BaseModule::viewID()
     * 
     * @param string $name Имя выводимого элемента для которого создаётся идентификатор, 
     *     например 'button'.
     * 
     * @return void
     */
    public function setViewID(string $name = null): void
    {
        if ($name === null) {
            $name = $this->params->id;
        }
        $this->makedViewID = true; // чтобы дважды не генерировалось
        $this->params->id = $this->creator->viewID($name);
    }

    /**
     * @var bool Если значение `true`, уникальный идентификатор виджету был сгенерирован.
     */
    protected $makedViewID = false;

    /**
     * Генерирует и устанавливает уникальный идентификатор виджету.
     * 
     * Для избежания повторной генерации
     * 
     * @see \Gm\Mvc\Module\BaseModule::viewID()
     * 
     * @return string
     */
    public function makeViewID(): string
    {
        if ($this->makedViewID === false) {
            $this->makedViewID = true;
            return $this->params->id = $this->creator->viewID($this->params->id) . $this->viewIDPrefix;
        }
        return $this->params->id;
    }

    /**
     * Указывает Панели управления контейнер для вывода содержимого виджета.
     * 
     * Контейнер виджета определяется праметром `dockTo`.
     * 
     * @param string|array $container Идентификатор или параметры контейнера 
     *     содержимого виджета.
     * 
     * @return void
     */
    public function dockTo(string|array $container): void
    {
        if (is_string($container)) {
            $this->dockTo = [
                'container' => $container,
                'destroy'   => true
            ];
        } else
            $this->dockTo = $container;
    }
}
