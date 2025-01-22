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
use Gm\Exception;
use Gm\Helper\Json;

/**
 * Базовая модель сетки данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Data\Model
 * @since 1.0
 */
class BaseGridModel extends BaseModel
{
    /**
     * @var string Сортировка в порядке возрастания.
     */
    public const SORT_ASC = 'ASC';

    /**
     * @var string Сортировка в порядке убывания.
     */
    public const SORT_DESC = 'DESC';

    /**
     * @var string Событие, возникшее после успешного получения строки по запросу.
     */
    public const EVENT_AFTER_SELECT = 'afterSelect';

    /**
     * @var string Событие, возникшее перед удалением строк.
     */
    public const EVENT_BEFORE_DELETE = 'beforeDelete';

    /**
     * @var string Событие, возникшее после удаления строк.
     */
    public const EVENT_AFTER_DELETE = 'afterDelete';

    /**
     * @var string Событие перед установкой фильтра.
     */
    public const EVENT_BEFORE_SET_FILTER = 'beforeSetFilter';

    /**
     * @var string Событие процесса установки фильтра.
     */
    public const EVENT_ON_SET_FILTER = 'onSetFilter';

    /**
     * @var string Событие, возникшее после установки фильтра.
     */
    public const EVENT_AFTER_SET_FILTER = 'afterSetFilter';

    /**
     * Количество строк на странице сетки.
     * 
     * Передаётся через параметр HTTP-запросом.
     * 
     * @var int
     */
    public int $limit;

    /**
     * Параметр передаваемый HTTP-запросом, указывающий количество элементов на странице.
     * 
     * Параметр передаётся с помощью метода POST и определяется {@see BaseGridModel::defineLimit()}.
     * Если значение параметра `false`, тогда будет применяться значение {@see BaseGridModel::$defaultLimit}.
     * 
     * @var string|false
     */
    public string|false $limitParam = 'limit';

    /**
     * Фильтр количества элементов.
     * 
     * Применяется для фильтрации количества элементов передаваемых в HTTP-запросе.
     * Если фильтр не указан, будет использоваться любое количества элементов в HTTP-запросе.
     * 
     * Пример фильтра: `[10, 20, 30, ...]`.
     * 
     * @var array
     */
    public array $limitFilter = [];

    /**
     * Максимальное допустимое количество выводимых элементов.
     * 
     * Применяется для проверки в том случаи, если значение отличное от '0' и не 
     * установлен фильтр количества элементов {@see BaseGridModel::$limitFilter}.
     * 
     * @var int
     */
    public int $maxLimit = 0;

    /**
     * Определяет, что парамтер $limit получен из HTTP-запроса.
     * 
     * @see BaseGridModel::defineLimit()
     * 
     * @var bool
     */
    protected bool $hasLimit = false;

    /**
     * Значение количества выводимых элементов по умолчанию.
     * 
     * Используется в том случаи, если значение параметра {@see BaseGridModel::$limitParam} 
     * отсутствует в HTTP-запросе.
     * 
     * @var int
     */
    public int $defaultLimit = 10;

    /**
     * Текущая страница сетки.
     * 
     * Передаётся через параметр HTTP-запросом.
     * 
     * @var int
     */
    public int $page;

    /**
     * Параметр передаваемый HTTP-запросом, указывающий на текущую страницу.
     * 
     * Параметр передаётся с помощью метода POST и определяется {@see BaseGridModel::definePage()}.
     * Если значение параметра `false`, тогда будет применяться значение {@see BaseGridModel::$defaultPage}.
     * 
     * @var string|false
     */
    public string|false $pageParam = 'param';

    /**
     * Определяет, что парамтер $page получен из HTTP-запроса.
     * 
     * @see BaseGridModel::definePage()
     * 
     * @var bool
     */
    protected bool $hasPage = false;

    /**
     * Значение текущей страницы по умолчанию.
     * 
     * Используется в том случаи, если значение параметра {@see BaseGridModel::$pageParam} 
     * отсутствует в HTTP-запросе.
     * 
     * @var int
     */
    public int $defaultPage = 1;

    /**
     * Индекс смещения относительно начала.
     * 
     * Передаётся через параметр HTTP-запросом.
     * 
     * @var int
     */
    public int $offset;

    /**
     * Параметр передаваемый HTTP-запросом, указывающий на смещение относительно начала 
     * элементов сетки.
     * 
     * Параметр передаётся с помощью метода POST и определяется {@see BaseGridModel::defineOffset()}.
     * Если значение параметра `false`, тогда будет применяться значение {@see BaseGridModel::$defaultOffset}.
     * 
     * @var string|false
     */
    public string|false $offsetParam = 'start';

    /**
     * Определяет, что парамтер $offset получен из HTTP-запроса.
     * 
     * @see BaseGridModel::defineOffset()
     * 
     * @var bool
     */
    protected bool $hasOffset = false;

    /**
     * Значение смещение относительно начала элементов сетки по умолчанию.
     * 
     * Используется в том случаи, если значение параметра {@see BaseGridModel::$offsetParam} 
     * отсутствует в HTTP-запросе.
     * 
     * @var int
     */
    public int $defaultOffset = 0;

    /**
     * Сортировка строк в сетке.
     * 
     * Передаётся через параметр HTTP-запросом.
     * 
     * @var array
     */
    public array $order;

    /**
     * Параметр передаваемый HTTP-запросом, указывающий на сортировку строк в сетке.
     * 
     * Параметр передаётся с помощью метода POST и определяется {@see BaseGridModel::defineOrder()}.
     * Если значение параметра `false`, тогда будет применяться значение {@see BaseGridModel::$defaultOrder}.
     * 
     * @var string|false
     */
    public string|false $orderParam = 'sort';

    /**
     * Определяет, что парамтер $order получен из HTTP-запроса.
     * 
     * @see BaseGridModel::defineOrder()
     * 
     * @var bool
     */
    protected bool $hasOrder = false;

    /**
     * Порядок cортировку строк в сетке по умолчанию.
     * 
     * Используется в том случаи, если значение параметра {@see BaseGridModel::$orderParam} 
     * отсутствует в HTTP-запросе.
     * 
     * @var array
     */
    public array $defaultOrder = [];

    /**
     * Собирать идентификаторы строк после каждого запроса.
     * 
     * Идентификаторы собираются {@see BaseGridModel::$collectedRowsId} методом 
     * {@see BaseGridModel::fetchRows()} и используются для вспомогательных запросов.
     * 
     * @var bool
     */
    public bool $collectRowsId = false;

    /**
     * Собранные идентификаторы строк.
     * 
     * @see BaseGridModel::getCollectedRowsId()
     * 
     * @var array
     */
    protected array $collectedRowsId = [];

    /**
     * Идентификатор(ы) выбранных строк в сетке.
     * 
     * Передаётся через параметр HTTP-запросом.
     * 
     * @var array
     */
    public array $rowsId;

    /**
     * Параметр передаваемый HTTP-запросом, указывающий на идентификаторы выбранных строк.
     * 
     * Параметр передаётся с помощью метода POST и определяется {@see BaseGridModel::defineRowsId()}.
     * 
     * @var string|false
     */
    public string|false $rowsIdParam = 'id';

    /**
     * Определяет, что парамтер $rowsId получен из HTTP-запроса.
     * 
     * @see BaseGridModel::defineRowsId()
     * 
     * @var bool
     */
    protected bool $hasRowsId = false;

    /**
     * "Быстрый" фильтр через столбец сетки.
     * 
     * @var array
     */
    protected array $fastFilter;

    /**
     * Размерность "Быстрого" фильтр через столбец сетки.
     * 
     * Количество фильтров применяемых для фильтрации элементов строки.
     * 
     * @var int
     */
    protected int $fastFilterSize = 0;

    /**
     * Параметр передаваемый HTTP-запросом, указывающий на быструю фильтрацию строк в сетке.
     * 
     * Параметр передаётся с помощью метода POST и определяется {@see BaseGridModel::defineFastFilter()}.
     * Если значение параметра `false`, тогда будет применяться значение `[]`.
     * 
     * @var string|false
     */
    public string|false $fastFilterParam = 'filter';

    /**
     * Определяет, что парамтер $fastFilter получен из HTTP-запроса.
     * 
     * @see BaseGridModel::defineFastFilter()
     * 
     * @var bool
     */
    protected bool $hasFastFilter = false;

    /**
     * "Прямой" фильтр через запрос (формы).
     * 
     * @var array
     */
    protected array $directFilter;

    /**
     * Размерность "Прямого" фильтр через запрос (формы).
     * 
     * Количество фильтров применяемых для фильтрации элементов строки.
     * 
     * @var array
     */
    protected int $directFilterSize = 0;

    /**
     * Начало диапазона строк.
     * 
     * @see BaseGridModel::defineRange()
     * 
     * @var int
     */
    protected int $rangeBegin = 0;

    /**
     * Конец диапазона строк.
     * 
     * @see BaseGridModel::defineRange()
     * 
     * @var int
     */
    protected int $rangeEnd = 0;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this->rowsId = $this->defineRowsId();
        $this->limit  = $this->defineLimit();
        $this->offset = $this->defineOffset();
        $this->defineRange();
        $this->page   = $this->definePage();
        $this->order  = $this->defineOrder();
        $this->fastFilter   = $this->defineFastFilter();
        $this->directFilter = $this->defineDirectFilter();
        $this->fastFilterSize   = sizeof($this->fastFilter);
        $this->directFilterSize = sizeof($this->directFilter);

        /**
         * Здесь можно выполнить инициализацию событий модели:
         * Пример событий: выборки и удаление строк.
         * 
         * $this
         *  ->on(self::EVENT_AFTER_SELECT, function ($rows, $receiver) {
         *       ...
         *   })
         *  ->on(self::EVENT_AFTER_DELETE, function ($someRows) {
         *      ...
         *   });
         */
    }

    /**
     * Возвращает собранные идентификаторы строк.
     * 
     * Собранные идентификаторы строк применяются для вспомогатльных запросов.
     * Такие идентификаторы необходимо собирать с помощью метода {@see BaseGridModel::fetchRows()}.
     * 
     * @see BaseGridModel::$collectedRowsId
     * 
     * @return array
     */
    public function getCollectedRowsId(): array
    {
        return $this->collectedRowsId;
    }

    /**
     * Определяет идентификаторы выбранных строк из сетки.
     * 
     * Если значение указано в параметрах конфигурации, тогда возвратит {@see BaseGridModel::$rowsId};
     * 
     * @see BaseGridModel::$rowsId
     * 
     * @return array
     */
    protected function defineRowsId(): array
    {
        // если значение указано в параметрах конфиграции
        if (isset($this->rowsId)) {
            return $this->rowsId;
        }

        // если запрещено получать значение из HTTP-запроса
        if ($this->rowsIdParam === false) {
            return [];
        }

        $rowsId = Gm::$app->request->getPost($this->rowsIdParam);
        if ($rowsId) {
            $rowsId = explode(',', $rowsId);
        } else
            return [];
        // параметр был получен из запроса
        $this->hasRowsId = true;
        return $rowsId;
    }

    /**
     * Определяет количество строк на странице.
     * 
     * - если значение указано в параметрах конфигурации, тогда возвратит {@see BaseGridModel::$limit};
     * - если не указан параметр запроса или сам параметр отсутствует в запросе, 
     * тогда возвратит {@see BaseGridModel::$defaultLimit};
     * - если значение параметра не входит в указанный фильтр или является не допустимым, 
     * тогда возвратит {@see BaseGridModel::$defaultLimit}.
     * 
     * @see BaseGridModel::$limit
     * 
     * @return int
     */
    protected function defineLimit(): int
    {
        // если значение указано в параметрах конфиграции
        if (isset($this->limit)) {
            return $this->limit;
        }

        if ($this->dataManager && isset($this->dataManager->limit))
            $defaultLimit = $this->dataManager->limit;
        else
            $defaultLimit = $this->defaultLimit;

        // если запрещено получать значение из HTTP-запроса
        if ($this->limitParam === false) {
            return $defaultLimit;
        }

        $limit = Gm::$app->request->getPost($this->limitParam, -1, 'int');
        if ($limit === -1) {
            return $defaultLimit;
        }
        // параметр был получен из запроса
        $this->hasLimit = true;

        $limit = (int) $limit;
        if ($limit <= 1) {
            return $defaultLimit;
        }

        if ($this->limitFilter) {
            if (!in_array($limit, $this->limitFilter)) {
                return $defaultLimit;
            }
        } else {
            if ($this->maxLimit && $limit > $this->maxLimit) {
                return $defaultLimit;
            }
        }
        return $limit;
    }

    /**
     * Определяет диапазон (начало и конец) вывода строк.
     * 
     * Начало {@see BaseGridModel::$rangeBegin} диапазона определеляется смещением 
     * относительно начала списка строк {@see BaseGridModel::$offset}.
     * Конец {@see BaseGridModel::$rangeEnd} диапазона определеляется количеством 
     * выводимых строк на странице {@see BaseGridModel::$limit}.
     * 
     * @return void
     */
    protected function defineRange(): void
    {
        $this->rangeBegin = $this->offset;
        $this->rangeEnd = $this->rangeBegin + $this->limit;
    }

    /**
     * Определяет индекс смещения относительно начала.
     * 
     * - если значение указано в параметрах конфигурации, тогда возвратит {@see BaseGridModel::$offset};
     * - если не указан параметр запроса или сам параметр отсутствует в запросе, 
     * тогда возвратит {@see BaseGridModel::$defaultOffset};
     * - если значение параметра является не допустимым, 
     * тогда возвратит {@see BaseGridModel::$defaultOffset}.
     * 
     * @see BaseGridModel::$offset
     * 
     * @return int
     */
    protected function defineOffset(): int
    {
        // если значение указано в параметрах конфиграции
        if (isset($this->offset)) {
            return $this->offset;
        }

        if ($this->dataManager && isset($this->dataManager->offset))
            $defaultOffset = $this->dataManager->offset;
        else
            $defaultOffset = $this->defaultOffset;

        // если запрещено получать значение из HTTP-запроса
        if ($this->offsetParam === false) {
            return $defaultOffset;
        }

        $offset = Gm::$app->request->getPost($this->offsetParam, -1, 'int');
        if ($offset === -1) {
            return $defaultOffset;
        }
        // параметр был получен из запроса
        $this->hasOffset = true;

        $offset = (int) $offset;
        if ($offset < 0) {
            return $defaultOffset;
        }
        return $offset;
    }

    /**
     * Определяет индекс смещения относительно начала.
     * 
     * - если значение указано в параметрах конфигурации, тогда возвратит {@see BaseGridModel::$offset};
     * - если не указан параметр запроса или сам параметр отсутствует в запросе, 
     * тогда возвратит {@see BaseGridModel::$defaultOffset};
     * - если значение параметра является не допустимым, 
     * тогда возвратит {@see BaseGridModel::$defaultOffset}.
     * 
     * @see BaseGridModel::$offset
     * 
     * @return int
     */
    protected function definePage(): int
    {
        // если значение указано в параметрах конфиграции
        if (isset($this->page)) {
            return $this->page;
        }

        if ($this->dataManager && isset($this->dataManager->page))
            $defaultPage = $this->dataManager->page;
        else
            $defaultPage = $this->defaultPage;

        // если запрещено получать значение из HTTP-запроса
        if ($this->pageParam === false) {
            return $defaultPage;
        }

        $page = Gm::$app->request->getPost($this->pageParam, -1, 'int');
        if ($page === -1) {
            return $defaultPage;
        }
        // параметр был получен из запроса
        $this->hasPage = true;

        $page = (int) $page;
        if ($page < 0) {
            return $defaultPage;
        }
        return $page;
    }

    /**
     * Выполняет преобразование массива параметров в формат сортировки строк.
     * 
     * Например: `['alias' => 'ASC', ...] => ['field' => 'ASC', ...]`.
     * 
     * @param array $order Массив с параметрами сортировки.
     * 
     * @return array
     */
    protected function formatOrderArray(array $order): array
    {
        if ($this->dataManager) {
            $format = [];
            foreach ($order as $alias => $direction) {
                $field = $this->dataManager->getFullField($alias);
                if ($field !== null) {
                    $format[$field] = $direction;
                }
            }
            return $format;
        }
        return $order;
    }

    /**
     * Выполняет преобразование JSON параметров в формат сортировки строк.
     * 
     * Например: `'{"alias":"ASC", ...}' => ['field' => 'ASC', ...]`.
     * 
     * @param string $order Массив с параметрами сортировки.
     * 
     * @return array
     */
    protected function formatOrderJson(string $order): ?array
    {
        $order = Json::decode($order);
        if (Json::error()) {
            // TODO: debug
            return null;
        }

        if ($this->dataManager) {
            $format = [];
            foreach ($order as $alias => $direction) {
                $field = $this->dataManager->getFullField($alias);
                if ($field !== null) {
                    $format[$field] = $direction;
                }
            }
            return $format;
        }
        return $order;
    }

    /**
     * Выполняет преобразование атрибутов в формат сортировки строк.
     * 
     * Например: `[['property' => 'field', 'direction' => 'ASC'], ...] => ['field' => 'ASC', ...]`.
     * 
     * @param string $order Массив с параметрами сортировки.
     * 
     * @return array
     */
    protected function formatOrderAttributes(array $attributes): array
    {
        $format = [];
        foreach ($attributes as $attribute) {
            $property = $attribute['property'] ?? false;
            $direction = isset($attribute['direction']) ? strtoupper($attribute['direction']) : false;
            // если параметры были переданы не правильно
            if (empty($property) || empty($direction)) {
                if (GM_MODE_DEV)
                    throw new Exception\InvalidArgumentException('Unable to sort the list, there are no property or sort type');
                else
                    continue;
            }

            if ($this->dataManager && $this->dataManager->fields) {
                $options = $this->dataManager->getFieldOptions($property);
                // если нет опций для сортирумего поля
                if ($options === null) {
                    if (GM_MODE_DEV) {
                        throw new Exception\InvalidArgumentException('Unable to sort the list, there are no field options for the property "' . $property . '"');
                    }
                } else {
                    // проверка существования 
                    $field = $options['direct'] ?? $options['field'];
                    $format[$field] = $direction;
                }
            } else
                $format[$property] = $direction;
        }
        return $format;
    }

    /**
     * Определяет сортировку строк в сетке.
     * 
     * - если значение указано в параметрах конфигурации, тогда возвратит {@see BaseGridModel::$order};
     * - если не указан параметр запроса или сам параметр отсутствует в запросе, 
     * тогда возвратит {@see BaseGridModel::$defaultOrder};
     * - если значение параметра является не допустимым, 
     * тогда возвратит {@see BaseGridModel::$defaultOrder}.
     * 
     * @see BaseGridModel::$order
     * 
     * @return int
     */
    protected function defineOrder(): array
    {
        // если значение указано в параметрах конфиграции
        if (isset($this->order)) {
            return $this->order;
        }

        if ($this->dataManager && isset($this->dataManager->order))
            $defaultOrder = $this->dataManager->order;
        else
            $defaultOrder = $this->defaultOrder;

        if ($defaultOrder) {
            // если значение JSON-формат
            if (is_string($defaultOrder)) {
                $defaultOrder = $this->formatOrderJson($defaultOrder);
                $defaultOrder = $defaultOrder ?: [];
            } else
            // если значение массив
            if (is_array($defaultOrder)) {
                $defaultOrder = $this->formatOrderArray($defaultOrder);
            }
        }

        // если запрещено получать значение из HTTP-запроса
        if ($this->orderParam === false) {
            return $defaultOrder;
        }

        $order = Gm::$app->request->getPost($this->orderParam, null);
        if ($order === null) {
            return $defaultOrder;
        }
        // параметр был получен из запроса
        $this->hasOrder = true;

        if (empty($order)) {
            return $defaultOrder;
        }

        $order = Json::decode($order);
        if (Json::error()) {
            // TODO: debug
            return $defaultOrder;
        } else
            return $this->formatOrderAttributes($order);
    }

    /**
     * Выполняет преобразование параметров к формату фильтра.
     * 
     * Результат преобразования: 
     * ```php
     * [
     *     'property' => 'foobar', // ключ (свойство, поле) для сравнения
     *     'value'    => 'sample', // значение для сравнения
     *     'operator' => '=', // оператор сравнения
     *     'where'    => function() { // аномнимная функция
     *     }
     * ]
     * ```
     * 
     * @param string $order Парамтры фильтра.
     * 
     * @return array
     */
    protected function formatFilter(array $filter): array
    {
        $format = [];
        foreach ($filter as $params) {
            if (empty($params['property']) || empty($params['operator'])) continue;

            $format[$params['property']] = [
                'property' => $params['property'], 
                'value'    => $params['value'] ?? '', 
                'operator' => $params['operator'],
                'where'    => null
            ];
        }
        return $format;
    }

    /**
     * Возвращает 1-й порядок сортировки из указанных.
     * 
     * Результат может имеет вид `['property', 'ASC']` или значение `null` если 
     * сортировка отсутствует.
     * 
     * @param array|null $default Значение по умолчанию если нет сортировки.
     * 
     * @return array|null
     */
    public function getOneOrder(array $default = null): ?array
    {
        if ($this->order)
            return [
                key($this->order),
                current($this->order)
            ];
        else
            return $default;
    }

    /**
     * Определяет параметры "быстрого" фильтра.
     * 
     * - если значение указано в параметрах конфигурации, тогда возвратит {@see BaseGridModel::$fastFilter};
     * - если не указан параметр запроса или сам параметр отсутствует в запросе `[]`;
     * - если значение параметра является не допустимым `[]`.
     * 
     * @see BaseGridModel::$fastFilter
     * 
     * @return array
     */
    protected function defineFastFilter(): array
    {
        // если значение указано в параметрах конфиграции
        if (isset($this->fastFilter)) {
            return $this->fastFilter;
        }

        // если запрещено получать значение из HTTP-запроса
        if ($this->fastFilterParam === false) {
            return [];
        }

        $fastFilter = Gm::$app->request->getPost($this->fastFilterParam, null);
        if ($fastFilter === null) {
            return [];
        }
        // параметр был получен из запроса
        $this->hasFastFilter = true;

        if (empty($fastFilter)) {
            return [];
        }

        $fastFilter = Json::decode($fastFilter);
        if (Json::error()) {
            // TODO: debug
            return [];
        } else
            return $this->formatFilter($fastFilter);
    }

    /**
     * Определяет параметры "прямого" фильтра, если он ранее был установлен.
     * 
     * - если значение указано в параметрах конфигурации, тогда возвратит {@see BaseGridModel::$directFilter};
     * - если параметры "прямого" фильтра отсутствуют в хранилище модуля то возвратит `[]`.
     * 
     * @see BaseGridModel::$directFilter
     * 
     * @return array
     */
    public function defineDirectFilter(): array
    {
        $store = $this->module->getStorage();
        if ($store->directFilter !== null) {
            $modelName = $this->getModelName();
            // если есть фильтр для конкретной модели данные (т.к. в настройках компонента, 
            // может быть несколько списков с фильтрами)
            if (isset($store->directFilter[$modelName]))
                return $store->directFilter[$modelName];
        }
        return [];
    }

    /**
     * Устанавливает значения "прямого" фильтра.
     * 
     * @return void
     */
    public function setDirectFilter(): void
    {
        /** @var \Gm\Session\Container $storage */
        $storage = $this->module->getStorage();
        /** @var \Gm\Data\DataManager $manager */
        $manager = $this->dataManager;

        // если фильтр не создан
        if ($storage->directFilter === null) {
            $storage->directFilter = [];
        }
        $directFilter = $storage->directFilter;
        $filter = [];

        $this->beforeSetFilter();
        // если менеджер данных имеет опцию "filter",
        // для использования "прямого фильтра" или указаны поля аудита записи в самом фильтре.
        if (isset($manager->filter) || $filter) {
            /** @var \Gm\Http\Request $request */
            $request = Gm::$app->request;

            foreach ($manager->filter as $key => $params) {
                $value = $request->post($key);
                if ($value === null) continue;
                // валидация значений фильтра
                $value = $this->validateFilterValue($key, $value);
                if ($value === false) continue;
                $filter[$key] = [
                    'value'    => $value,
                    'property' => $key,
                    'operator' => $params['operator'],
                    'where'    => $params['where'] ?? null
                ];
            }
        }
        $this->onSetFilter($filter);
        // установка фильтра именно для этой модели данных,
        // т.к. в модуле может быть много моделей
        $directFilter[$this->getModelName()] = $filter;
        $storage->directFilter = $directFilter;
        $this->afterSetFilter($filter);
    }

    /**
     * Событие перед установкой "прямого" фильтра.
     * 
     * @return void
     */
    protected function beforeSetFilter(): void
    {
        $this->trigger(self::EVENT_BEFORE_SET_FILTER);
    }

    /**
     * Событие после установкой "прямого" фильтра.
     * 
     * @return void
     */
    protected function afterSetFilter(array $filter): void
    {
        $this->trigger(self::EVENT_AFTER_SET_FILTER, ['filter' => $filter]);
    }

    /**
     * Событие установки "прямого" фильтра.
     * 
     * @return void
     */
    protected function onSetFilter(array &$filter): void
    {
        $this->trigger(self::EVENT_ON_SET_FILTER, ['filter' => $filter]);
    }

    /**
     * Выполняет проверку значений "прямого" фильтра.
     * 
     * @param string $field Название поля фильтра.
     * @param string $value Значение.
     * 
     * @return mixed Если `false`, значение не проверено.
     */
    protected function validateFilterValue(string $field, mixed $value): mixed
    {
        if (strlen($value) > 0 && $value !== 'null')
            return $value;
        else
            return false;
    }

    /**
     * Проверяет, был ли задействован "быстрый" (через столбец) фильтр.
     * 
     * @return bool
     */
    public function hasFastFilter(): bool
    {
        return !empty($this->fastFilter);
    }

    /**
     * Проверяет, был ли задействован "прямой" (по запросу) фильтр.
     * 
     * @return bool
     */
    public function hasDirectFilter(): bool
    {
        $store = $this->module->getStorage();
        if ($store->directFilter !== null) {
            $modelName = $this->getModelName();
            // если есть фильтр для конкретной модели данные (т.к. в настройках компонента, может быть несколько списков с фильтрами)
            return !empty($store->directFilter[$modelName]);
        }
        return false;
    }

    /**
     * Проверяет, был ли задействован "быстрый" или "прямой" фильтр.
     * 
     * @see BaseGridModel::hasFastFilter()
     * @see BaseGridModel::hasDirectFilter()
     * 
     * @return bool
     */
    public function hasFilter(): bool
    {
        return $this->hasFastFilter() || $this->hasDirectFilter();
    }

    /**
     * Событие, возникающие перед получением строк.
     * 
     * Получение записей {@see BaseGridModel::selectRows()}.
     * 
     * @return void
     */
    public function beforeFetchRows(): void
    {
    }

    /**
     * Возвращает строки, полученные в результате запроса и приведение их к нужному 
     * формату.
     * 
     * @param mixed $receiver Приёмник или строитель строк.
     * 
     * @return array
     */
    public function fetchRows(mixed $receiver): array
    {
        return [];
    }

    /**
     *  Событие, возникающие после получения строк.
     *
     * @param array $rows Строки, полученные в результате запроса и приведения их к нужному 
     * формату.
     *
     * @return array
     */
    public function afterFetchRows(array $rows): array
    {
        return $rows;
    }

    /**
     * Событие, возникающие после выборки строк.
     * 
     * @param array $rows Полученные строки из выборки.
     * @param mixed $receiver Приёмник или строитель строк.
     * 
     * @return array Результат имеет вид:
     * ```php
     *     [
     *         "total" => 10, // общее количество строк в сетке
     *         "rows"  => [...] // строки выборки
     *     ]
     *```
     */
    public function afterSelectRows(array &$rows, mixed $receiver): array
    {
        $this->trigger(self::EVENT_AFTER_SELECT, ['rows' => $rows, 'receiver' => $receiver]);

        $total = $this->getTotalRows($receiver);
        return [
            'total' => $total === 0 ? sizeof($rows) : $total,
            'rows'  => $rows
        ];
    }

    /**
     * Выбирает строки и подготавливает их к возврату.
     *
     * @return array
     */
    public function selectRows(): array
    {
        $builder  = $this->getRowsBuilder();
        $receiver = $this->buildQuery($builder);

        $this->beforeFetchRows();
        $rows = $this->fetchRows($receiver);
        $rows = $this->afterFetchRows($rows);
        return $this->afterSelectRows($rows, $receiver);
    }

    /**
     * Выполняет строителем запрос на получение строк.
     * 
     * После формирования и выполнения запроса строителем, будет сформирован 
     * приёмник для получения строк.
     * 
     * @param mixed $builder Строитель строк.
     *
     * @return mixed Возвращает приёмник строк. Если приёмник отсутствует, то возвратит 
     *     строителя. 
     */
    public function buildQuery($builder)
    {
        return $builder;
    }

    /**
     * @see BaseGridModel::getRowsBuilder()
     * 
     * @var mixed
     */
    protected $rowsBuilder;

    /**
     * Возвращает построителя строк.
     * 
     * @return mixed
     */
    public function getRowsBuilder()
    {
        return $this->rowsBuilder;
    }

    /**
     * Возвращает общее количество строк в сетке для пагинации.
     * 
     * Количество не включает фильтр и условия проверки.
     * Количество определяется приёмником $receiver или строителем $builder строк.
     * 
     * @param mixed $receiver Приёмник строк (по умолчанию `null`).
     * 
     * @return int
     */
    public function getTotalRows(mixed $receiver = null): int
    {
        return 0;
    }

    /**
     * Возвращает общее количество строк в сетке.
     * 
     * Количество не включает фильтр и условия проверки.
     * Применяется для определения количества обработанных строк, например, удаление
     * строк {@see BaseGridModel::deleteMessage()}.
     * 
     * @return int
     */
    public function getCountRows(): int
    {
        return 0;
    }

    /**
     * Возвращает количество выделенных (выбранных) строк из запроса.
     *
     * @return int
     */
    public function getSelectedCount(): int
    {
        return sizeof($this->rowsId);
    }

    /**
     * Возвращает строки полученные по запросу.
     *
     * @return array
     */
    public function getRows(): array
    {
        return $this->selectRows();
    }

    /**
     * Событие, возникающие перед удалением строк.
     *
     * @param bool $someRows Если значение `true`, удаление нескольких строк (по умолчанмю `true`).
     *
     * @return bool Возвращает значение `true`, если строки можно удалить.
     */
    public function beforeDelete(bool $someRows = true): bool
    {
        /** @var bool $canDelete возможность удаления строк определяется событием */
        $canDelete = true;
        $this->trigger(
            self::EVENT_BEFORE_DELETE,
            [
                'someRows'  => $someRows,
                'canDelete' => &$canDelete
            ]
        );
        return $canDelete;
    }

    /**
     * Событие, возникающие после удаления строк.
     *
     * @param bool $someRows Если значение `true`, удаление нескольких строк (по умолчанмю `true`).
     * @param mixed $result Если значение `false`, ошибка удаления. Иначе, количество удаленных 
     *     строк (по умолчанмю `null`).
     *
     * @return void
     */
    public function afterDelete(bool $someRows = true, mixed $result = null): void
    {
        $this->trigger(
            self::EVENT_AFTER_DELETE,
            [
                'someRows' => $someRows,
                'result'   => $result,
                'message'  => $this->deleteMessage($someRows, $result)
            ]
        );
    }

    /**
     * Выполняет удаление всех строк из сетки.
     * 
     * @return false|int Возвращает значение `false` если была ошибки, иначе количество 
     *     удаленных строк.
     */
    public function clearRows(): false|int
    {
        return 0;
    }

    /**
     * Выполняет удаление всех строк.
     * 
     * @return false|int Возвращает значение `false` если была ошибки, иначе количество 
     *     удаленных строк.
     */
    public function clear(): false|int
    {
        $result = false;
        if ($this->beforeDelete(true)) {
            $result = $this->clearRows();
            $this->afterDelete(true, $result);
        }
        return $result;
    }

    /**
     * Выполняет удаление строк с указанными идентификаторами.
     * 
     * @return false|int Возвращает значение `false` если была ошибки, иначе количество 
     *     удаленных строк.
     */
    public function deleteRows(array $rowsId): false|int
    {
        return 0;
    }

    /**
     * Выполняет удаление строк.
     * 
     * @return false|int Возвращает значение `false` если была ошибки, иначе количество 
     *     удаленных строк.
     */
    public function delete(): false|int
    {
        $someRows = sizeof($this->rowsId) > 1;

        $result = false;
        if ($this->beforeDelete($someRows)) {
            $result = $this->deleteRows($this->rowsId);
            $this->afterDelete($someRows, $result);
        }
        return $result;
    }

    /**
     * Формирует информацию (сообщение) о удалении строк.
     *
     * @see BaseGridModel::afterDelete()
     * 
     * @param bool $someRows Если значение `true`, удаление нескольких строк.
     * @param int $result Количество удаленных строк.
     * 
     * @return array
     */
    public function deleteMessage(bool $someRows, int $result): array
    {
        $type     = 'accept';
        $message  = '';
        // удаление выбранных строк
        if ($someRows) {
                $selected = $this->getSelectedCount();
                $missed   = $selected - $result;
                // записи удалены
                if ($result > 0) {
                    // записи удалены частично
                    if ($missed > 0) {
                        $message = $this->deleteMessageText(
                            'partiallySome',
                            [
                                'deleted' => $result, 'nDeleted' => $result,
                                'selected' => $selected, 'nSelected' => $selected
                            ]
                        );
                        $type = 'warning';
                    // записи удалены полностью
                    } else
                        $message = $this->deleteMessageText(
                            'successfullySome',
                            ['n' => $result, 'N' => $result]
                        );
                // записи не удалены
                } else {
                    $message = $this->deleteMessageText(
                        'unableSome',
                        ['n' => $selected, 'N' => $selected]
                    );
                    $type = 'error';
                }
        // удаление всех строк
        } else {
            $missed   = $this->getCountRows();
            $selected = $result;
            // записи удалены
            if ($result > 0) {
                // строки удалены частично
                if ($missed > 0) {
                     $message = $this->deleteMessageText(
                        'partiallyAll',
                        [
                            'deleted' => $result, 'nDeleted' => $result,
                            'skipped' => $missed, 'nSkipped' => $missed
                        ]
                    );
                    $type = 'warning';
                // строки удалены полностью
                } else {
                    $message = $this->deleteMessageText(
                        'successfullyAll',
                        ['n' => $result, 'N' => $result]
                    );
                }
            // строки не удалены
            } else {
                $message = $this->deleteMessageText(
                    'unableAll',
                   ['n' => $selected]
                );
                $type = 'error';
            }
        }
        return [
            'selected' => $selected, // количество выделенных строк в сетке
            'deleted'  => $result, // количество удаленных строк
            'missed'   => $missed, // количество пропущенных строк
            'success'  => $missed == 0, // успех удаления строк
            'message'  => $message, // сообщение
            'title'    => Gm::t(BACKEND, 'Deletion'), // загаловок сообщения
            'type'     => $type // тип сообщения
        ];
    }

    /**
     * Возвращет текст сообщения о удалении строк.
     *
     * @param string $type Вид действия, может иметь значение:
     *     - 'partiallySome', выбранные строки удалены частично;
     *     - 'successfullySome', выбранные строки удалены полностью;
     *     - 'unableSome', выбранные строки не удалены;
     *     - 'partiallyAll', все строки удалены частично;
     *     - 'successfullyAll', все строки удалены полностью;
     *     - 'unableAll', все выбранные строки не удалены.
     * @param array $params Параметры перевода (локализации сообщения).
     * 
     * @return string Текст сообщения о удалении строк.
     */
    protected function deleteMessageText(string $type, array $params): string
    {
        switch ($type) {
            // выбранные строки удалены частично
            case 'partiallySome':
                return Gm::t(
                    BACKEND,
                    'The records were partially deleted, from the selected {nSelected} {selected, plural, =1{record} other{records}}, {nDeleted} were deleted, the rest were omitted',
                    $params
                );
            // выбранные строки удалены полностью
            case 'successfullySome':
                return Gm::t(
                    BACKEND,
                    'Successfully deleted {N} {n, plural, =1{record} other{records}}',
                    $params
                );
            // выбранные строки не удалены
            case 'unableSome':
                return Gm::t(
                    BACKEND,
                    'Unable to delete {N} {n, plural, =1{record} other{records}}, no records are available',
                    $params
                );
            // все строки удалены частично
            case 'partiallyAll':
                return Gm::t(
                    BACKEND,
                    'Records have been partially deleted, {nDeleted} deleted, {nSkipped} {skipped, plural, =1{record} other{records}} skipped',
                    $params
                );
            // все строки удалены полностью
            case 'successfullyAll':
                return Gm::t(
                    BACKEND,
                    'Successfully deleted {N} {n, plural, =1{record} other{records}}',
                    $params
                );
            // все выбранные строки не удалены
            case 'unableAll':
                return Gm::t(
                    BACKEND,
                    'Unable to delete {n, plural, =1{record} other{records}}, no {n, plural, =1{record} other{records}} are available',
                    $params
                );
            default:
                return '';
        }
    }
}
