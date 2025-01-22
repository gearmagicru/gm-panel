<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Audit;

use Gm;
use Closure;
use Gm\Helper\Str;
use Gm\Http\Request;
use Gm\Http\Response;
use Gm\Helper\Browser;
use Gm\I18n\Formatter;
use Gm\I18n\Translator;
use Gm\Stdlib\Collection;
use Gm\Panel\User\UserDevice;
use Gm\Panel\User\UserProfile;
use Gm\Panel\User\UserIdentity;
use Gm\Mvc\Module\BaseModule;
use Gm\Mvc\Controller\BaseController;

/**
 * Класс предназначен для определения атрибутов информации журнала аудита.
 * 
 * Все необходимые для записи атрибуты указываются в журнале аудита {@see Audit::$properties}.
 * Значение каждого атрибута устанавливается с помощью метода {@see Audit::defineProperty()} 
 * или прямого вызова геттера атрибута информации: `get<Property>()`.
 * 
 * Атрибуты имеющие свои геттеры:
 * - `userId`, идентификатор пользователя;
 * - `userName`, имя пользователя;
 * - `userDetail`, подробная информация о пользователе;
 * - `permission`, права доступа пользователя;
 * - `ipAddress`, IP-адрес пользователя;
 * - `moduleId`, идентификатор модуля;
 * - `moduleName`, имя модуля;
 * - `browserName`, версия браузера;
 * - `browserFamily`, семейство браузера;
 * - `osName`, версия ОС;
 * - `osFamily`, семейство ОС;
 * - `requestUrl`, маршрут HTTP-запроса;
 * - `requestMethod`, метод HTTP-запроса;
 * - `requestCode`, код состояния HTTP-ответа;
 * - `queryId`, идентификатор записи в HTTP-запросе;
 * - `query`, переменные HTTP-запроса;
 * - `error`, ошибка возвращаемая HTTP-ответом;
 * - `success`, успех выполнения HTTP-запроса;
 * - `comment`, комментарий к HTTP-запросу;
 * - `date`, дата последнего HTTP-запроса.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Audit
 * @since 1.0
 */
class Info extends Collection
{
    /**
     * Идентификация пользователя.
     *
     * @var UserIdentity|null
     */
    private ?UserIdentity $_userIdentity = null;

    /**
     * Профиль пользователя.
     *
     * @var UserProfile|null
     */
    private ?UserProfile $_userProfile = null;

    /**
     * Устройство пользователя.
     *
     * @var UserDevice|null
     */
    private ?UserDevice $_userDevice = null;

    /**
     * Замыкание возвращающие комментарий действия пользователя.
     *
     * @see Info::getComment()
     * 
     * @var Closure|null
     */
    public ?Closure $commentCallback = null;

    /**
     * Значение для параметра, который не определён.
     * 
     * Такое значение подставляется в комментарий.
     *
     * @var string
     */
    public string $unknown = '<unknown>';

    /**
     * Последний вызываемый контроллер модуля.
     * 
     * @see Info::getBaseController()
     * 
     * @var BaseController|null
     */
    protected ?BaseController $controller;

    /**
     * Последний вызываемый модуль.
     * 
     * @see Info::getBaseModule()
     * 
     * @var BaseModule|null
     */
    protected ?BaseModule $module;

    /**
     * HTTP-запрос.
     * 
     * @see Info::__construct()
     * 
     * @var Request
     */
    protected Request $request;

    /**
     * HTTP-ответ.
     * 
     * @see Info::__construct()
     * 
     * @var Response
     */
    protected Response $response;

    /**
     * Форматтер.
     * 
     * @see Info::__construct()
     * 
     * @var Formatter
     */
    protected Formatter $formatter;

    /**
     * Транслятор (локализатор сообщений).
     * 
     * @var Translator
     */
    protected Translator $translator;

    /**
     * Конструктор класса.
     * 
     * @return void
     */
    public function __construct()
    {
        if (Gm::hasUserIdentity()) {
            $this->_userIdentity = Gm::userIdentity();
        }

        $this->translator = $this->getTranslator();
        $this->controller = $this->getController();
        $this->request    = Gm::$services->getAs('request');
        $this->response   = Gm::$services->getAs('response');
        $this->formatter  = Gm::$services->getAs('formatter');
        $this->unknown    = Gm::t(BACKEND, '<unknown>'); 
    }

    /**
     * Возвращает локализатор сообщений.
     * 
     * @return Translator
     */
    public function getTranslator(): Translator
    {
        if (isset($this->translator)) {
            return $this->translator;
        }

        $translator = Gm::$services->getAs('translator');
        if (IS_FRONTEND) {
            $translator->addCategory(BACKEND);
        }
        return $this->translator = $translator;
    }

    /**
     * Возвращает последний вызываемый контроллер модуля.
     * 
     * @return BaseController|null
     */
    public function getController(): ?BaseController
    {
        if (isset($this->controller)) {
            return $this->controller;
        }
        return $this->controller = Gm::$app->controller;
    }

    /**
     * Возвращает последний вызываемый модуль.
     * 
     * @return BaseModule|null
     */
    public function getModule(): ?BaseModule
    {
        if (isset($this->module)) {
            return $this->module;
        }
        return $this->module = Gm::$app->module;
    }

    /**
     * Определяет значение атрибуту информации.
     * 
     * Для определения значения, используется геттер атрибута `get<Property>()`.
     *
     * @param string $property Имя атрибута.
     * 
     * @return void
     */
    public function defineProperty(string $property): void
    {
        if (array_key_exists($property, $this->container)) {
            $value = $this->container[$property];
        } else {
            $getProperty = 'get' . $property;
            if (method_exists($this, $getProperty)) {
                $value = $this->$getProperty();
            } else {
                $value = null;
            }
        }
        if ($value !== null) {
            $this->container[$property] = $value;
        }
    }

    /**
     * Устанавливает ошибку атрибутам информации.
     * 
     * После установки атрибут `success = 0`.
     * 
     * @param string $message Текст ошибки (атрибут `error`).
     * @param mixed $params Параметры ошибки (атрибут `errorParams`).
     * 
     * @return void
     */
    public function setError(string $message, mixed $params = null): void
    {
        $this->success = 0;
        $this->error = $message;
        $this->errorParams = $params;
    }

    /**
     * Инициализирует раздел информации "device" (информация о устройстве пользователя).
     * 
     * @return void
     */
    public function deviceSection(): void
    {
        if ($this->_userIdentity) {
            $this->_userDevice = $this->_userIdentity->getDevice();
        }
    }

    /**
     * Инициализирует раздел информации "user" (информация о пользователе).
     * 
     * @return void
     */
    public function userSection(): void
    {
        if ($this->_userIdentity) {
            $this->_userProfile = $this->_userIdentity->getProfile();
        }
    }

    /**
     * Инициализирует раздел информации "controller" (информация о контроллере).
     * 
     * @return void
     */
    public function controllerSection(): void
    {
        if ($controller = $this->controller) {
            $this->controllerName   = $controller->getShortClass();
            $this->controllerAction = (string) $controller->action();
            $this->controllerEvent  = $controller->getClass() . '::' . $this->controllerAction . 'Action';
        } else {
            // в том случаи если доступа к контроллеру не было или он не был создан, 
            // получаем данные о запросе из маршрутизатора
            $this->controllerName   = Gm::alias('@match:controller');
            $this->controllerAction =  Gm::alias('@match:action');
            $this->controllerEvent  = $this->controllerName . '::' . $this->controllerAction . 'Action';
        }
    }

    /**
     * Возвращает атрибут "userId" (идентификатор пользователя).
     * 
     * @return int|int Если значение `null`, то пользователь не авторизован. Иначе, 
     *     идентификатор пользователя.
     */
    public function getUserId(): ?int
    {
        return $this->_userIdentity?->getId();
    }

    /**
     * Возвращает атрибут "userName" (имя пользователя).
     * 
     * @return string|null Если значение `null`, пользователь не авторизован. Иначе, 
     *     имя пользователя.
     */
    public function getUserName(): ?string
    {
        return $this->_userIdentity?->getUsername();
    }

    /**
     * Возвращает атрибут "userDetail" (подробная информация о пользователе - имя его в профиле).
     * 
     * @return string|null Если null, пользователь не авторизован. Иначе, 
     *     подробная информация о пользователе.
     */
    public function getUserDetail(): ?string
    {
        return $this->_userProfile?->callName;
    }

    /**
     * Возвращает атрибут "permission" (права доступа пользователя).
     * 
     * @return string|null Если null, пользователь не авторизован. Иначе, 
     *     права доступа пользователя.
     */
    public function getPermission(): ?string
    {
        return $this->_userIdentity?->getBac()?->permission();
    }

    /**
     * Возвращает атрибут "ipAddress" (IP-адрес пользователя).
     * 
     * @return string|null IP-адрес пользователя.
     */
    public function getIpAddress(): ?string
    {
        return $this->request->getUserIp();
    }

    /**
     * Возвращает атрибут "moduleId" (идентификатор модуля).
     * 
     * @return string|null Если значение `null`, то модуль не задействован. Иначе, 
     *     идентификатор модуля.
     */
    public function getModuleId(): ?string
    {
        return $this->getModule()?->id;
    }

    /**
     * Возвращает атрибут "moduleName" (имя модуля).
     * 
     * @return string|null Если значение `null`, то модуль не задействован. Иначе, 
     *     имя модуля.
     */
    public function getModuleName(): ?string
    {
        if ($id = $this->getModule()->id) {
            // т.к. модулем был создан переводчик, попытка определения названия
            $name = $this->translator->translate($id, '{name}');
            if ($name === '{name}') {
                $name = SYMBOL_NONAME;
            }
            return $name;
        }
        return null;
    }

    /**
     * @see Info::getBrowserName()
     * 
     * @var string|null
     */
    protected ?string $browserName;

    /**
     * Возвращает атрибут "browserName" (версия браузера).
     * 
     * Если пользователь авторизован, то возвращает версию браузера на момент авторизации.
     * Иначе, пытается определить.
     * 
     * @return string|null Версия браузера.
     */
    public function getBrowserName(): ?string
    {
        if (!isset($this->browserName)) {
            $this->browserName = $this->_userDevice ? $this->_userDevice?->browserName : Browser::browserName();
        }
        return $this->browserName;
    }

    /**
     * @see Info::getBrowserFamily()
     * 
     * @var string|null
     */
    protected ?string $browserFamily;

    /**
     * Возвращает атрибут "browserFamily" (семейство браузера).
     * 
     * Если пользователь авторизован, то возвращает семейство браузера на момент авторизации.
     * Иначе, пытается определить.
     * 
     * @return string|null Семейство браузера.
     */
    public function getBrowserFamily(): ?string
    {
        if (!isset($this->browserFamily)) {
            return $this->_userDevice ? $this->_userDevice?->browserFamily : Browser::browserFamily();
        }
        return $this->browserFamily;
    }

    /**
     * @see Info::getOsName()
     * 
     * @var string|null
     */
    protected ?string $osName;

    /**
     * Возвращает атрибут "osName" (версия ОС).
     * 
     * Если пользователь авторизован, то возвращает версию ОС на момент авторизации.
     * Иначе, пытается определить.
     * 
     * @return string|null Версия ОС.
     */
    public function getOsName(): ?string
    {
        if (!isset($this->osName)) {
            return $this->_userDevice ? $this->_userDevice?->osName : Browser::platformName();
        }
        return $this->osName;
    }

    /**
     * @see Info::getOsFamily()
     * 
     * @var string|null
     */
    protected ?string $osFamily;

    /**
     * Возвращает атрибут "osFamily" (семейство ОС).
     * 
     * Если пользователь авторизован, то возвращает семейство ОС на момент авторизации.
     * Иначе, пытается определить.
     * 
     * @return string|null Семейство ОС.
     */
    public function getOsFamily(): ?string
    {
        if (!isset($this->osFamily)) {
            return $this->_userDevice ? $this->_userDevice?->osFamily : Browser::platformFamily();
        }
        return $this->osFamily;
    }

    /**
     * Возвращает атрибут "requestUrl" (маршрут HTTP-запроса).
     * 
     * @return string|null Маршрут HTTP-запроса.
     */
    public function getRequestUrl(): ?string
    {
        return Gm::alias('@route');
    }

    /**
     * Возвращает атрибут "requestMethod" (метод HTTP-запроса).
     * 
     * @return string Маршрут HTTP-запроса.
     */
    public function getRequestMethod(): string
    {
        return $this->request->getMethod();
    }

    /**
     * Возвращает атрибут "requestCode" (кода состояния HTTP-ответа).
     * 
     * @return int Код состояния HTTP-ответа.
     */
    public function getRequestCode(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * Возвращает атрибут "queryId" (идентификатор записи в HTTP-запросе).
     * 
     * Идентификатор записи определяется из маршрута запроса или из модели данных, 
     * которой он был обработан. Если идентификатор не определён, возвратит "0".
     * 
     * @return string|int Идентификатор записи в запросе пользователя.
     */
    public function getQueryId()
    {
        if ($controller = $this->controller) {
            /** @var object|null $model */
            $model = $controller->getLastDataModel();
            if ($model && method_exists($model, 'getIdentifier')) {
                $id = $model->getIdentifier();
                $id = is_array($id) ? json_encode($id) : (string) $id;
                if (!is_numeric($id)) {
                    $id = substr($id, 0, 100);
                }
                return $id;
            // если модель данных не зайдествована, попытка 
            // определить идентификатор из маршрута запроса
            } else 
                return (int) Gm::$app->router->get('id');
        }
        return 0;
    }

    /**
     * Возвращает атрибут "query" (переменные HTTP-запроса).
     * 
     * Содержит данные переменных $_GET, $_POST и $_COOKIE в виде строки.
     * 
     * @return string|null Если null, переменные отсутствуют. Иначе, переменные HTTP-запроса.
     */
    public function getQuery(): ?string
    {
        return $_REQUEST ? Str::implodeParameters($_REQUEST, ' = "', '"' . PHP_EOL, 256) : null;
    }

    /**
     * Возвращает атрибут "error" (ошибка возвращаемая HTTP-ответом).
     * 
     * @return string|null Если null, HTTP-запрос отсутствует. Иначе, текст ошибки.
     */
    public function getError(): ?string
    {
        if ($response = $this->response) {
            if ($response->format === 'jsong' && !$response->meta->isSuccess()) {
                return $response->meta->getMsgError();
            }
        }
        return null;
    }

    /**
     * Возвращает атрибут "success" (успех выполнения HTTP-запроса).
     * 
     * @return int Успех выполнения HTTP-запроса.
     */
    public function getSuccess(): int
    {
        if ($response = $this->response) {
            if ($response->format === 'jsong')
                return (int) $response->meta->isSuccess();
            else
                return (int) $response->isOk();
        }
        return 0;
    }

    /**
     * Возвращает описание последнего действия контроллера.
     * 
     * Каждый контроллер имеет описание {@see \Gm\Mvc\Controller\BaseController::translateAction()} 
     * своего действия.
     * 
     * Описание действия контроллера добавляется в комментарий.
     * 
     * @see Info::getComment()
     * 
     * @return string Успех выполнения HTTP-запроса.
     */
    protected function getCommentAction(): string
    {
        $commentAction = '';
        if ($controller = $this->controller) {
            $commentAction = $controller->translateAction($this);
        }
        return $commentAction ?: 'unknow';
    }

    /**
     * Возвращает атрибут "comment" (комментарий к HTTP-запросу).
     * 
     * Если указано замыкание {@see Info::$commentCallback}, то оно будет вызвано.
     * 
     * @return string|null Если null, комментарий отсутствует.
     */
    public function getComment(): ?string
    {
        if ($this->commentCallback instanceof Closure) {
            return $this->commentCallback->call($this);
        }
        return Gm::t(
            BACKEND,
            '{profile} at user account {user} use: {action} from module {module} at {date} from {ipaddress}',
            [
                '@incut',
                'profile'   => $this->userDetail ?: $this->unknown,
                'user'      => $this->userName ?: $this->unknown,
                'action'    =>  $this->getCommentAction(),
                'module'    => $this->moduleName ?: '',
                'date'      => $this->date ? $this->formatter->toDateTime($this->date) . ' (' . $this->formatter->timeZone->getName() . ')' : $this->unknown,
                'ipaddress' => $this->ipaddress,
                'browser'   => $this->getBrowserName(),
                'os'        => $this->getOSName()
            ]
        );
    }

    /**
     * Возвращает атрибут "date" (дата последнего HTTP-запроса).
     * 
     * @return string Дата последнего HTTP-запроса.
     */
    public function getDate(): string
    {
        return  $this->formatter->makeDate('Y-m-d H:i:s', Gm::$app->dataTimeZone);
    }
}
