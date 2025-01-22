<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Data\Model;

use Gm;
use Gm\Http\Response;
use Gm\Config\Config;
use Gm\Data\DataManager;
use Gm\Stdlib\BaseObject;
use Gm\Cache\StorageFactory;
use Gm\Mvc\Module\BaseModule;
use Gm\Mvc\Controller\BaseController;

/**
 * Базовая модель сетки данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Data\Model
 * @since 1.0
 */
class BaseModel extends BaseObject
{
    /**
     * Менеджер данных.
     * 
     * @var DataManager|null
     */
    public ?DataManager $dataManager;

    /**
     * Модуль приложения.
     *
     * @var BaseModule|null
     */
    public ?BaseModule $module = null;

    /**
     * Временное хранилище модели данных.
     *
     * @var null|StorageFactory
     */
    public $storage;

    /**
     * Настройки модели данных.
     *
     * @var Config|null
     */
    public ?Config $settings;

    /**
     * @inheritdoc
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->init();
    }

    /**
     * Инициализация модели данных.
     * 
     * @return void
     */
    public function init(): void
    {
        $this->getDataManager();
    }

    /**
     * Возвращает настройки модуля.
     * 
     * @return Config|null
     */
    public function getSettings(): ?Config
    {
        return $this->module ? $this->module->getSettings() : null;
    }

    /**
     * Возвращает название модели данных.
     * 
     * @return string
     */
    public function getModelName(): string
    {
        return $this->getReflection()->getShortName();
    }

    /**
     * Возвращает параметры конфигурации менеджера данных.
     * 
     * Настройки менеджера данных расположены в разделе "dataManager" файла конфигурации модуля.
     * И возвращаются по названию модели данных {@see BaseModel::getModelName()}.
     * 
     * @return array
     */
    public function getDataManagerConfig(): array
    {
        $dataManager = $this->module->getConfigParam('dataManager');
        return $dataManager[$this->getModelName()] ?? [];
    }

    /**
     * Возвращает менеджер данных.
     * 
     * @see BaseModel::createDataManager()
     * 
     * @return DataManager|null
     */
    public function getDataManager(): ?DataManager
    {
        if (!isset($this->dataManager)) {
            $this->dataManager = $this->createDataManager();
        }
        return $this->dataManager;
    }

    /**
     * Создаёт менеджер данных.
     * 
     * @see BaseModel::getDataManagerConfig()
     * 
     * @return DataManager|null
     */
    public function createDataManager(): ?DataManager
    {
        $config = $this->getDataManagerConfig();
        return $config ? new DataManager($config, $this) : null;
    }

    /**
     * Возвращает параметры конфигурации временного хранилища модели данных.
     * 
     * @return array
     */
    public function getStorageConfig(): array
    {
        return [
            'adapter' => [
                'name'    => 'session',
                'options' => [
                    'namespace' => 'sample_namespace',
                ]
            ]
        ];
    }

    /**
     * Возвращает сессионное хранилище модели данных.
     * 
     * @see BaseModel::getStorageConfig()
     * 
     * @return StorageFactory
     */
    public function getStorage()
    {
        if ($this->storage === null) {
            $this->storage = $this->createStorage();
        }
        return $this->storage;
    }

    /**
     * Создаёт сессионное хранилище для модели данных.
     * 
     * Параметры хранилища ресурсов определяется из {@see BaseModel::getStorageConfig()}.
     * 
     * @return StorageFactory
     */
    public function createStorage()
    {
        return StorageFactory::factoryBySession($this->getStorageConfig(), Gm::getName('_DataModel'), Gm::$app->session);
    }

    /**
     * Возвращает текущий контроллер модуля.
     * 
     * @see \Gm\Mvc\Module\BaseModule::controller()
     * 
     * @return BaseController|null
     */
    public function controller(): ?BaseController
    {
        return $this->module ? $this->module->controller() : null;
    }

    /**
     * Возвращает текущий HTTP-ответ контроллера.
     * 
     * @see \Gm\Mvc\Controller\BaseController::getResponse()
     * 
     * @return Response|null
     */
    public function response(): ?Response
    {
        return $this->module ? $this->module->controller()->getResponse() : null;
    }

    /**
     * Выполняет перевод (локализацию) сообщения или сообщений.
     * 
     * @param string|array<int, string> $message Текст сообщения (сообщений).
     * @param array<string, string> $params Параметры перевода (по умолчанию `[]`).
     * @param string $locale Код локализации, например: 'ru_RU', 'en_GB'. Если 
     *     значение '', то применяется текущая локализация (по умолчанию '').
     * 
     * @return string|array
     */
    public function t(string|array $message, array $params = [], string $locale = ''): string|array
    {
        return $this->module->t($message, $params, $locale);
    }
}
