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
use Gm\Helper\Json;
use Gm\Panel\Http\Response;
use Gm\Panel\Widget\GridBrowseDialog;

/**
 * Контроллер реализующий выбор записей из таблицы диалогового окна.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Controller
 * @since 1.0
 */
class BrowseGridController extends BaseController
{
    /**
     * Вызывать события приложения при обращении к действиям контроллера.
     *
     * @var bool
     */
    public bool $useAppEvents = false;

    /**
     * {@inheritdoc}
     */
    protected string $defaultModel = 'Browse';

    /**
     * {@inheritdoc}
     */
    protected string $defaultAction = 'view';

    /**
     * {@inheritdoc}
     */
    public bool $enableCsrfValidation = true;

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        // не учитываем для действия "dataAction"
        $behaviors['audit']['deny'] = 'data';
        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function createWidget(): GridBrowseDialog
    {
        return new GridBrowseDialog();
    }

    /**
     * Действие "pickup" вызывается при выборе записей из таблицы диалогового окна.
     * 
     * @return Response
     */
    public function pickupAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();
        /** @var \Gm\Http\Request $request */
        $request = Gm::$app->request;

        /** 
         * @var \Gm\Panel\Widget\Binder $binder Связующее с элементом интерфейса (который 
         * вызвал диалоговое окно). 
         */
        $binder = $this->getWidgetBinder();
        if (!$binder->hasBind()) {
            $response
                ->meta->error('No bind to interface element specified.');
            return $response;
        }

        /** @var string $pickup выбранные записи в формате JSON */
        $pickup = $request->getPost('pickup', '');
        if (empty($pickup)) {
            $response
                ->meta->error(Gm::t(BACKEND, 'Entry must be selected'));
            return $response;
        }

        // попытка декодировать в массив значений
        $pickup = Json::tryDecode($pickup);
        if (empty($pickup)) {
            $response
                ->meta->error(Gm::t(BACKEND, 'Entry must be selected'));
        }

        /** @var \Gm\Panel\Data\Model\Browse $model Модель данных */
        $model = $this->getModel($this->defaultModel);
        if ($model === false) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel]));
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName(), [$this, $model, $pickup, $binder]);
        }

        // определить выбранные записи
        $model->pickup($pickup, $binder, $response);
        return $response;
    }

    /**
     * Действие "view" выводит интерфейс диалогового окна.
     * 
     * @return Response
     */
    public function viewAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** 
         * @var \Gm\Panel\Widget\Binder $binder Связующее с элементом интерфейса (который 
         * вызвал диалоговое окно). Выполняем его вызов для сохранения и передаче параметров. 
         */
        $binder = $this->getWidgetBinder();
        if (!$binder->hasBind()) {
            $response
                ->meta->error('No bind to interface element specified.');
            return $response;
        }

        /** @var \Gm\Panel\Data\Model\Browse $model Модель данных */
        $model = $this->getModel($this->defaultModel);
        if ($model === false) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel]));
            return $response;
        }

        /** @var GridBrowseDialog|false $widget */
        $widget = $this->getWidget();
        // если была ошибка при формировании виджета
        if ($widget === false) {
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName(), [$this, $widget, $binder]);
        }

        $response
            ->setContent($widget->run())
            ->meta
                ->addWidget($widget);
        return $response;
    }

    /**
     * Действие "filter" выполняет фильтрацию записей диалогового окна.
     * 
     * @return Response
     */
    public function filterAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var \Gm\Panel\Data\Model\Browse $model Модель данных */
        $model = $this->getModel($this->defaultModel);
        if ($model === false) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel]));
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName(), [$this, $model]);
        }
        return $response;
    }

    /**
     * Действие "data" выводит список записей диалогового окна.
     * 
     * @return Response
     */
    public function dataAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var \Gm\Panel\Data\Model\Browse $model Модель данных */
        $model = $this->getModel($this->defaultModel);
        if ($model === false) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel]));
            return $response;
        }

        // получение списка записей
        $grid = $model->getRows();

        // если необходимо запомнить из последнего запроса идентификаторы записей
        if ($model->collectRowsId) {
            $store = $this->module->getStorage();
            $store->rowsId = $model->getCollectedRowsId();
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName(), [$this, $model, $grid]);
        }

        $response
            ->meta->total = $grid['total'];
        return $response->setContent($grid['rows']);
    }

    /**
     * Действие "supplement" возвращает дополнение к записям сетки данных.
     * 
     * @return Response
     */
    public function supplementAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var \Gm\Panel\Data\Model\Browse $model Модель данных */
        $model = $this->getModel($this->defaultModel);
        if ($model === false) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel]));
            return $response;
        }

        // если запомнили из последнего запроса идентификаторы записей
        if ($model->collectRowsId) {
            $store = $this->module->getStorage();
            $model->rowsId = $store->rowsId;
        }

        // получение дополнительных записей
        $rows = $model->getSupplementRows();

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName(), [$this, $model, $rows]);
        }
        return $response->setContent($rows);
    }
}
