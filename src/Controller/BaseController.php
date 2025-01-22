<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Controller;

use Gm;
use Gm\Panel\Widget\Docker;
use Gm\Panel\Widget\Binder;
use Gm\Panel\Http\Response;
use Gm\Panel\Widget\BaseWidget;
use Gm\Mvc\Controller\Controller;

/**
 * Базовый класс контроллера реализующий вывод представления (отвечающий за взаимодействие с пользователем).
 * 
 * Контроллер имеет свойства и методы для взаимодействия с представлением.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Controller
 * @since 1.0
 */
class BaseController extends Controller
{
    /**
     * Включает аудит действий пользователя для текущего контроллера.
     * 
     * Внимание: это свойство контролирует только поведение {@see \Gm\Panel\Behavior\AuditBehavior}.
     * Если контроллер не имеет это поведение, то в свойстве нет смысла.
     * 
     * Свойство может принимать значения:
     * - `false`, аудит будет не доступен;
     * - `true` или `null`, работа аудита определяется службой аудита действий 
     * пользователей {@see \Gm\Panel\Audit\Audit}.
     * 
     * @var bool|null
     */
    public ?bool $enableAudit = null;

    /**
     * {@inheritdoc}
     */
    public bool $enableCsrfValidation = true;

    /**
     * Докер.
     * 
     * @see BaseController::getWidgetDocker()
     * 
     * @var Docker
     */
    protected Docker $widgetDocker;

    /**
     * Связующее с виджетом посредством запроса.
     * 
     * @var Binder
     */
    protected Binder $widgetBinder;

    /**
     * Применяется для проверки разрешения для просмотра аудита записей.
     * 
     * @see BaseController::canViewAudit()
     * 
     * @var bool
     */
    protected bool $canViewAudit;

    /**
     * Виджет контроллера, реализующий его интерфейс.
     * 
     * @see BaseController::getWidget()
     * 
     * @var BaseWidget|false
     */
    protected BaseWidget|false $widget;

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'verb' => [
                'class'    => '\Gm\Filter\VerbFilter',
                'autoInit' => true,
                'actions'  => [
                    '*' => ['POST', 'ajax' => 'GJAX']
                ]
            ],
            'audit' => [
                'class'    => '\Gm\Panel\Behavior\AuditBehavior',
                'autoInit' => true,
                'allowed'  => '*',
                'enabled'  => $this->enableAudit
            ]
        ];
    }

    /**
     * Создаёт виджет пользовательского интерфейса панели управления.
     * 
     * @return BaseWidget|false Возвращает значение `false`, если возникла ошибка 
     *     при создании виджета.
     */
    public function createWidget(): BaseWidget|false
    {
        return new BaseWidget();
    }

    /**
     * Возвращает виджет пользовательского интерфейса панели управления.
     * 
     * @return BaseWidget|false Возвращает значение `false`, если возникла ошибка 
     *     при создании виджета.
     */
    public function getWidget(): BaseWidget|false
    {
        if (!isset($this->widget)) {
            $this->widget = $this->createWidget();
        }
        return $this->widget;
    }

    /**
     * Возвращает докер.
     * 
     * @see BaseController::$widgetDocker
     * 
     * @return Docker
     */
    public function getWidgetDocker(): Docker
    {
        if (!isset($this->widgetDocker)) {
            $this->widgetDocker = new Docker($this->module->getStorage());
        }
        return $this->widgetDocker;
    }

    /**
     * Возвращает связующее с виджетом посредством запроса.
     * 
     * @see BaseController::$widgetBinder
     * 
     * @return Binder
     */
    public function getWidgetBinder(): Binder
    {
        if (!isset($this->widgetBinder)) {
            $this->widgetBinder = new Binder($this->getName(), $this->module->getStorage());
        }
        return $this->widgetBinder;
    }

    /**
     * Проверяет, имеет ли пользователь разрешение для просмотра аудита записей.
     * 
     * Аудит записей - регистрация события просмотра и изменения записи пользователем.
     * 
     * Проверка необходима для отображения соответствующих элементов (поля формы, столбцы 
     * сетки и т.д.) представления. Определяется разрешением пользователя
     * {@see \Gm\Data\DataManager::PERMISSION_VIEW_AUDIT} к текущему модулю.
     * 
     * @return bool Возвращает значение `true`, если пользователь имеет разрешение для 
     *     просмотра аудита записей.
     */
    public function canViewAudit(): bool
    {
        if (!isset($this->canViewAudit)) {
            /** @var \Gm\Data\Model\DataModel $model Модель данных */
            $model = $this->getModel($this->defaultModel);
            if ($model === false) {
                return $this->canViewAudit = false;
            }
            /** @var \Gm\Data\DataManager $manager */
            $manager = $model->getDataManager();
            if ($manager)
                $this->canViewAudit = $manager->useAudit && $manager->canViewAudit();
            else
                $this->canViewAudit = false;
        }
        return $this->canViewAudit;
    }

    /**
     * Действие контроллера - view.
     * 
     * Возвращает представление.
     * 
     * @return \Gm\View\BaseView
     */
    public function viewAction()
    {
        return $this->getView();
    }

    /**
     * {@inheritdoc}
     */
    public function getResponse(string $format = null): Response
    {
        if (!isset($this->response)) {
            $this->response = Gm::$app->response;
            if ($format === null) {
                $format = Response::FORMAT_JSONG;
            }
            $this->response->setFormat($format);
        }
        return $this->response;
    }

    /**
     * Добавляет текст ошибки в метаданные, используемые для HTTP-ответа в формате JSONG.
     * 
     * @see \Gm\Panel\Http\Response\JsongMetadata::error()
     * 
     * @param string|array<string> $message Сообщение.
     * @param int $statusCode Код состояния HTTP-ответа от 1xx до 5xx (по умолчанию 400).
     * @param string $status Статус сообщения, заголовок (по умолчанию '').
     * @param null|string $type Тип сообщения (по умолчанию `null`).
     * @param null|string $icon URL-адрес значка. Если не `null`, то `$type = custom ` (по умолчанию `null`).
     * 
     * @return Response
     */
    public function errorResponse(
        string|array $message = '', 
        int $statusCode = 400, 
        string $status = '', 
        ?string $type = null, 
        ?string $icon = null
    ): Response
    {
        $response = $this->getResponse(Response::FORMAT_JSONG);
        $response->setStatusCode($statusCode);
        $response
            ->meta
                ->error($message, $status, $type, $icon);
        return $response;
    }

    /**
     * {@inheritdoc}
     * 
     * @param string|null $default Значение по умолчанию.
     */
    public function translateAction(mixed $params, string $default = null): ?string
    {
        $str = $this->actionName ? Gm::t(BACKEND, "{{$this->actionName} action}") : '';
        return $str ?: $default;
    }
}
