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
use Gm\Panel\Widget\TabTree;

/**
 * Контроллер реализующий представление в виде элемента управления вложенными деревьями с 
 * последующем выводом их структуры.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Controller
 * @since 1.0
 */
class TreeController extends BaseController
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
    protected string $defaultAction = 'view';

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
                'data' => ['GET', 'ajax' => 'GJAX'],
                '*'    => ['POST', 'ajax' => 'GJAX']
                ]
            ],
            'audit' => [
                'class'    => '\Gm\Panel\Behavior\AuditBehavior',
                'autoInit' => true,
                'allowed'  => '*'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function translateAction(mixed $params, string $default = null): ?string
    {
        switch ($this->actionName) {
            // вывод записи по указанному идентификатору
            case 'data':
                if ($params->queryId) {
                    return Gm::t(BACKEND, "{{$this->actionName} tree action}", [$params->queryId]);
                };

            default:
                return parent::translateAction(
                    $params,
                    $default ?: Gm::t(BACKEND, "{{$this->actionName} tree action}")
                );
        }
    }

    /**
     * Создаёт виджет вкладки панели c древовидным интерфейсом данных.
     * 
     * @return TabTree
     */
    public function createWidget(): TabTree
    {
        return new TabTree();
    }

    /**
     * Действие "view" выводит интерфейс дерева.
     * 
     * @return Response
     */
    public function viewAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var TabTree $widget */
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
     * Действие "data" выводит записи по указанному идентификатору.
     * 
     * @return Response
     */
    public function dataAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var \Gm\Panel\Data\Model\NodesModel $model Модель данных */
        $model = $this->getModel($this->defaultModel);
        if ($model === false) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel]));
            return $response;
        }

        // получение узлов дерева
        $nodes = $model->getNodes();
        if ($nodes === null) {
            $response
                ->meta->error(Gm::t(BACKEND, 'No data to perform action'));
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName(), [$this, $model, $nodes]);
        }
        return $response->setContent($nodes);
    }
}
