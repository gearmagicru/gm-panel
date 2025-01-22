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
use Gm\Db\Sql;

/**
 * Модель сетки данных дерева (при взаимодействии с 
 * представлением, использующий компонент Gm.view.grid.Tree GmJS).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Data\Model
 * @since 1.0
 */
class TreeGridModel extends GridModel
{
    /**
     * Идентификатор узла дерева.
     * 
     * @var string|int|null
     */
    protected string|int|null $nodeId = null;

    /**
     * Проверяет, является ли запрашиваемый элемент в запросе корневым (root).
     * 
     * Если элемент корневой {@see \Gm\Panel\Data\Model\TreeGridModel::isRootNode()}, вывод всех его дочерних элементов.
     * 
     * @var bool
     */
    protected bool $isRootNode;

    /**
     * Раскрыть (получить дочернии элементы для каждого элемента списка) все элементы списка полученные в запросе.
     * 
     * Каждому возвращаемому элементу списка {@see \Gm\Panel\Data\Model\TreeGridModel::afterFetchRow()} указывается атрибут "expanded".
     * 
     * @var bool
     */
    protected bool $expandNodes = false;

    /**
     * Возвращает имя поля количества (дочерних) записей на уровень ниже.
     * 
     * Имя указываемого поля должно совподать с именем 
     * в таблице базы данных.
     * 
     * @return string
     */
    public function countKey(): string
    {
        return $this->dataManager->countKey ?? 'count';
    }

    /**
     * Возвращает имя поля указывающие на идентификатор предка.
     * 
     * Имя указываемого поля должно совподать с именем 
     * в таблице базы данных.
     * 
     * @return string
     */
    public function parentKey(): string
    {
        return $this->dataManager->parentKey ?? 'parent_id';
    }

    /**
     * Возвращает количество потомков.
     * 
     * @param array $row Запись.
     * 
     * @return int
     */
    public function fetchChildCount(array $row): int
    {
        return $row[$this->countKey()] ?? 0;
    }

    /**
     * {@inheritdoc}
     */
    public function afterFetchRow(array $row, array &$rows): void
    {
        // дополнительные параметры узла
        $row[$this->countKey()] = $count = $this->fetchChildCount($row);
        $row['leaf']     = $count > 0 ? 0 : 1;
        $row['expanded'] = $this->expandNodes;
        $rows[] = $row;
    }

    /**
     * {@inheritdoc}
     */
    public function maskedRow(): array
    {
        $fieldAliases = $this->dataManager->fieldAliases;
        // добавление маски поля для $countKey (чтобы не указывать его в списке полей менеджера данных)
        $countKey = $this->countKey();
        $fieldAliases[$countKey] = $countKey;
        return $fieldAliases;
    }

    /**
     * Добавление "быстрой фильтрации" по столбцам в конструктор запроса {@see buildQuery()}.
     *
     * @param Sql\Select|Sql\Query $operator Оператор.
     * @param array $filters Имена полей с их значениями.
     *
     * @return void
     */
    public function buildFastFilter(Sql\AbstractSql $operator, array $filters): void
    {
        if ($this->isRootNode()) {
            parent::buildFastFilter($operator, $filters);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildLimit(Sql\AbstractSql $operator): void
    {
        // если не корневые элементы списка, то нет ограничений
        if (!$this->isRootNode())
            $operator->limit(null);
        else
            $operator->limit($this->limit);
    }

    /**
     * {@inheritdoc}
     */
    public function buildOffset(Sql\AbstractSql $operator): void
    {
        // если не корневые элементы списка, то нет ограничений
        if (!$this->isRootNode())
            $operator->offset(null);
        else
            $operator->offset($this->offset);
    }

    /**
     * {@inheritdoc}
     */
    public function selectNodes(string|int $parentId = null): array
    {
        /** @var \Gm\Db\Sql\Select $select */
        $select = $this->builder()->select($this->tableName());
        $select->quantifier(new \Gm\Db\Sql\Expression('SQL_CALC_FOUND_ROWS'));
        $select->columns(['*']);
        // если корневой элемент 
        if ($this->hasFastFilter() && $this->isRootNode()) {
        // все дочернии элементы для указанного $parentId
        } else
            $select->where([$this->parentKey() => $parentId]);
        /** @var \Gm\Db\Adapter\Driver\AbstractCommand $command */
        $command = $this->buildQuery($select);
        $rows    = $this->fetchRows($command);
        $rows    = $this->afterFetchRows($rows);
        return $this->afterSelect($rows, $command);
    }

    /**
     * {@inheritdoc}
     */
    public function afterSelect(array $rows, mixed $command = null): array
    {
        $this->trigger(self::EVENT_AFTER_SELECT, ['rows' => $rows, 'command' => $command]);
        return [
            'total' => $command ? $command->getFoundRows() : sizeof($rows),
            'nodes' => $rows
        ];
    }

    /**
     * Возвращает идентификатор выбранного (при раскрытии) элемента из списка.
     * 
     * Такой элемент имеет дочернии элементы и является родителем.
     *
     * @return string|int|null
     */
    public function getNodeId(): string|int|null
    {
        if ($this->nodeId === null) {
            $this->nodeId = Gm::$app->request->post('node', null);
        }
        return $this->nodeId;
    }

    /**
     * Проверяет, является ли запрашиваемый элемент в запросе корневым.
     * 
     * Если элемент имеет значение {@see \Gm\Panel\Data\Model\TreeGridModel::getNodeId()} = "root".
     * 
     * @return bool
     */
    public function isRootNode(): bool
    {
        if (!isset($this->isRootNode)) {
            $this->isRootNode = $this->getNodeId() === 'root';
        }
        return $this->isRootNode;
    }

    /**
     * Возвращает все дочернии элементы родителя.
     * 
     * @param string|int|null $parentId Идентификатор родителя.
     * 
     * @return array
     */
    public function getChildNodes(string|int $parentId = null): array
    {
        return $this->selectNodes($parentId);
    }

    /**
     * Возвращает все элементы дерева 1-о уровня.
     *
     * @return array
     */
    public function getNodes(): array
    {
        return $this->selectNodes();
    }

    /**
     * Возвращает все элементы дерева, с определением корневых элементов.
     * 
     * @return array
     */
    public function getTreeNodes(): array
    {
        if ($this->isRootNode())
            return $this->getNodes();
        else
            return $this->getChildNodes($this->getNodeId());
    }
}
