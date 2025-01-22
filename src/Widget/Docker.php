<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Widget;

use Gm;
use Gm\Exception;
use Gm\Session\Container as StorageContainer;

/**
 * Docker
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class Docker
{
    /**
     * @var string
     */
    public string $name = '';

    /**
     * @var string
     */
    public string $container = '';

    /**
     * @var string
     */
    public string $value = '';

    /**
     * @var array
     */
    protected array $query = [];

   /**
     * Хранилище (контейнер) модуля.
     * 
     * @var StorageContainer
     */
    protected StorageContainer $storage;

    /**
     * Конструктор класса.
     * 
     * @param StorageContainer $storage
     * 
     * @return void
     */
    public function __construct(StorageContainer $storage)
    {
        $this->storage = $storage;

        $this->query = $this->define();
        if ($this->query) {
            $this->name      = $this->query['name'];
            $this->container = $this->query['container'];
            $this->value     = $this->query['value'];
        }
    }

    /**
     * @return bool
     */
    public function hasDock(): bool
    {
        return !empty($this->query);
    }

   /**
     * Возвращает запрос определяющий параметры докера.
     * 
     * Докер - компонент Sencha ExtJS, имеющий контейнер для динамического добавления 
     * в него компонентов.
     * 
     * @return array Если в запросе нет параметров докера, то результатом будет 
     *     пустой массив. Иначе, массив вида:
     * ```php
     * return [
     * ]
     * ```
     */
    public function getQuery(): array
    {
        $docker = Gm::$app->request->get('docker', []);
        if ($docker) {
            $docker = explode(',', $docker);
            if ($docker && sizeof($docker) === 3)
                return [
                    'name'      => $docker[0], // bv
                    'container' => $docker[1],
                    'value'     => $docker[2]
                ];
            else
                throw new Exception\UnexpectedValueException('Docker not specified.');
        }
        return $docker;
    }

    /**
     * @return array
     */
    public function define(): array
    {
        $docker = $this->getQuery();
        if ($docker) {
            $this->storage->docker = $docker;
            return $docker;
        } else {
            if (isset($this->storage->docker)) {
                $docker = $this->storage->docker;
            }
        }
        return $docker;
    }

    /**
     * @param StorageContainer $storage
     * 
     * @return void
     */
    public function setStorage(StorageContainer $storage): void
    {
        $this->storage = $storage;
    }

    /**
     * @return void
     */
    public function forget(): void
    {
        if ($this->storage->docker !== null) {
            unset($this->storage->docker);
        }
    }
}
