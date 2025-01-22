<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Data\Model;

/**
 * Модель формирования массива сетки строк.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Data\Model
 * @since 1.0
 */
class ArrayGridModel extends BaseGridModel
{
    /**
     * @var string Статический режим наполнения массива.
     */
    public const MODE_STATIC = 'static';

    /**
     * @var string Динамический режим наполнения массива.
     */
    public const MODE_DYNAMIC = 'dynamic';

    /**
     * Режим наполнения массива.
     * 
     * Определяет, каким образом будет выполняться пагинации строк при наполнении 
     * массива.
     * 
     * @var string
     */
    public string $mode = self::MODE_STATIC;

    /**
     * Использовать "прямой" фильтр при получении строк.
     * 
     * В том случае если фильтр не задействован в формировании запроса {@see BaseGridMode::buildQuery()},
     * то он может применяться в получении строки {@see ArrayGridModel::fetchRow()}.
     * Для этого значение должно быть `true`.
     * 
     * @var bool
     */
    public bool $useDirectFilterOnFetch = false;

    /**
     * Общее количество строк в сетке для пагинации.
     * 
     * @see ArrayGridModel::getTotalRows()
     * 
     * @var int
     */
    protected int $totalRows = 0;

    /**
     * {@inheritdoc}
     */
    public function getTotalRows(mixed $receiver = null): int
    {
        return $this->totalRows;
    }

    /**
     * Выполняет фильтрацию значения элемента строки.
     * 
     * @param array<string, mixed> $filter Параметры фильтра. Например:
     * ```php
     * [
     *     'property' => 'foobar', // ключ элемента строки
     *     'value'    => 'sample', // значение для сравнения c элементов строки
     *     'operator' => '=' // оператор сравнения
     * ]
     * ```
     * @param mixed $value Значение элемента строки.
     * @param array<string, mixed> $row Строка элементов с их значениями в виде пар "ключ - значение".
     * 
     * @return bool Возвращает значение `null` если к значению элемента фильтр не применялся.
     */
    public function filterValue(array $filter, mixed $value, array $row): ?bool
    {
        return null;
    }

    /**
     * Выполняет фильтрацию элементов строки.
     *
     * @param array<string, mixed> $row Элементы строки в виде пар "ключ - значение".
     * @param array<int, array> $filters Параметры фильтров. Например:
     * ```php
     * [
     *     [
     *         'property' => 'foobar', // ключ элемента строки
     *         'value'    => 'sample', // значение для сравнения c элементов строки
     *         'operator' => '=' // оператор сравнения
     *     ],
     *     // ...
     * ]
     * ```
     * 
     * @return int Возвращает количество фильтров применимых к элементам строки.
     */
    public function filterRow(array $row, array $filters): int
    {
        $count = 0;
        foreach ($filters as $property => $filter) {
            if (!isset($row[$property])) continue;

            $value = $row[$property];
            /** @var bool|null $hasFilterValue Если фильтр применялся */
            $hasFilterValue = $this->filterValue($filter, $value, $row);
            // если значение использовалось для поиска
            if ($hasFilterValue !== null) {
                if ($hasFilterValue === true) $count++;
                continue;
            }

            switch ($filter['operator']) {
                case 'lt':
                    if ($value < $filter['value']) {
                        $count++;
                    }
                    break;

                case 'gt':
                    if ($value > $filter['value']) {
                        $count++;
                    }
                    break;

                case 'eq':
                case '=':
                case '==':
                    if (is_bool($value)) {
                        $value = $value ? 1 : 0;
                    } else
                    if (is_string($value)) {
                        if ($value === 'true')
                            $value = 1;
                        else
                        if ($value === 'false')
                            $value = 0;
                    }
                    if ($value == $filter['value']) {
                        $count++;
                    }
                    break;

                case 'like':
                    if (strpos($value, $filter['value']) !== false) {
                        $count++;
                    }
                    break;
            }
        }
        return $count;
    }

    /**
     * Возвращает уникальное значение ключа для сортировки строк.
     *
     * @param string $key Ключ одного из элементов строки по каторому выполняется сортировака.
     * @param array|null $row Элементы строки в виде пар "ключ - значение" (по умолчанию `null`).
     *
     * @return string|null Значение ключа.
     */
    public function sortKey(string $key, array $row = null): ?string
    {
        if ($row)
            return isset($row[$key]) ? $row[$key] : null;
        else
            return $key;
    }

    /**
     * Выполняет сортировку строк.
     *
     * @param array $rows Строки.
     * @param null|string $name Имя (ключ) одного из элементов строки по каторому 
     *     выполняется сортировака (по умолчанию `null`).
     * @param null|string $direction Направление (порядок) сортировки {@see BaseGridModel::SORT_ASC}, 
     *     {@see BaseGridModel::SORT_DESC} (по умолчанию `null`).
     *
     * @return array 
     */
    public function sortRows(array $rows, string $name = null, string $direction = null): array
    {
        /** @var null|array $order */
        $order = $this->getOneOrder();

        $name = $name ?: ($order ? $order[0] : null);
        $direction = $direction ?: ($order ? $order[1] : null);

        if (empty($name)) return $rows;

        $result = [];
        $uniqid = 0;
        foreach ($rows as $index => $row) {
            $uniqid++;
            $sortKey = $this->sortKey($name, $row);
            if ($sortKey === null) {
                $result[] = $row;
            } else {
                // т.к. $sortKey может повториться, то
                $result[$sortKey . '@' . $uniqid] = $row;
            }
        }

        if ($direction === self::SORT_ASC)
            krsort($result);
        else
            ksort($result);
        return array_values($result);
    }

    /**
     * {@inheritdoc}
     * 
     * @see ArrayGridModel::fetchStaticRows()
     * @see fetchDynamicRows::fetchStaticRows()
     */
    public function fetchRows(mixed $receiver): array
    {
        if ($this->mode === self::MODE_STATIC) {
            return $this->fetchStaticRows($receiver);
        } 
        if ($this->mode === self::MODE_DYNAMIC) {
            return $this->fetchDynamicRows($receiver);
        }
        return [];
    }

    /**
     * Возвращает строки, полученные в результате запроса и приведение их к нужному 
     * формату в динамическом режиме наполнения.
     * 
     * В динамическом режиме наполнения пагинация строк расчитывается на каждой итерации.
     * 
     * @param mixed $receiver Приёмник или строитель строк.
     * 
     * @return array
     */
    public function fetchDynamicRows(mixed $receiver): array
    {
        $rows = [];
        $index = 0;

        foreach ($receiver as $key => $row) {
            $row = $this->beforeFetchRow($row, $key);
            if ($row === null) continue;

            // если количество применимых фильтров отличается от количестка указанных
            if ($this->useDirectFilterOnFetch && $this->directFilter) {
                if ($this->filterRow($row, $this->directFilter) !== $this->directFilterSize) continue;
            }

            if ($this->fastFilter) {
                // если количество применимых фильтров отличается от количестка указанных
                if ($this->filterRow($row, $this->fastFilter) !== $this->fastFilterSize) continue;
            }

            $index++;
            if ($index <= $this->rangeBegin) continue;
            if ($index > $this->rangeEnd) break;

            $rows[] = $this->fetchRow($row, $key);
        }
        return $rows;
    }

    /**
     * Возвращает строки, полученные в результате запроса и приведение их к нужному 
     * формату в статическом режиме наполнения.
     * 
     * В статическом режиме наполнения все строки приводятся к нужному формату, а потом
     * выполняется пагинация.
     * 
     * @param mixed $receiver Приёмник или строитель строк.
     * 
     * @return array
     */
    public function fetchStaticRows(mixed $receiver): array
    {
        static $i = 0;
        $rows = [];
        foreach ($receiver as $key => $row) {
            $row = $this->beforeFetchRow($row, $key);
            if ($row === null) continue;

            if ($this->useDirectFilterOnFetch && $this->directFilter) {
                // если количество применимых фильтров отличается от количестка указанных
                if ($this->filterRow($row, $this->directFilter) !== $this->directFilterSize) continue;
            }

            if ($this->fastFilter) {
                // если количество применимых фильтров отличается от количестка указанных
                if ($this->filterRow($row, $this->fastFilter) !== $this->fastFilterSize) continue;
            }

            $rows[] = $this->fetchRow($row, $key);
        }
        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function afterFetchRows(array $rows): array
    {
        $rows = $this->sortRows($rows);
        $this->totalRows = sizeof($rows);

        if ($this->mode === self::MODE_STATIC) {
            return array_slice($rows, $this->offset, $this->limit);
        }
        return $rows;
    }

    /**
     * Возвращает строку элементов перед ёё привидением (fetch).
     *
     * @param mixed $row Строка элементов в виде пар "ключ - значение" или объект.
     * @param int|string $rowKey Идентификатор (ключ) строки.
     * 
     * @return array|null Возвращает изменённую строку. Если значение `null`, строка 
     *     элементов будет пропущена на следующем шаге итерации.
     */
    public function beforeFetchRow(mixed $row, int|string $rowKey): ?array
    {
        return $row;
    }

    /**
     * Выполняет привидение строки элементов к нужному формату.
     *
     * @param array $row Строка элементов в виде пар "ключ - значение".
     * @param int|string $rowKey Идентификатор (ключ) строки.
     * 
     * @return array Возвращает строку элементов в нужном формате.
     */
    public function fetchRow(array $row, int|string $rowKey): array
    {
        return $row;
    }
}