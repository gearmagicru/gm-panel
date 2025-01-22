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
use Gm\Stdlib\BaseObject;
use Gm\Panel\Http\Response;
use Gm\Panel\Widget\InfoWindow;

/**
 * Контроллер реализующий представление в виде диалогового окна с 
 * информацией о пользователе.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Controller
 * @since 1.0
 */
class InfoController extends BaseController
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
     * Идентификатор записи из URL запроса.
     * 
     * @var int
     */
    protected int $identifier;

    /**
     * {@inheritdoc}
     */
    public function translateAction(mixed $params, string $default = null): ?string
    {
        switch ($this->actionName) {
            // вывод интерфейса
            case 'view':
                return Gm::t(
                    BACKEND, '{view info action}', [Gm::$app->request->getQuery('title', 'unknow')]
                );

            default:
                return parent::translateAction($params, $default);
        }
    }

    /**
     * Возвращает идентификатор записи из URL запроса.
     * 
     * @return int
     */
    public function getIdentifier(): int
    {
        if (!isset($this->identifier)) {
            $this->identifier = (int) Gm::alias('@match:id');
        }
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function createWidget(): InfoWindow
    {
        return new InfoWindow();
    }

    /**
     * Действие "view" выводит интерфейса модуля.
     * 
     * @return Response
     */
    public function viewAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var InfoWindow|false $widget */
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

        /** @var object|BaseObject|false Модель данных */
        $model = $this->getModel($this->defaultModel);
        if ($model === false) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel]));
            return $response;
        }

        // получение записи по идентификатору в запросе
        $form = $model->get();
        if ($form === null) {
            $response
                ->meta->error(Gm::t(BACKEND, 'Not enough information on the specified record, the user may have been deleted'));
            return $response;
        }

        // предварительная обработка перед возвратом ёё атрибутов
        $form->processing();

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName(), [$this, $form]);
        }
        return $response->setContent($form->getAttributes());;
    }
}
