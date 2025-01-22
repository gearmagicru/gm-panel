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
use Gm\Panel\Widget\GridDialog;

/**
 * Контроллер реализующий представление в виде диалогового списка записей с осуществлением 
 * их фильтрации и обработки.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Controller
 * @since 1.0
 */
class DialogGridController extends GridController
{
    /**
     * {@inheritdoc}
     */
    public function translateAction(mixed $params, string $default = null): ?string
    {
        switch ($this->actionName) {
            // вывод интерфейса
            case 'view':
                if ($this->queryId) {
                    return Gm::t(BACKEND, "{{$this->actionName} grid row(s) action}", [$params->queryId]);
                }

                default:
                    return parent::translateAction(
                        $params, 
                        $default ?: Gm::t(BACKEND, "{{$this->actionName} form action}")
                    );
        }
    }

    /**
     * Создаёт виджет диалогового окна с сеткой данных.
     * 
     * @return GridDialog
     */
    public function createWidget(): GridDialog
    {
        return new GridDialog();
    }

    /**
     * Действие "view" выводит интерфейса диалога.
     * 
     * @return Response
     */
    public function viewAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var GridDialog|false $widget */
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
}
