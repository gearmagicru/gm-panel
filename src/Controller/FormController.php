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
use Gm\Panel\Http\Response;
use Gm\Panel\Widget\Widget;
use Gm\Panel\Widget\EditWindow;
use Gm\Panel\Data\Model\FormModel;

/**
 * Контроллер реализующий представление в виде формы с последующей ёё обработкой.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Controller
 * @since 1.0
 */
class FormController extends BaseController
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
    protected string $defaultModel = 'Form';

    /**
     * {@inheritdoc}
     */
    protected string $defaultAction = 'view';

    /**
     * {@inheritdoc}
     */
    public function translateAction(mixed $params, string $default = null): ?string
    {
        switch ($this->actionName) {
            // вывод записи по указанному идентификатору
            case 'data':
                 /** @var FormModel $model */
                $model = $this->lastDataModel;
                if ($model instanceof FormModel)
                    $title = $model->getActionTitle();
                else
                    $title = $params->queryId;
                return Gm::t(BACKEND, "{{$this->actionName} form action}", [$title]);

            // удаление записи(-ей)
            case 'delete':
            // изменение записи по указанному идентификатору
            case 'update':
                return Gm::t(BACKEND, "{{$this->actionName} form action}", [$params->queryId]);

            // вывод интерфейса
            case 'view':
                if (empty($params->queryId))
                    return Gm::t(BACKEND, "{{$this->actionName} add form action}");
                else
                    return Gm::t(BACKEND, "{{$this->actionName} form action}", [$params->queryId]);

            default:
                return parent::translateAction(
                    $params,
                    $default ?: Gm::t(BACKEND, "{{$this->actionName} form action}")
                );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createWidget(): Widget|false
    {
        return new EditWindow();
    }

    /**
     * Действие "view" возвращает интерфейс формы.
     * 
     * @return Response
     */
    public function viewAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var EditWindow|false $widget */
        $widget = $this->getWidget();
        // если была ошибка при формировании виджета
        if ($widget === false) {
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName(), [$this, $widget]);
        }

        $response
            ->setContent($widget->run())
            ->meta
                ->addWidget($widget);
        return $response;
    }

    /**
     * Действие "data" возвращает информацию.
     * 
     * @return Response
     */
    public function dataAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var \Gm\Panel\Data\Model\FormModel|null $model Модель данных */
        $model = $this->getModel($this->defaultModel);
        if ($model === null) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"',[$this->defaultModel]));
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName(), [$this, $model]);
        }

        /** @var \Gm\Panel\Data\Model\FormModel $form запись по идентификатору запроса */
        $form = $model->get();
        if ($form === null) {
            $response
                ->meta->error(
                    $model->hasErrors() ? $model->getError() : Gm::t(BACKEND, 'The item you selected does not exist or has been deleted')
                );
            return $response;
        }

        // предварительная обработка перед возвратом её атрибутов
        $form->processing();
        return $response->setContent($form->getAttributes());
    }

    /**
     * Действие "update" выполняет изменение информации.
     * 
     * @return Response
     */
    public function updateAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();
        /** @var \Gm\Http\Request $request */
        $request  = Gm::$app->request;
        /** @var \Gm\Panel\Data\Model\FormModel $model модель данных */
        $model = $this->getModel($this->defaultModel);
        if ($model === false) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel]));
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName(), [$this, $model]);
        }

        // получение записи по идентификатору в запросе
        $form = $model->get();
        if ($form === null) {
            $response
                ->meta->error(Gm::t(BACKEND, 'No data to perform action'));
            return $response;
        }

        // загрузка атрибутов в модель из запроса
        if (!$form->load($request->getPost())) {
            $response
                ->meta->error(Gm::t(BACKEND, 'No data to perform action'));
            return $response;
        }

        // валидация атрибутов модели
        if (!$form->validate()) {
            $response
                ->meta->error(Gm::t(BACKEND, 'Error filling out form fields: {0}', [$form->getError()]));
            return $response;
        }

        // сохранение атрибутов модели
        if (!$form->save()) {
            // если ошибка была не указана ранее
            if (!$response->meta->isError()) {
                $response
                    ->meta->error(
                        $form->hasErrors() ? $form->getError() : Gm::t(BACKEND, 'Could not save data')
                    );
            }
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName('After'), [$this, $form]);
        }
        return $response;
    }

    /**
     * Действие "delete" выполняет добавление информации.
     * 
     * @return Response
     */
    public function addAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();
        /** @var \Gm\Http\Request $request */
        $request  = Gm::$app->request;

        /** @var \Gm\Panel\Data\Model\FormModel $model модель данных */
        $model = $this->getModel($this->defaultModel);
        if ($model === false) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel]));
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName(), [$this->module, $model]);
        }

        // загрузка атрибутов в модель из запроса
        if (!$model->load($request->getPost())) {
            $response
                ->meta->error(Gm::t(BACKEND, 'No data to perform action'));
        }

        // валидация атрибутов модели
        if (!$model->validate()) {
            $response
                ->meta->error(Gm::t(BACKEND, 'Error filling out form fields: {0}', [$model->getError()]));
            return $response;
        }

        // сохранение атрибутов модели
        if (!$model->save()) {
            // если ошибка была не указана ранее
            if (!$response->meta->isError()) {
                $response
                    ->meta->error(
                        $model->hasErrors() ? $model->getError() : Gm::t(BACKEND, 'Could not add data')
                    );
            }
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName('After'), [$this, $model]);
        }
        return $response;
    }

    /**
     * Действие "delete" выполняет удаление информации.
     * 
     * @return Response
     */
    public function deleteAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var \Gm\Panel\Data\Model\FormModel $model Модель данных */
        $model = $this->getModel($this->defaultModel);
        if ($model === null) {
            $response
                ->meta->error(Gm::t(BACKEND, 'Could not defined data model "{0}"', [$this->defaultModel]));
            return $response;
        }

        // проверка идентификатора в запросе
        if (!$model->hasIdentifier()) {
            $response
                ->meta->error(Gm::t(BACKEND, 'Could not delete record'));
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName(), [$this, $model]);
        }

        // получение записи по идентификатору в запросе
        $form = $model->get();
        if ($form === null) {
            $response
                ->meta->error(Gm::t(BACKEND, 'No data to perform action'));
            return $response;
        }
        // удаление записи
        if ($form->delete() === false) {
            // если ошибка была не указана ранее
            if (!$response->meta->isError()) {
                $response
                    ->meta->error($form->hasErrors() ? $form->getError() : Gm::t(BACKEND, 'Could not delete record'));
            }
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName('After'), [$this, $form]);
        }
        return $response;
    }

    /**
     * Команда клиенту - обновить запись в сетке данных.
     * 
     * @param null|int $rowId Идентификатор записи в сетке данных.
     * @param string $gridName Имя выводимой сетки данных (Gm.view.grid.Grid GmJS) 
     *     для которой создаётся идентификатор (по умолчанию 'grid') {@see \Gm\Mvc\Module::viewId()}.
     * 
     * @return $this
     */
    public function cmdReloadRowGrid(int $rowId = null, string $gridName = 'grid'): static
    {
        if ($rowId === null) {
            $rowId = (int) Gm::$app->router->get('id');
        }
        $this->getResponse()
            ->meta
                ->command('reloadRowGrid', $this->module->viewId($gridName), $rowId);
        return $this;
    }

    /**
     * Команда клиенту - перегрузить (обновить) хранилище (store) сетки данных.
     * 
     * @param string $gridName Имя выводимой сетки данных (Gm.view.grid.Grid GmJS) 
     *     для которой создаётся идентификатор (по умолчанию 'grid') {@see \Gm\Mvc\Module::viewId()}.
     * 
     * @return $this
     */
    public function cmdReloadGrid(string $gridName = 'grid'): static
    {
        $this->getResponse()
            ->meta
                ->command('reloadGrid', $this->module->viewId($gridName));
        return $this;
    }
}
