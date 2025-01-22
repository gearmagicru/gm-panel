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
use Gm\Panel\Data\Model\TreeGridModel;

/**
 * Контроллер реализующий представление в виде элемента управления (поле) с выпадающем 
 * (комбинированным) списком и с последующей его фильтрацией.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Controller
 * @since 1.0
 */
class ComboTriggerController extends TriggerController
{
    /**
     * {@inheritdoc}
     */
    protected string $triggerParam = 'combo';

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
                    '*' => ['GET', 'ajax' => 'GJAX']
                ]
            ]
        ];
    }

    /**
     * Действие "combo" формирует список записей выпадающего списка.
     * 
     * @return Response
     */
    public function comboAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        // определение имени триггера
        $triggerName = $this->defineTriggerName();
        if ($triggerName === null) {
            $response
                ->meta->error(Gm::t('app', 'Invalid parameter passed'));
            return $response;
        }
        return $this->getTriggerResponse($triggerName);
    }

    /**
     * {@inheritdoc}
     */
    public function getTriggerResponse(string $triggerName): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var \Gm\Panel\Data\Model\Combo\ComboModel $model */
        $model = $this->getModel($triggerName);
        if ($model === false) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$triggerName]));
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName(), [$this, $model, $triggerName]);
        }

        if ($model instanceof TreeGridModel) {
            $result = $model->getTreeNodes();
            $response->meta->total = $result['total'];
            $response->setContent($result['nodes']);
        } else {
            $result = $model->getRows();
            $response->meta->total = $result['total'];
            $response->setContent($result['rows']);
        }
        return $response;
    }
}
