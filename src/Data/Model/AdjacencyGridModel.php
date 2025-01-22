<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Data\Model;

use Gm\Data\AdjacencyList;

/**
 * Модель сетки данных вывода списка смежностей (при взаимодействии с 
 * представлением, использующий компонент Gm.view.grid.Grid GmJS).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Data\Model
 * @since 1.0
 */
class AdjacencyGridModel extends TreeGridModel
{
    /**
     * Все доступные (дочернии) идентификаторы списка смежностей.
     * 
     * @var array|null
     */
    protected ?array $adjacencyIdentifier = null;

    /**
     * Список смежностей.
     * 
     * @var AdjacencyList
     */
    protected AdjacencyList $list;

    /**
     * 
     * Возвращает все доступные (дочернии) идентификаторы списка смежностей.
     * 
     * @param null|int $identifier Идентификатор выбранного элемента из списка.
     * 
     * @return int[]
     */
    public function getAdjacencyIdentifier($identifier = 0)
    {
        if ($this->adjacencyIdentifier === null) {
            if (empty($identifier))
                $identifier = $this->getIdentifier();
            $this->adjacencyIdentifier = $this->getList()->getItemsById($identifier);
        }
        return $this->adjacencyIdentifier;
    }

    /**
     * Возвращает список смежностей.
     * 
     * @return AdjacencyList
     */
    protected function getList(): AdjacencyList
    {
        if (!isset($this->list)) {
            $this->list = new AdjacencyList($this->getDataManager());
        }
        return $this->list;
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteProcessCondition(array &$where): void
    {
        /** @var \Gm\Db\Adapter\Adapter $db */
        $db = $this->getDb();
        // если в запросе указан идентификатор
        $identifier = $this->getIdentifier();
        if ($identifier) {
            // для выбранных элементов, находим их дочернии элементы списка смежностей 
            $identifier = $this->getAdjacencyIdentifier($identifier);
            $where[$db->rawExpression($this->dataManager->fullPrimaryKey())] = $identifier;
        }
        // если есть поле "_lock" в таблице
        if ($this->dataManager->lockRows) {
            $where[] = $db->rawExpression($this->tableName() . '._lock <> 1');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete(bool $someRecords = true, mixed $result = null): void
    {
        if ($result !== false) {
            $this->getList()->update();
        }
        parent::afterDelete($someRecords, $result);
    }
}
