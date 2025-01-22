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
 * Binder
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class Binder
{
   /**
     * @var string
     */
    public string $view = '';

   /**
     * @var string
     */
    public string $id = '';

   /**
     * @var string
     */
    public string $value = '';

    /**
     * @var string
     */
    public string $action = '';

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
     * Имя связующего.
     * 
     * Должно быть уникальным для {@see Binder::$storage} хранилища (контейнер) модуля.
     * Чтобы впоследствии не было коллизии, т.к. для одного и того же хранилища
     * может использываться одно связующее.
     * 
     * В контроллер имя определяется через {@see \Gm\Mvc\Controller\BaseController::getName()}.
     * 
     * @var string
     */
    protected string $name;

    /**
     * Конструктор класса.
     * 
     * @param string $name
     * @param StorageContainer $storage
     * 
     * @return void
     */
    public function __construct(string $name, StorageContainer $storage)
    {
        $this->storage = $storage;
        $this->name = 'bind' . $name;

        $this->query = $this->define();
        if ($this->query) {
            $this->view   = $this->query['view'];
            $this->id     = $this->query['id'];
            $this->value  = $this->query['value'];
            $this->action = $this->query['action'];
        }
    }

    /**
     * @return bool
     */
    public function hasBind(): bool
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
        $bind = Gm::$app->request->get('bind', []);
        if ($bind) {
            $bind = explode(',', $bind);
            if (sizeof($bind) < 2) {
                throw new Exception\UnexpectedValueException('Bind not specified.');
            }
            return [
                'view'   => $bind[0], // view
                'id'     => $bind[1], // id
                'value'  => $bind[2] ?? '', // value
                'action' => $bind[3] ?? '' // action
            ];        
        }
        return $bind;
    }

    /**
     * @param string $view
     * @param string $id
     * @param string|null $value
     * @param string|null $action
     * 
     * @return string
     */
    public function queryParam(string $view, string $id, string $value = null, string $action = null): string
    {
        $query = "bind=$view,$id";
        if ($value !== null) {
            $query .= ',' . $value;
        }
        if ($action !== null) {
            $query .= ',' . $action;
        }
        return $query;
    }

    /**
     * @return array
     */
    public function define(): array
    {
        $name = $this->name;
        $bind = $this->getQuery();
        if ($bind) {
            $this->storage->{$name} = $bind;
        } else {
            if (isset($this->storage->{$name})) {
                $bind = $this->storage->{$name};
            }
        }
        return $bind;
    }

    /**
     * @param StorageContainer $storage
     * 
     * @return void
     */
    public function setStorage(StorageContainer $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return void
     */
    public function forget(): void
    {
        $name = $this->name;
        if ($this->storage->{$name} !== null) {
            unset($this->storage->{$name});
        }
    }
}
