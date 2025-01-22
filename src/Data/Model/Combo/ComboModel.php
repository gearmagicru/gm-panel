<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Data\Model\Combo;

use Gm;
use Gm\Panel\Data\Model\GridModel;
use Gm\Db\Sql;

/**
 * Модель данных элементов выпадающего (комбинированного) списка 
 * (представления, использующий компонент Gm.form.Combo GmJS).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Data\Model\Combo
 * @since 1.0
 */
class ComboModel extends GridModel
{
    /**
     * Слово поиска.
     * 
     * @var string
     */
    protected string $search = '';

    /**
     * Параметр запроса для поиска.
     * 
     * @var string
     */
    protected string $searchParam = 'q';

    /**
     * Название уникального ключа возвращаемых записей.
     * 
     * @var string
     */
    protected string $key = '';

    /**
     * Параметр уникального ключа записи.
     * 
     * @var string
     */
    protected string $keyParam = 'key';

    /**
     * Доступные ключи.
     * 
     * @var array
     */
    protected array $allowedKeys = ['id' => 'id'];

    /**
     * Добавить запись "без выбора".
     * 
     * @var bool
     */
    protected bool $useNoneRow = true;

    /**
     * Настройки менеджера данных.
     * 
     * Настройки менеджера данных расположены в разделе "dataManager" файла конфигурации модуля.
     * И возвращаются по названию модели данных {@see getName()}.
     * 
     * Для модели выпадающего списка, настройки имееют вид:
     * 
     *  - "tableName"  = "{{название_таблицы}}"
     *  - "primaryKey" = "первичный_ключ"
     *  - "searchBy"   = "имя_поля" // поле которое используется для вывода и поиска записей
     *  - "fields"     = [["field" => "имя_поля",...]...]
     */

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        // служба форматтер
        $this->formatter = Gm::$app->formatter;
        /** @var \Gm\Http\Request $request */
        $request = Gm::$app->request;
        /** @var \Gm\Data\DataManager $manager */
        $manager = $this->getDataManager();

        // слово поиска
        $this->search = $request->getQuery($this->searchParam, '');
        // количество записей на странице
        if (isset($manager->limit)) {
            $this->limit = $manager->limit;
        }
        $this->limit = $request->getQuery('limit', $this->limit);
        // определение вида сортировки полей
        if (isset($manager->order)) {
            $this->order = $manager->order;
        }
        $order = $request->getQuery('sort', $this->order);
        $this->order = $this->defineOrder($order);
        // индекс начала списка записей
        $this->offset = $request->getQuery('start', $this->offset);
        // добавление записи "без выбора"
        $noneRow = $request->getQuery('noneRow', null);
        if ($noneRow !== null) {
            $this->useNoneRow = $noneRow == 1;
        }

        // уникальный ключ записи
        $key = Gm::$app->request->getQuery($this->keyParam);
        $this->key = $this->allowedKeys[$key] ?? 'id';
    }

    /**
     * {@inheritdoc}
     */
    public function buildFilter(Sql\AbstractSql $operator): void
    {
        if ($this->search) {
            $operator->where->like($this->dataManager->searchBy, '%' . $this->search . '%');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetchRows(mixed $receiver = null): array
    {
        $rows = [];
        if ($receiver === null) {
            return $rows;
        }
        while ($row = $receiver->fetch()) {
            $this->beforeFetchRow($row);
            $row = $this->fetchRow($row);
            if ($row === null) {
                continue;
            }
            $this->afterFetchRow($row, $rows);
        }
        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function selectAll(string $tableName = null): array
    {
        /** @var \Gm\Db\Sql\Select $select */
        $select = $this->builder()->select($this->dataManager->tableName);
        $select->quantifier(new Sql\Expression('SQL_CALC_FOUND_ROWS'));
        $select->columns(['*']);

        /** @var \Gm\Db\Adapter\Driver\AbstractCommand $command */
        $command = $this->buildQuery($select);
        $rows = $this->fetchRows($command);
        $rows = $this->afterFetchRows($rows);
        return $this->afterSelect($rows, $command);
    }

    /**
     * {@inheritdoc}
     */
    public function afterFetchRow(array $row, array &$rows): void
    {
        $rows[] = [$row[$this->dataManager->primaryKey], $row[$this->dataManager->searchBy]];
    }

    /**
     * {@inheritdoc}
     */
    public function afterFetchRows(array $rows): array
    {
        if ($this->useNoneRow) {
            array_unshift($rows, $this->noneRow());
        }
        return $rows;
    }

    /**
     * Возвращает запись "без выбора".
     * 
     * @return array
     */
    public function noneRow(): array
    {
        return ['null', Gm::t(BACKEND, '[None]')];
    }
}
