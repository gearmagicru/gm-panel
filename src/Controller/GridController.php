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
use Gm\Panel\Widget\TabGrid;
use Gm\Mvc\Controller\Exception;

/**
 * Контроллер реализующий представление в виде сетки данных с осуществлением их 
 * фильтрации и обработки.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Controller
 * @since 1.0
 */
class GridController extends BaseController
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
     * Идентификатор для формирования "вложенных" моделей представлений.
     * Имеет вид для модели представления "$subViewId - $viewId" или для URL ресурса
     * модуля "...images/$subViewId/".
     * 
     * @var string
     */
    protected string $subViewId = '';

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
    public function translateAction(mixed $params, string $default = null): ?string
    {
        switch ($this->actionName) {
            // удаление записей по указанным идентификаторам
            case 'delete':
            // изменение записи по указанному идентификатору
            case 'update':
            // вывод информации о "раскрытой" записи списка
            case 'expand':
                return Gm::t(BACKEND, "{{$this->actionName} grid row(s) action}", [$params->queryId]);

            default:
                return parent::translateAction(
                    $params,
                    $default ?: Gm::t(BACKEND, "{{$this->actionName} grid row(s) action}")
                );
        }
    }

    /**
     * Создаёт виджет вкладки панели с сеткой данных (Gm.view.grid.Grid GmJS).
     * 
     * @return Widget
     */
    public function createWidget(): Widget
    {
        return new TabGrid();
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

        /** @var TabGrid|false $widget */
        $widget = $this->getWidget();
        // если была ошибка при формировании представления
        if ($widget === false) {
            return $response;
        }

        // сброс "dropdown" фильтра таблицы
        $store = $this->module->getStorage();
        $store->directFilter = null;
 
        // если в конфигурации модели данных указан аудит записей "useAudit" и есть
        // разрешение на просмотр аудита записей, то в вывод списка добавляются соответствующие столбцы
        if ($this->canViewAudit()) {
            $widget->grid->addAuditColumns();
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

        /** @var \Gm\Panel\Data\Model\GridModel $model модель данных*/
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

        /** @var \Gm\Panel\Data\Model\GridModel $model модель данных*/
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

        /** @var \Gm\Panel\Data\Model\GridModel $model модель данных*/
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

        /** @var \Gm\Panel\Data\Model\GridModel $model Модель данных */
        $model = $this->getModel($this->defaultModel);
        if ($model === null) {
            return $this->errorResponse(
                GM_MODE_PRO ? 
                    Gm::t(BACKEND, 'Could not delete record') :
                    Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel])
            );
        }

        // проверка идентификатора в запросе
        if (!$model->hasIdentifier()) {
            return $this->errorResponse(
                GM_MODE_PRO ? 
                    Gm::t(BACKEND, 'Could not delete record') :
                    Gm::t('app', 'Invalid parameter specified "{0}"', ['identifier'])
            );
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName(), [$this, $model]);
        }

        // удаление записей
        if ($model->delete() === false) {
            // если ошибка была указана ранее
            if ($response->meta->isError())
                return $response;
            else
                return $this->errorResponse(Gm::t(BACKEND, 'Could not delete record'));
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

        /** @var \Gm\Panel\Data\Model\GridModel $model модель данных*/
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
            // если не было сообщения об ошибке ранее
            if (!$response->meta->isError()) {
                $response
                    ->meta->error(Gm::t(BACKEND, 'Could not delete record'));
                return $response;
            }
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
        $request  = Gm::$app->request;

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

        // сохранение атрибутов модели
        if (!$form->save()) {
            $response
                ->meta->error(Gm::t(BACKEND, 'Could not save data1'));
            return $response;
        }

        // Т.к. сохранение атрибутов `$form->save()` лишь учитывает возможность их 
        // записи в базу, файл и т.п., но не учитывает реакцию "поведений" (behaviors) 
        // контроллера или модели, то не стоит на неё надеяться.
        // Для этого проверяем последнее событие.
        if ($event = $form->getEvents()->getLastEvent(true)) {
            if (!$event['message']['success']) {
                $response->meta->error($event['message']['message']);
            }
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName(), [$this, $form]);
        }
        return $response;
    }

    /**
     * Действие "expand" возвращает полную информацию о указанной записи.
     * 
     * @return Response
     * 
     * @throws Exception\NotDefinedException Ошибка определения модели данных.
     */
    public function expandAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var \Gm\Panel\Data\Model\FormModel $model Модель данных */
        $model = $this->getModel($this->defaultModel . 'Expand');
        if ($model === null) {
            throw new Exception\NotDefinedException(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel]));
        }

        // идентификатор "раскрытой" записи списка
        $id = Gm::alias('@match:id');
        if (empty($id)) {
            $response
                ->meta->error(Gm::t(BACKEND, 'The item you selected does not exist or has been deleted'));
            return $response;
        }

        /** @var \Gm\Panel\Data\Model\FormModel $form Запись по идентификатору запроса */
        $form = $model->get();
        if ($form === null) {
            $response
                ->meta->error(Gm::t(BACKEND, 'The item you selected does not exist or has been deleted'));
            return $response;
        }

        // предварительная обработка перед возвратом ёё атрибутов
        $form->processing();
        // создание модели представления
        $attributes = $form->getAttributes();
        $attributes['form'] = $form;

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName(), [$this, $form]);
        }
        return $response->setContent(
            $this->getViewManager()->renderPartial('rowInfo', $attributes)
        );
    }

    /**
     * Команда клиенту - обновить запись в сетке (списке) данных.
     * 
     * @param null|int $rowId Идентификатор записи в сетке (списке) данных.
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
        $prefix = $this->subViewId ? $this->subViewId . '-' : '';
        $this->getResponse()
            ->meta
                ->command('reloadRowGrid', $this->module->viewId($prefix . $gridName), $rowId);
        return $this;
    }

    /**
     * Команда клиенту - перегрузить (обновить) хранилище (store) сетки (списка) данных.
     * 
     * @param string $gridName Имя выводимой сетки данных (Gm.view.grid.Grid GmJS) 
     *     для которой создаётся идентификатор (по умолчанию 'grid') {@see \Gm\Mvc\Module::viewId()}.
     * 
     * @return $this
     */
    public function cmdReloadGrid(string $gridName = 'grid'): static
    {
        $prefix = $this->subViewId ? $this->subViewId . '-' : '';
        $this->getResponse()
            ->meta
                ->command('reloadGrid', $this->module->viewId($prefix . $gridName));
        return $this;
    }
}
