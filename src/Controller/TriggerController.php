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

/**
 * Контроллер представлен в виде триггера обработки запросов пользователей.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Controller
 * @since 1.0
 */
class TriggerController extends BaseController
{
    /**
     * Вызывать события приложения при обращении к действиям контроллера.
     *
     * @var bool
     */
    public bool $useAppEvents = false;

    /**
     * Имена триггеров с именами модулей данных.
     * 
     * @var array<string, string>
     */
    protected array $triggerNames = [];

    /**
     * Имя параметра из запроса методом GET.
     * 
     * Для определения имени модели данных из {@see TriggerController::$triggerNames}.
     * 
     * @var string
     */
    protected string $triggerParam = 'trigger';

    /**
     * Возвращает название триггера из запроса.
     * 
     * @return string|null
     */
    public function defineTriggerName(): ?string
    {
        /** @var \Gm\Http\Request $request */
        $request  = Gm::$app->request;

        $name = $request->getQuery($this->triggerParam);
        return $name ? $this->getTriggerName($name) : null;
    }

    /**
     * Возвращает результат полученный от модели данных в виде HTTP-ответа.
     * 
     * @param string $triggerName Имя триггера.
     * 
     * @return Response
     */
    public function getTriggerResponse(string $triggerName): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var object|\Gm\Stdlib\BaseObject $model */
        $model = $this->getModel($triggerName);
        if ($model === null) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$triggerName]));
            return $response;
        }

        /** @var array $result */
        $result = $model->getItems();
        $response->meta->total = $result['total'];
        $response->setContent($result['items']);
        return $response;
    }

    /**
     * Возвращает название модели данных по указанному триггеру.
     * 
     * @param string $triggerName Имя триггера.
     * 
     * @return string|null
     */
    public function getTriggerName(string $triggerName): ?string
    {
        return $this->triggerNames[$triggerName] ?? null;
    }

    /**
     * Проверяет, существует ли имя триггера.
     * 
     * @param string $triggerName Имя триггера.
     * 
     * @return bool
     */
    public function hasTriggerName(string $triggerName): bool
    {
        return isset($this->triggerNames[$triggerName]);
    }
}
