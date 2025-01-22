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
 * Модель данных формы управления списком смежностей (при взаимодействии с 
 * представлением, использующий компонент Gm.view.form.Panel GmJS).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Data\Model
 * @since 1.0
 */
class AdjacencyFormModel extends FormModel
{
    /**
     * Все доступные (дочернии) идентификаторы списка смежностей.
     * 
     * @var null|int[]
     */
    protected $adjacencyIdentifier;

    /**
     * Список смежностей.
     * 
     * @var null|AdjacencyList
     */
    protected $list;

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
     * 
     * Возвращает список смежностей.
     * 
     * @return AdjacencyList
     */
    protected function getList()
    {
        if ($this->list === null) {
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
    public function afterDelete(false|int|null $result = null): void
    {
        if ($result !== false) {
            $this->getList()->update();
        }

        parent::afterDelete($result);
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave(
        bool $isInsert, 
        array $columns = null, 
        false|int|string|null $result = null
    ): void
    {
        if ($result !== false) {
            $this->getList()->update();
        }

        parent::afterSave($isInsert, $columns, $result);
    }
}
