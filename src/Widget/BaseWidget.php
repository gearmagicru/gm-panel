<?php
/**
 * Этот файл является частью пакета GM Framework.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Widget;

use Gm\Stdlib\Collection;
use Gm\Stdlib\BaseObject;

/**
 * Базовый класс виджета для формирования элементов интерфейса Панели управления.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class BaseWidget extends BaseObject implements WidgetInterface
{
    /**
     * Коллекция параметров виджета.
     * 
     * @see BaseWidget::configure()
     * 
     * @var Collection|array
     */
    public Collection|array $params = [];

    /**
     * Имена параметров, которые должны стать свойствами класса.
     * 
     * Если такие имена параметров есть, то они становятся свойствами класса в 
     * {@see BaseWidget::configure()}, но как параметры перестают существовать.
     * Применяется для передачи значений свойств класса в качестве параметров указанных 
     * в конструкторе класса.
     *
     * @var array
     */
    public array $passParams = [];

    /**
     * Собственник виджета. 
     * 
     * В том случае, если виджет был создан другим виджетом и является его атрибутом.
     * Например, панель имеет кнопку, то панель - owner, собственник кнопки. А обращение
     * к кнопке будет так: `panel->button` или внтури объекта панели `$this->button`.
     * 
     * Необходимо для постраения иерархии виджетов:
     * ```
     * window
     *     ->form
     *         ->field1
     *         ->field2
     *     ->buttons
     * ```
     * 
     * @var object|null
     */
    public ?object $owner;

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
     * - '/foobar.css'  => 'https://domain/modules/gm/gm.wd.foobar/assets/css/foobar.css';
     * - '/foo/bar.css' => 'https://domain/modules/gm/gm.wd.foobar/assets/css/foo/bar.css'.
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
     * должен иметь имя класса 'Gm.wd.foobar.Name' ('https://domain/modules/gm/gm.wd.foobar/assets/js/Name.js').
     * 
     * @var string
     */
    public string $namespaceJs = '';

    /**
     * {@inheritdoc}
     * 
     * @param object $owner Собственник виджета. В том случае, если виджет был создан 
     *     другим виджетом и является его атрибутом (по умолчанию `null`).
     */
    public function __construct(array $params = [], object $owner = null)
    {
        $this->owner = $owner;

        $this->configure($params);

        $this->init();
    }

    /**
     * {@inheritdoc}
     */
    public function configure(array $params): void
    {
        // если есть параметры, которые должны стать свойством класса
        if ($this->passParams) {
            foreach ($this->passParams as $name) {
                if (isset($params[$name])) {
                    $this->$name = $params[$name];
                    unset($this->params[$name], $params[$name]);
                }
            }
        }

        $this->params = Collection::createInstance(
            array_merge($this->params, $params)
        );
    }

    /**
     * Инициализация виджета.
     * 
     * Этот метод вызывается в конце конструктора после инициализации вижета 
     * заданной конфигурацией.
     * 
     * @return void
     */
    protected function init(): void
    {
    }

    /**
     * Событие возникающие перед окончательным получением содержимого виджета.
     * 
     * @see BaseWidget::run()
     * 
     * @return bool Возвращает значение `false`, если необходимо остановить запуск
     *     виджета (по умолчанию `true`).
     */
    public function beforeRender(): bool
    {
        return true;
    }

    /**
     * Событие возникающие после окончательного получения содержимого виджета.
     * 
     * @see BaseWidget::run()
     * 
     * @param mixed $result Содержимое виджета.
     * 
     * @return mixed
     */
    public function afterRender(mixed $result): mixed
    {
        return $result;
    }

    /**
     * Возвращает параметры виджета, преобразованные в массив в виде пар "имя - значение".
     * 
     * @see BaseWidget::$params
     * 
     * @return array
     */
    public function render(): array
    {
        return $this->params->toArray();
    }

    /**
     * Запускает виджет и получает его содержимое.
     * 
     * В качестве содержимого виджета выступают его параметры в виде пар "имя - значение".
     * 
     * Перед запуском виджета будет выполнен метод {@see BaseWidget::beforeRender()}.
     * Определяющий необходимость получения его параметров.
     * 
     * После запуска будет выполнен метод {@see BaseWidget::afterRender()}.
     * 
     * @return array
     */
    public function run(): mixed
    {
        $result = [];
        if ($this->beforeRender()) {
            $result = $this->render();
            $result = $this->afterRender($result);
        }
        return $result;
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
     * Добавляет список имён классов JS виджета.
     * 
     * @see BaseWidget::$requires
     * 
     * @param string $names Имена классов JS виджета, например: `['Gm.wd.foobar.Name', '...', ...]`.
     * 
     * @return $this
     */
    public function addRequires(array $names): static
    {
        $this->requires = array_merge($this->requires , $names);
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
     * Добавляет имена файлов таблиц стилей виджета.
     * 
     * @see Widget::$css
     * 
     * @param string $filename Имена файлов таблиц стилей, например: ['/foobar.css', '/foo/bar.css', ...].
     * 
     * @return $this
     */
    public function addCsses(array $filenames): static
    {
        $this->css = array_merge($this->css, $filenames);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setNamespaceJS(string $namespace): static
    {
        $this->namespaceJs = $namespace;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function imageSrc(string $filename): string
    {
        return '/images' . $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function cssSrc(string $filename): string
    {
        return '/css' . $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function jsSrc(string $filename): string
    {
        return '/js' . $filename;;
    }

    /**
     * {@inheritdoc}
     */
    public function jsPath(string $name = ''): string
    {
        return '/js' . $name;
    }

    /**
     * Проверяет, существует ли параметр, когда к параметру обращаются как к свойству 
     * объекта. 
     * 
     * @param string $name Имя параметра.
     * 
     * @return bool Если значение `true`, параметр существует.
     */
    public function __isset(string $name)
    {
        return isset($this->params[$name]);
    }

    /**
     * Устанавливает значение параметру, когда к нему обращаются, как к свойству объекта.
     *
     * @param string $name Имя параметра.
     * @param mixed $value Значение параметра.
     * 
     * @return void
     */
    public function __set(string $name, mixed $value)
    {
        $this->params[$name] = $value;
    }

    /**
     * Удаляет параметр, когда к нему обращаются, как к свойству объекта.
     *
     * @param string $name Имя параметра.
     * 
     * @return void
     */
    public function __unset(string $name)
    {
        unset($this->params[$name]);
    }

    /**
     * Возращает значение параметра.
     *
     * @param string $name Имя параметра.
     * 
     * @return mixed Если значение `null`, когда имя параметра не существует.
     */
    public function &__get(string $name)
    {
        return $this->params->getFor($name);
    }
}
