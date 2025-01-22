<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace  Gm\Panel\Http\Response;

use Gm\Exception;
use Gm\Debug\Dumper;
use Gm\Http\Response;
use Gm\Stdlib\Collection;
use Gm\Panel\Widget\WidgetInterface;

/**
 * Класс метаданных используемых для HTTP-ответа панели управления "GM Panel" в 
 * формате JSONG.
 * 
 * Параметр `$icon` (CSS класс значка, определяется темой) в методах класса может 
 * иметь значения:
 * - 'g-icon_dlg-query-error', ошибки выполнения запроса к бд;
 * - 'g-icon_dlg-db-error',  ошибки работы адаптера бд;
 * - 'g-icon_dlg-db-connect-error', ошибки подключения к бд;
 * - 'g-icon_dlg-script-error', ошибки выполнения скрипта;
 * - 'g-icon_dlg-forbidden', ошибки доступа;
 * - 'g-icon_dlg-request-error', ошибки запроса к серверу;
 * - 'g-icon_dlg-warning', предупреждения;
 * - 'g-icon_dlg-error', ошибка;
 * - 'g-icon_dlg-success', успех;
 * - 'g-icon_dlg-message', сообщение;
 * - 'g-icon_dlg-question', вопрос.  
 * Параметр устанавливается для Ext.MessageBox.icon в виде значения, например 
 * 'g-icon-svg g-icon_dlg-db-error'.
 * 
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Http\Response
 * @since 1.0
 */
class JsongMetadata extends Collection
{
    /**
     * Свойство определяющие возврат контента метаданных.
     * 
     * @var string
     */
    public string $contentProperty = 'data';

    /**
     * Код состояния ошибки отдаваемый в HTTP-ответе.
     * 
     * Если значение '0', то статус указываться не будет.
     * 
     * @see JsongMetadata::error()
     * 
     * @var int
     */
    public int $errorStatusCode = 400;

    /**
     * HTTP-ответ.
     * 
     * @var Response|null
     */
    protected ?Response $response = null;

    /**
     * Конструктор класса.
     * 
     * @param \Gm\Http\Response|null $response HTTP-ответ (по умолчанию `null`).
     * 
     * @return void
     */
    public function __construct(?Response $response = null)
    {
        $this->response = $response;
        $this->container = [
            'success' => true,
            'message' => ['status' => '', 'text' => ''],
            'data'    => [],
            'command' => []
        ];
    }

    /**
     * Добавляет значение элементу коллекцию, превращая элемент в нумерованный массив.
     * 
     * @param string $key Ключ элемента коллекции.
     * @param mixed $value Значение элемента.
     * 
     * @return $this
     */
    public function add(string $key, mixed $value): static
    {
        if (!isset($this->container[$key]))
            $this->container[$key] = [$value];
        else {
            // избежать дублирования
            if (!in_array($value, $this->container[$key])) {
                $this->container[$key][] = $value;
            }
        }
        return $this;
    }

    /**
     * Добавляет в коллекцию "requires" значения.
     * 
     * @param array $requires Значения коллекции.
     * 
     * @return $this
     */
    public function addRequires(array $requires): static
    {
        foreach ($requires as $require) {
            $this->add('requires', $require);
        }
        return $this;
    }

    /**
     * Добавляет в коллекцию "css" значения (подключаемые таблицы стилей).
     * 
     * @param array $css Таблицы стилей, например: `['https://domain/css/foobar.css'...]`.
     * 
     * @return $this
     */
    public function addCss(array $css): static
    {
        foreach ($css as $one) {
            $this->add('css', $one);
        }
        return $this;
    }

    /**
     * Добавляет метаданные виджета.
     * 
     * Метаданные виджета:
     * - файлы таблиц стилей {@see \Gm\Panel\Widget\BaseWidget::$css};
     * - пространство имён {@see \Gm\Panel\Widget\BaseWidget::$requires} GmJS, ExtJS;
     * - пространство имён виджета (для подключения JS виджета) {@see \Gm\Panel\Widget\BaseWidget::$namespaceJs}.
     * 
     * @param WidgetInterface $widget Виджет.
     * 
     * @return $this
     */
    public function addWidget(WidgetInterface $widget): static
    {
        // подключение CSS
        if ($widget->css) {
            foreach ($widget->css as $filename) {
                $this->add('css', $widget->cssSrc($filename));
            }
        }

        // подключение JS
        if ($widget->requires) {
            $this->addRequires($widget->requires);
        }

        // пространство имён виджета (для своих JS)
        if ($widget->namespaceJs) {
            $this->add('jsPath', [$widget->namespaceJs, $widget->jsPath()]);
        }
        return $this;
    }

    /**
     * Сообщение передаваемое в метаданных.
     * 
     * @param string|array<string> $message Сообщение.
     * @param string $status Статус сообщения (заголовок, по умолчанию '').
     * @param null|string $type Тип сообщения (по умолчанию `null`).
     * @param null|string $icon URL-адрес значка. Если не `null`, то `$type = custom` (по умолчанию `null`).
     * 
     * @return $this
     */
    public function message(string|array $message, string $status = '', ?string $type = null, ?string $icon = null): static
    {
        if (is_array($message)) {
            $this->message = $message;
        } else {
            if ($status || $type || $icon) {
                $this->message = ['text' => $message];
                if ($status)
                    $this->message['status'] = $status;
                if ($type)
                    $this->message['type'] = $type;
                if ($icon)
                    $this->message['icon'] = $icon;
            } else
                $this->message = $message;
        }
        return $this;
    }

    /**
     * Устанавливает успех ответу.
     * 
     * @param string|array<string> $message Сообщение.
     * @param string $status Статус сообщения (заголовок, по умолчанию '').
     * @param null|string $type Тип сообщения.
     * @param null|string $icon URL-адрес значка. Если не `null`, то `$type = custom `.
     * 
     * @return $this
     */
    public function success(string|array $message = '', string $status = '', ?string $type = null, ?string $icon = null): static
    {
        if ($message) {
            $this->message($message, $status, $type, $icon);
        }
        $this->success = true;
        return $this;
    }

    /**
     * Устанавливает ошибки ответу.
     * 
     * @param string|array<string> $message Сообщение.
     * @param string $status Статус сообщения (заголовок, по умолчанию '').
     * @param null|string $type Тип сообщения (message).
     * @param null|string $icon URL-адрес значка. Если не `null`, то `$type = custom `.
     * 
     * @return $this
     */
    public function error(string|array $message = '', string $status = '', ?string $type = null, ?string $icon = null): static
    {
        if ($message) {
            $this->message($message, $status, $type, $icon);
        }
        $this->success = false;
        if ($this->response !== null && $this->errorStatusCode !== 0) {
            $this->response->setStatusCode($this->errorStatusCode);
        }
        return $this;
    }

    /**
     * Проверяет, не было ли ошибки.
     * 
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success === true;
    }

    /**
     * Проверяет, была ли установлена ошибка.
     * 
     * @return bool
     */
    public function isError(): bool
    {
        return $this->success === false;
    }

    /**
     * Устанавливает контент для HTTP-ответа.
     * 
     * @param mixed $content Контент (по умолчанию `[]`).
     * 
     * @return $this
     */
    public function content(mixed $content = []): static
    {
        $this->container[$this->contentProperty] = $content;
        return $this;
    }

    /**
     * Возвращает текст сообщения.
     * 
     * @return null|string Если значение `null`, текст сообщения отсутствует.
     */
    public function getMsgText(): ?string
    {
        if (is_array($this->message))
            return $this->message['text'] ?: null;
        else
            return $this->message ?: null;
    }

    /**
     * Возвращает текст сообщения если была ошибка.
     * 
     * @return null|string Сообщение только при ошибке, иначе значение `null`.
     */
    public function getMsgError(): ?string
    {
        return !$this->isSuccess() ? $this->getMsgText() : null;
    }

   /**
     * Добавляет параметры указанному ключу.
     * 
     * Параметры включают имя и атрибуты к которому они относятся.
     * 
     * @param string $key Ключ.
     * @param string $name Имя.
     * @param array $attributes Атрибуты (по умолчанию `[]`).
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException Если значение ключа не массив.
     */
    public function addAttributes(string $key, string $name, array $attributes = []): static
    { 
        if (!isset($this->container[$key])) {
            $this->container[$key] = [];
        }
        if (!is_array($this->container[$key])) {
            throw new Exception\RuntimeException("Could not adding array to metadata attribute \"$key\".");
        }
        $this->container[$key][] = ['name' => $name, 'attr' => $attributes];
        return $this;
    }

    /**
     * Добавляет отладочную информацию.
     * 
     * @return $this
     */
    public function addDebug(): static
    {
        $this->debug = [
            'memoryUsage'     => Dumper::memoryUsage(),
            'memoryPeakUsage' => Dumper::memoryPeakUsage(),
            'time'            => Dumper::executeTime(),
        ];
        return $this;
    }

    /**
     * Конвертирует метаданные в строку.
     * 
     * @return string
     */
    public function toString(): string
    {
        return $this->toJson(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }


    /**
     * Добавляет в метаданные атрибут "command".
     * 
     * @see JsongMetadata::addAttributes()
     * 
     * @param string $name Название комманды.
     * @param array<mixed> $args Аргументы комманды.
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException
     */
    public function command(string $name, ...$args): static
    {
        $this->addAttributes('command', $name, $args);
        return $this;
    }

    /**
     * Добавляет в метаданные комманду "popupMsg".
     * 
     * Показать всплывающие сообщение.
     * 
     * @see JsongMetadata::command()
     * 
     * @param string $message Сообщение.
     * @param string $title Заголовок.
     * @param string|null $type Тип сообщения, например: 'accept' (по умолчанию `null`).
     * @param string|null $icon URL-адрес значок (по умолчанию `null`).
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException
     */
    public function cmdPopupMsg(string $message, string $title, ?string $type = null, ?string $icon = null): static
    {
        return $this->command('popupMsg', $message, $title, $type, $icon);
    }

    /**
     * Добавляет в метаданные комманду "msgBox" (Сообщение - предупреждение).
     * 
     * Показать окно с предупреждением.
     * 
     * @see JsongMetadata::cmdMsgBox()
     * 
     * @param string $message Сообщение.
     * @param string $title Заголовок.
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException
     */
    public function cmdWarningMsg(string $message, string $title): static
    {
        return $this->cmdMsgBox([
            'message' => $message,
            'title'   => $title,
            'icon'    => 'WARNING'
        ]);
    }

    /**
     * Добавляет в метаданные комманду "msgBox" (Сообщение - информация).
     * 
     * Показать окно с информацией.
     * 
     * @see JsongMetadata::cmdMsgBox()
     * 
     * @param string $message Сообщение.
     * @param string $title Заголовок.
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException
     */
    public function cmdInfoMsg(string $message, string $title): static
    {
        return $this->cmdMsgBox([
            'message' => $message,
            'title'   => $title,
            'icon'    => 'INFO'
        ]);
    }

    /**
     * Добавляет в метаданные комманду "msgBox" (Сообщение - ошибки).
     * 
     * Показать окно с ошибкой.
     * 
     * @see JsongMetadata::cmdMsgBox()
     * 
     * @param string $message Сообщение.
     * @param string $title Заголовок.
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException
     */
    public function cmdErrorMsg(string $message, string $title): static
    {
        return $this->cmdMsgBox([
            'message' => $message,
            'title'   => $title,
            'icon'    => 'ERROR'
        ]);
    }

    /**
     * Добавляет в метаданные комманду "msgBox".
     * 
     * Показать окно с сообщением.
     * 
     * @see JsongMetadata::cmdMsgBox()
     * 
     * @param string $message Сообщение.
     * @param string $title Заголовок.
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException
     */
    public function cmdShowMsg(string $message, string $title): static
    {
        return $this->cmdMsgBox([
            'message' => $message,
            'title'   => $title
        ]);
    }

    /**
     * Добавляет в метаданные комманду "msgBox" (Сообщением с параметрами).
     * 
     * Показать окно с сообщением.
     * 
     * @see JsongMetadata::command()
     * 
     * @param array $params Параметры сообщениея.
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException
     */
    public function cmdMsgBox(array $params): static
    {
        return $this->command(
            'msgBox',
            array_merge([
                'title'   => '',
                'message' => '',
                'buttons' => 'OK', // OK (1), YES (2), NO (4), CANCEL (8), OKCANCEL (9), YESNO (6), YESNOCANCEL (14)
                'icon'    => 'INFO' // INFO, WARNING, QUESTION, ERROR
            ], $params)
        );
    }

    /**
     * Добавляет в метаданные комманду "msgMask".
     * 
     * Вывод сообщения в маске.
     * 
     * @see JsongMetadata::command()
     * 
     * @param string $message Сообщение.
     * @param string $title Заголовок.
     * @param string $action Действие (по умолчанию 'info').
     * @param string $icon CSS класс значка (по умолчанию `null`).
     * @param string $type Тип сообщения (по умолчанию 'glyph').
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException
     */
    public function cmdMsgMask(string $message, string $title, string $action = 'info', ?string $icon = null, ?string $type = 'glyph'): static
    {
        return $this->command(
            'msgMask',
            [
                'message'  => $message,
                'title'    => $title,
                'action'   => $action,
                'icon'     => $icon,
                'type'     => $type
            ]
        );
    }

    /**
     * Добавляет в метаданные комманду "reloadGrid".
     * 
     * Обновить данные списка компонента Gm.view.grid.Grid GmJS.
     * 
     * @see JsongMetadata::command()
     * 
     * @param string $gridId Идентификатор компонента списка.
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException
     */
    public function cmdReloadGrid(string $gridId): static
    {
        return $this->command('reloadGrid', $gridId);
    }

    /**
     * Добавляет в метаданные комманду "reloadTreeGrid".
     * 
     * Обновить данные дерева компонента Gm.view.grid.Tree GmJS.
     * 
     * @see JsongMetadata::command()
     * 
     * @param string $treeGridId Идентификатор дерева компонента.
     * @param string $nodeId Идентификатор узла дерева (по умолчачанюи `root`).
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException
     */
    public function cmdReloadTreeGrid(string $treeGridId, string $nodeId = 'root'): static
    {
        return $this->command('reloadTreeGrid', $treeGridId, $nodeId);
    }

    /**
     * Добавляет в метаданные комманду "reloadTree".
     * 
     * Обновить данные дерева компонента Ext.tree.Panel ExtJS.
     * 
     * @see JsongMetadata::command()
     * 
     * @param string $treeId Идентификатор дерева компонента.
     * @param string $nodeId Идентификатор узла дерева (по умолчачанюи `root`).
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException
     */
    public function cmdReloadTree(string $treeId, string $nodeId = 'root'): static
    {
        return $this->command('reloadTree', $treeId, $nodeId);
    }

    /**
     * Добавляет в метаданные комманду "reloadGrid".
     * 
     * Обновить запись списка компонента Gm.view.grid.Grid GmJS.
     * 
     * @see JsongMetadata::command()
     * 
     * @param string $gridId Идентификатор списка компонента.
     * @param string $rowId Идентификатор строки.
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException
     */
    public function cmdReloadRowGrid(string $gridId, string $rowId): static
    {
        return $this->command('reloadGrid', $gridId, $rowId);
    }

    /**
     * Добавляет в метаданные комманду "reloadStore".
     * 
     * Обновить хранилище компонента.
     * 
     * @see JsongMetadata::command()
     * 
     * @param string $componentId Идентификатор компонента.
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException
     */
    public function cmdReloadStore(string $componentId): static
    {
        return $this->command('reloadStore', $componentId);
    }

    /**
     * Добавляет в метаданные комманду "redirect".
     * 
     * Перейти в браузере по указанному URL адресу.
     * 
     * @see JsongMetadata::command()
     * 
     * @param string $url URL адрес.
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException
     */
    public function cmdRedirect(string $url): static
    {
        return $this->command('redirect', $url);
    }

    /**
     * Добавляет в метаданные комманду "loadWidget".
     * 
     * Загрузка виджета по указанному URL адресу.
     * 
     * @see JsongMetadata::command()
     * 
     * @param string $route Маршрут запроса.
     * @param array<string, mixed>|null $params Параметры запроса методом POST (по умолчанию `null`).
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException
     */
    public function cmdLoadWidget(string $route, array $params = null): static
    {
        return $this->command('loadWidget', $route, $params);
    }

    /**
     * Добавляет в метаданные комманду "htmlElement".
     * 
     * Вызывает метод $method html элемента Sencha ExtJS.
     * 
     * @see JsongMetadata::command()
     * 
     * @param string $id Идентификатор html элемента.
     * @param string $method Имя метода.
     * @param array $args<string, mixed> Аргументы метода.
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException
     */
    public function cmdElement(string $id, string $method, array $args = null): static
    {
        return $this->command('htmlElement', $id, $method, $args);
    }

    /**
     * Добавляет в метаданные комманду "create".
     * 
     * Создаёт объект Sencha ExtJS.
     * 
     * @see JsongMetadata::command()
     * 
     * @param array $config Конфигурация объекта.
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException
     */
    public function cmdCreate(array $config): static
    {
        return $this->command('create', $config);
    }

    /**
     * Добавляет в метаданные комманду "component".
     * 
     * Вызывает метод компонента Sencha ExtJS.
     * 
     * @see JsongMetadata::command()
     * 
     * @param string $id Идентификатор компонента.
     * @param string $method Имя метода.
     * @param array<mixed>|null $args Аргументы метода.
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException
     */
    public function cmdComponent(string $id, string $method, array $args = null): static
    {
        return $this->command('component', $id, $method, $args);
    }

    /**
     * Добавляет в метаданные комманду "callControllerMethod".
     * 
     * Вызывает метод контроллера компонента Sencha ExtJS.
     * 
     * @see JsongMetadata::command()
     * 
     * @param string $id Идентификатор компонента.
     * @param string $method Имя метода.
     * @param array<mixed>|null $args Аргументы метода.
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException
     */
    public function cmdCallControllerMethod(string $id, string $method, array $args = null): static
    {
        return $this->command('callControllerMethod', $id, $method, $args);
    }

    /**
     * Добавляет в метаданные комманду "gm".
     * 
     * Вызывает метод (функцию) объекта Gm, например: `Gm.download('/foobar.js')`.
     * 
     * @see JsongMetadata::command()
     * 
     * @param string $method Имя метода, например: 'download' => 'Gm.download'.
     * @param array<mixed>|null $args Аргументы метода (по умолчанию `null`).
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException
     */
    public function cmdGm(string $method, ?array $args = null): static
    {
        return $this->command('gm', $method, $args);
    }

    /**
     * Добавляет в метаданные комманду "appComponent".
     * 
     * Вызывает у компонента приложения Gm.app метод.
     * 
     * @see JsongMetadata::command()
     * 
     * @param string $name Имя компонента Gm.app.
     * @param string $method Имя метода.
     * @param array<mixed>|null $args Аргументы метода.
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException
     */
    public function cmdAppComponent(string $name, string $method, ?array $args = null): static
    {
        return $this->command('appComponent', $name, $method, $args);
    }

    /**
     * Добавляет в метаданные комманду "console".
     * 
     * Вызывает метод у объекта `console`.
     * 
     * @see JsongMetadata::command()
     * 
     * @param string $type Вид вывода в консоль: 'info', 'error', 'warn', 'log', 'dir'.
     * @param string $message Сообщение.
     * @param array<mixed> $vars Переменные выводимые в консоль (по умолчанмю `[]`).
     * 
     * @return $this
     * 
     * @throws Exception\RuntimeException
     */
    public function cmdConsole(string $type, string $message, array $vars = []): static
    {
        array_unshift($vars, $message);
        return $this->command('console', $type, $vars);
    }

    /**
     * Возвращает HTTP-ответ.
     * 
     * Метод применяется для удобства в возвращении HTTP-ответа, переданного через ряд конструкторов.
     * 
     * Например:
     * ```php
     * return $this
     *     ->getResponse()
     *         ->meta
     *             ->error('Error')
     *             ->response();
     * ```
     * 
     * @return Response|null
     */
    public function response(): Response|null
    {
        return $this->response;
    }
}
