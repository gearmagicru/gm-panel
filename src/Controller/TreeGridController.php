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
use Gm\Panel\Widget\TabTreeGrid;

/**
 * Контроллер реализующий представление в виде списка записей с 
 * осуществлением их фильтрации и обработки.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Controller
 * @since 1.0
 */
class TreeGridController extends BaseController
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
    protected string $defaultModel = 'Grid';

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
            // удаление записей по указанным идентификаторам
            case 'delete':
            // изменение записи по указанному идентификатору
            case 'update':
                return Gm::t(BACKEND, "{{$this->actionName} grid row(s) action}", [Gm::$app->request->post('node', '?')]);

            // вывод записей по указанному идентификатору
            case 'data':
                return Gm::t(BACKEND, '{data tree action}', [Gm::$app->request->post('node', '?')]);

            default:
                return parent::translateAction(
                    $params,
                    $default ?: Gm::t(BACKEND, "{{$this->actionName} grid row(s) action}")
                );
        }
    }

    /**
     * Создаёт виджет вкладки панели с Сеткой данных в виде дерева.
     * 
     * @return TabTreeGrid
     */
    public function createWidget(): TabTreeGrid
    {
        return new TabTreeGrid();
    }

    /**
     * Действие "view" возвращает интерфейс сетки данных.
     * 
     * @return Response
     */
    public function viewAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var TabTreeGrid $widget */
        $widget = $this->getWidget();
        // если была ошибка при формировании модели представления
        if ($widget === false) {
            return $response;
        }
        /** @var \Gm\Panel\Data\Model\TreeGridModel $model модель данных*/
        $model = $this->getModel($this->defaultModel);
        if ($model === false) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel]));
            return $response;
        }
        // сброс "dropdown" фильтра таблицы
        $store = $this->module->getStorage();
        $store->directFilter = null;
 
        // если в конфигурации модели данных указан аудит записей "useAudit" и есть
        // разрешение на просмотр аудита записей, то в вывод списка добавляются соответствующие столбцы
        $manager = $model->getDataManager();
        if ($manager->useAudit && $manager->canViewAudit()) {
            $widget->treeGrid->addAuditColumns();
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
     * Действие "filter" выполняет фильтрацию записей.
     * 
     * @return Response
     */
    public function filterAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        // создание модели данных по ёё имени
        /** @var \Gm\Panel\Data\Model\TreeGridModel $model модель данных*/
        $model = $this->getModel($this->defaultModel);
        if ($model === false) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel]));
            return $response;
        }
        // определение "прямого" фильтра
        $model->setDirectFilter();

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName(), [$this, $model]);
        }
        return $response;
    }

    /**
     * Действие "data" возвращает записи сетки данных.
     * 
     * @return Response
     */
    public function dataAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var \Gm\Panel\Data\Model\TreeGridModel $model модель данных*/
        $model = $this->getModel($this->defaultModel);
        if ($model === false) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel]));
            return $response;
        }
        // получение списка записей
        $tree = $model->getTreeNodes();
        // если необходимо запомнить из последнего запроса идентификаторы записей
        if ($model->collectRowsId) {
            $store = $this->module->getStorage();
            $store->rowsId = $model->getCollectedRowsId();
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName(), [$this, $model, $tree]);
        }

        $response->meta->dataProperty = 'children';
        $response->meta->total = $tree['total'];
        $response->meta->isRootNode = $model->hasFastFilter() || $model->isRootNode();
        return $response->setContent($tree['nodes']);
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

        /** @var \Gm\Panel\Data\Model\TreeGridModel $model модель данных*/
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

    /**
     * Действие "delete" выполняет удаление записей по указанным идентификаторам.
     * 
     * @return Response
     */
    public function deleteAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var \Gm\Panel\Data\Model\TreeGridModel $model модель данных*/
        $model = $this->getModel($this->defaultModel);
        if ($model === false) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel]));
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

        // удаление записей
        if ($model->delete() === false) {
            $response
                ->meta->error(Gm::t(BACKEND, 'Could not delete record'));
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName('After'), [$this, $model]);
        }
        return $response;
    }

    /**
     * Действие "clear" выполняет удаление всех записей сетки данных.
     * 
     * @return Response
     */
    public function clearAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var \Gm\Panel\Data\Model\TreeGridModel $model модель данных*/
        $model = $this->getModel($this->defaultModel);
        if ($model === false) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel]));
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName(), [$this, $model]);
        }

        // удаление записей
        if ($model->deleteAll() === false) {
            $response
                ->meta->error(Gm::t(BACKEND, 'Could not delete record'));
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName('After'), [$this, $model]);
        }
        return $response;
    }

    /**
     * Действие "update" выполняет изменение записи по указанному идентификатору.
     * 
     * @return Response
     */
    public function updateAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();
        /** @var \Gm\Http\Request $request */
        $request = Gm::$app->request;

        /** @var \Gm\Panel\Data\Model\FormModel $model модель данных*/
        $model = $this->getModel($this->defaultModel . 'Row');
        if ($model === false) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel . 'Row']));
            return $response;
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

        // сохранение атрибутов модели {
        if (!$form->save()) {
            $response
                ->meta->error(Gm::t(BACKEND, 'Could not save data'));
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName(), [$this, $form]);
        }
        return $response;
    }
}
