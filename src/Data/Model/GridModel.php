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
use Gm\Db\Sql;
use Gm\Helper\Json;
use Gm\Data\DataManager;
use Gm\Data\Model\DataModel;
use Gm\Db\Adapter\Driver\AbstractCommand;

/**
 * Модель сетки данных (при взаимодействии с 
 * представлением, использующий компонент Gm.view.grid.Grid GmJS).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Data\Model
 * @since 1.0
 */
class GridModel extends DataModel
{
    /**
     * @var string Событие, возникшее после успешного получения записи по запросу.
     * 
     * @see GridModel::selectOne()
     * @see GridModel::selectAll()
     */
    public const EVENT_AFTER_SELECT = 'afterSelect';

    /**
     * @var string Событие, возникшее перед удалением записи.
     * 
     * @see GridModel::deleteRecord()
     */
    public const EVENT_BEFORE_DELETE = 'beforeDelete';

    /**
     * @var string Событие, возникшее после удаления записи.
     */
    public const EVENT_AFTER_DELETE = 'afterDelete';

    /**
     * @var string Событие, перед после.
     */
    public const EVENT_BEFORE_SET_FILTER = 'beforeSetFilter';

    /**
     * @var string Событие, процесс установки фильтра.
     */
    public const EVENT_ON_SET_FILTER = 'onSetFilter';

    /**
     * @var string Событие, возникшее после.
     */
    public const EVENT_AFTER_SET_FILTER = 'afterSetFilter';

    public const COL_UPDATED_DATE = 'logUpdatedDate';

    public const COL_UPDATED_UTC = 'logUpdatedUTC';
    
    public const COL_UPDATED_USER = 'logUpdatedUser';

    public const COL_CREATED_DATE = 'logCreatedDate';

    public const COL_CREATED_UTC = 'logCreatedUTC';

    public const COL_CREATED_USER = 'logCreatedUser';

    /**
     * Идентификаторы записей в последнем запросе.
     *
     * @var array<int, int>
     */
    public array $rowsId = [];

    /**
     * Собирать идентификаторы записей после каждого запроса.
     * 
     * Идентификаторы собираются {GridModel::$rowsId} методом {GridModel::fetchRows()} и 
     * используются для вспомогательных запросов.
     * 
     * @var bool
     */
    public bool $collectRowsId = false;

    /**
     * Идентификатор записи.
     *
     * @var mixed
     */
    protected mixed $identifier = null;

    /**
     * Количество записей на странице.
     * 
     * @var int
     */
    protected int $limit = 10;

    /**
     * Текущая страница списка.
     * 
     * @var int
     */
    protected int $page = 0;

    /**
     * Порядковый номер начала списка записей.
     * 
     * @var int
     */
    protected int $offset = 0;

    /**
     * Порядок сортировки списка.
     * 
     * @var array
     */
    protected array $order = [];

    /**
     * "Быстрый" фильтр через столбец
     * 
     * @var array
     */
    protected array $fastFilter = [];

    /**
     * "Прямой" фильтр по запросу
     * 
     * @var array
     */
    protected array $directFilter = [];

    /**
     * Форматтер.
     * 
     * @var \Gm\I18n\Formatter
     */
    protected $formatter;

    /**
     * {@inheritdoc}
     */
    protected ?string $assignType = DataManager::AT_COLUMN;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        /** @var \Gm\Http\Request $request */
        $request = Gm::$app->request;
        /** @var \Gm\Data\DataManager $manager */
        $manager = $this->getDataManager();

        // количество записей на странице
        if (isset($manager->limit)) {
            $this->limit = $manager->limit;
        }
        $this->limit = $request->getPost('limit', $this->limit, 'int');
        // определение вида сортировки полей
        if (isset($manager->order)) {
            $this->order = $manager->order;
        }
        $this->order = $this->defineOrder($request->getPost('sort', ''), $this->order);
        // индекс начала списка записей
        if (isset($manager->offset)) {
            $this->offset = $manager->offset;
        }
        $this->offset = $request->getPost('start', $this->offset, 'int');
        // "быстрый" фильтр через столбец
        $this->fastFilter = $this->defineFastFilter($request->getPost('filter'));
        // "прямой" фильтр по запросу
        $this->directFilter = $this->defineDirectFilter();
        // служба форматтер
        $this->formatter = Gm::$app->formatter;

        /**
         * Здесь можно выполнить инициализацию событий модели:
         * Пример событий: выборки, сохранения и удаление записи.
         * 
         * $this
         *  ->on(self::EVENT_AFTER_SELECT, function ($rows, $command) {
         *       ...
         *   })
         *  ->on(self::EVENT_AFTER_DELETE, function ($someRecords) {
         *      if ($someRecords)
         *         $identifier = implode(',', $this->getIdentifier());
         *      ...
         *   })
         */
    }

    /**
     * Определяет параметры сортировки списка (переданные в формате JSON).
     *
     * @param string|array $orderJson Параметры сортировки в формате JSON.
     *
     * @return array Параметры сортировки списка.
     */
    protected function defineOrder(string|array $orderJson, array $default = []): array
    {
        if (empty($orderJson)) {
            return $default;
        }
        $order = [];
        /* если $orderJson маска полей с видом сортировки 
         * имеет вид: array("alias1" => "asc", "alias2" => "desc"...) 
         */
        if (is_array($orderJson)) {
            foreach($orderJson as $alias => $direction) {
                $field = $this->dataManager->getFullField($alias);
                if ($field !== null)
                    $order[$field] = $direction;
            }
            return $order;
        }
        
        try {
            $orderDecode = Json::decode($orderJson);
            $error = Json::error();
            if ($error)
                throw new Exception\JsonFormatException('Could not get sort type list');
        } catch(\Exception $e) {
            Gm::error($e->getMessage());
        }

        if ($orderDecode) {
            foreach ($orderDecode as $item) {
                $property = isset($item['property']) ? $item['property'] : false;
                $direction = isset($item['direction']) ? strtoupper($item['direction']) : false;
                // если параметры были переданы не правильно
                if (empty($property) || empty($direction)) {
                    throw new Exception\InvalidArgumentException('Unable to sort the list, there are no property or sort type');
                }
                $options = $this->dataManager->getFieldOptions($property);
                // если нет опций для сортирумего поля
                if ($options === null) {
                    throw new Exception\InvalidArgumentException('Unable to sort the list, there are no field options for the property "' . $property . '"');
                }
                // проверка существования 
                if (isset($options['direct']))
                    $field = $options['direct'];
                else
                    $field = $options['field'];
                $order[$field] = $direction;
            }
            return $order;
        }
        return $order;
    }

    /**
     * Возвращает параметры "быстрого фильтра".
     * 
     * @param null|string $filterJson Фильтр в JSON формате.
     *
     * @return array
     */
    protected function defineFastFilter(?string $filterJson): array
    {
        if (empty($filterJson)) {
            return [];
        }
        $filter = [];
        try {
            $filter = Json::decode($filterJson);
            $error  = Json::error();
            if ($error)
                throw new Exception\JsonFormatException();
        } catch(\Exception $e) {
            Gm::error($e->getMessage());
        }
        return $filter;
    }

    /**
     * Возвращает параметры "прямого фильтра".
     * 
     * @return array
     */
    public function defineDirectFilter(): array
    {
        $store = $this->module->getStorage();
        if ($store->directFilter !== null) {
            $modelName = $this->getModelName();
            // если есть фильтр для конкретной модели данные (т.к. в настройках компонента, может быть несколько списков с фильтрами)
            if (isset($store->directFilter[$modelName]))
                return $store->directFilter[$modelName];
        }
        return [];
    }

    /**
     * Установка "прямого фильтра".
     * 
     * @return void
     */
    public function setDirectFilter(): void
    {
        /** @var \Gm\Http\Request $request */
        $request = Gm::$app->request;
        /** @var \Gm\Session\Container $store */
        $store = $this->module->getStorage();
        /** @var \Gm\Data\DataManager $manager */
        $manager = $this->getDataManager();

        // если фильтр не создан
        if ($store->directFilter === null) {
            $store->directFilter = [];
        }
        $directFilter = $store->directFilter;
        $filter = [];
        // используется ли аудит записей
        if ($manager) {
            $useAudit = $manager->useAudit && $manager->canViewAudit();
        } else
            $useAudit = false;
        // возможно фильтр имеет значения для фильтрации записей по аудиту (если имеет роль пользователя)
        if ($useAudit) {
            // если в фильтре указаны поля аудита записи
            $logUser = $request->post('logUser');
            if ($logUser !== null && $this->validateFilterValue('logUser', $logUser)) {
                $filter[] = ['value' => $logUser, 'property' => self::COL_CREATED_USER /* не имеет смысла */, 'operator' => 'lu'];
            }
            $logDate = $request->post('logDate');
            if ($logDate !== null && $this->validateFilterValue('logDate', $logDate)) {
                $filter[] = ['value' => $logDate, 'property' => self::COL_CREATED_DATE /* не имеет смысла */, 'operator' => 'ld'];
            }
        }
        $this->beforeSetFilter();
        // если менеджер данных имеет опцию "filter",
        // для использования "прямого фильтра" или указаны поля аудита записи в самом фильтре.
        if (isset($manager->filter) || $filter) {
            foreach ($manager->filter as $key => $params) {
                $value = $request->post($key);
                if ($value === null) continue;
                // валидация значений фильтра
                $value = $this->validateFilterValue($key, $value);
                if ($value === false) continue;
                $filter[] = [
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
        $store->directFilter = $directFilter;
        $this->afterSetFilter($filter);
    }

    /**
     * Подготовка к установке "прямого фильтра"
     * 
     * @return void
     */
    protected function beforeSetFilter(): void
    {
        $this->trigger(self::EVENT_BEFORE_SET_FILTER);
    }

    /**
     * Подготовка к установке "прямого фильтра"
     * 
     * @return void
     */
    protected function afterSetFilter(array $filter): void
    {
        $this->trigger(self::EVENT_AFTER_SET_FILTER, ['filter' => $filter]);
    }

    /**
     * Процесс установки "прямого фильтра".
     * 
     * @return void
     */
    protected function onSetFilter(array &$filter): void
    {
        $this->trigger(self::EVENT_ON_SET_FILTER, ['filter' => $filter]);
    }

    /**
     * Валидация значения при установке {@see setDirectFilter()} "прямого фильтра".
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
     * Проверяет, был ли задействован "быстрый"  (через столбец) фильтр.
     * 
     * @return bool
     */
    public function hasFastFilter(): bool
    {
        return !empty($this->fastFilter);
    }

    /**
     * Проверяет, был ли задействован "прямой"  (по запросу) фильтр.
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
     * Проверяет, был ли задействован один из фильтров: "быстрый" (через столбец), 
     * "прямой" (по запросу).
     * 
     * @see GridModel::hasFastFilter()
     * @see GridModel::hasDirectFilter()
     * 
     * @return bool
     */
    public function hasFilter(): bool
    {
        return $this->hasFastFilter() || $this->hasDirectFilter();
    }

    /**
     * Добавление сортировки в конструктор запроса {@see buildQuery()}.
     *
     * @param Sql\Select|Sql\Query $operator Оператор.
     *
     * @return void
     */
    public function buildOrder(Sql\AbstractSql $operator): void
    {
        $operator->order($this->order);
    }

    /**
     * Добавление лимита записей в конструктор запроса {@see buildQuery()}.
     *
     * @param Sql\Select|Sql\Query $operator Оператор.
     *
     * @return void
     */
    public function buildLimit(Sql\AbstractSql $operator): void
    {
        // значение: 0, 1...|null (если null не использовать)
        $operator->limit($this->limit);
    }

    /**
     * Добавление смещение записей в конструктор запроса.
     *
     * @param Sql\Select|Sql\Query $operator Оператор.
     *
     * @return void
     */
    public function buildOffset(Sql\AbstractSql $operator): void
    {
        // значение: 0, 1...|null (если null не использовать)
        $operator->offset($this->offset);
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
        if (empty($filters)) {
            return;
        }

        foreach ($filters as $options) {
            // оператор фильтра
            $optOperator = $options['operator'] ?? false;
            // значение фильтра
            $optValue    = $options['value'] ?? false;
            if ($optValue === 'none')
                $optValue = false;
            // псевдоним или название поля из таблицы базы данных
            $optProperty = $options['property'] ?? false;
            // исключение
            if ($optOperator === '=')
                if (empty($optValue)) $optValue = '0';
            // если не указан один из параметров фильтра
            if (!$optOperator || !$optProperty) continue;
            // определение опций фильтра поля, если нет такого поля, его пропускаем
            $fieldOptions = $this->dataManager->getFieldOptions($optProperty);
            if ($fieldOptions !== null) {
                // тип фильтра
                $filterType = $fieldOptions['filterType'] ?? '';
                // имя поля для фильтрации: "direct" (поле включает имя базы данных) или "field" 
                $optProperty = isset($fieldOptions['direct']) ? $fieldOptions['direct'] : $fieldOptions['field'];
            } else
                continue;

            // filter operator: "boolean", "date", "list", "number"
            switch ($optOperator) {
                case 'where':
                    $operator->where(sprintf($options['where'], $optValue));
                    break;

                case 'like':
                    $operator->where->like($optProperty, $optValue . '%');
                    break;

                // равенство
                case 'eqv':
                case '==':
                case '=':
                    if (is_bool($optValue))
                        $optValue = $optValue ? 1 : 0;
                    else
                    if (is_string($optValue)) {
                        if ($optValue === 'true')
                            $optValue = 1;
                        else
                        if ($optValue === 'false')
                            $optValue = 0;
                        /*else
                            $optValue = (int) $optValue;**/
                    }
                    $operator->where([$optProperty => $optValue]);
                    break;

                // множество
                case 'in':
                    if (!is_array($optValue)) return;
                    $operator->where->in($optProperty, $optValue);
                    break;

                // до даты (Y-m-d) / меньше чем число
                case 'lt':
                    // если дата, а не числовое или строкове значение
                    if (!is_numeric($optValue)) {
                        $optValue = Gm::$app->formatter->toDate($optValue, 'php:Y-m-d', true, Gm::$app->dataTimeZone);
                    }
                    $operator->where->lessThan($optProperty, $optValue);
                    break;

                // после даты (Y-m-d) / больше чем число
                case 'gt':
                    // если дата, а не числовое или строкове значение
                    if (!is_numeric($optValue)) {
                        $optValue = Gm::$app->formatter->toDate($optValue, 'php:Y-m-d', true, Gm::$app->dataTimeZone);
                    }
                    $operator->where->greaterThan($optProperty, $optValue);
                    break;

                // на дату (Y-m-d) / ровно числовому или строковому значению
                case 'eq':
                    // если числовое или строкове значение
                    if (is_numeric($optValue)) {
                        $operator->where->equalTo($optProperty, $optValue);
                    // если дата и/или время
                    } else {
                        if ($filterType == 'datetime') {
                            $operator->where->Between(
                                $optProperty,
                                Gm::$app->formatter->toDate($optValue, 'php:Y-m-d 00:00:00', true, Gm::$app->dataTimeZone),
                                Gm::$app->formatter->toDate($optValue, 'php:Y-m-d 23:59:59', true, Gm::$app->dataTimeZone)
                            );
                        } else {
                            $optValue = Gm::$app->formatter->toDate($optValue, 'php:Y-m-d', true, Gm::$app->dataTimeZone);
                            $operator->where->equalTo($optProperty, $optValue);
                        }
                    }
                    break;

                // диапазон дат (table.property BETWEEN {fromDate} AND {toDate})
                case 'dr':
                    $fromDate = '';
                    $toDate   = Gm::$app->formatter->toDateTime('now', 'php:Y-m-d H:i:s', true, Gm::$app->dataTimeZone);
                    switch ($optValue) {
                        // за день
                        case 'lt-1d':
                            $fromDate = Gm::$app->formatter->toDate('now', 'php:Y-m-d 00:00:00', true, Gm::$app->dataTimeZone);
                            break;

                        // за вчера
                        case 'lt-2d':
                            $fromDate = Gm::$app->formatter->toDateInterval('now', '-P1D', 'Y-m-d 00:00:00', Gm::$app->dataTimeZone);
                            $toDate   = Gm::$app->formatter->toDateInterval('now', '-P1D', 'Y-m-d 23:59:59', Gm::$app->dataTimeZone);
                            break;

                        // за неделю
                        case 'lt-1w':
                            $fromDate = Gm::$app->formatter->toDateInterval('now', '-P1W', 'Y-m-d 00:00:00', Gm::$app->dataTimeZone);
                            break;

                        // за месяц
                        case 'lt-1m':
                            $fromDate = Gm::$app->formatter->toDateInterval('now', '-P1M', 'Y-m-d 00:00:00', Gm::$app->dataTimeZone);
                            break;

                        // за год
                        case 'lt-1y':
                            $fromDate = Gm::$app->formatter->toDateInterval('now', '-P1Y', 'Y-m-d 00:00:00', Gm::$app->dataTimeZone);
                            break;
                    }
                    if ($fromDate && $toDate) {
                        $operator->where
                            ->nest()
                                ->Between($optProperty, $fromDate, $toDate)
                            ->unnest();
                    }
                    break;

                // запись пользователя, если есть столбец аудита пользователя (table._updated_user={value} OR table._created_user={value})
                case 'lu':
                    $operator->where
                        ->nest()
                            ->equalTo($this->dataManager->getFieldOptions(self::COL_UPDATED_USER)['direct'], $optValue)
                            ->OR
                            ->equalTo($this->dataManager->getFieldOptions(self::COL_CREATED_USER)['direct'], $optValue)
                        ->unnest();
                    break;

                // запись пользователя, если есть столбец аудита даты (table._updated_user={value} OR table._created_user={value})
                case 'ld':
                    $fromDate = '';
                    $toDate   = Gm::$app->formatter->toDateTime('now', 'php:Y-m-d H:i:s');
                    switch ($optValue) {
                        // за день
                        case 'lt-1d':
                            $fromDate = Gm::$app->formatter->toDate('now', 'php:Y-m-d 00:00:00', true, Gm::$app->dataTimeZone);
                            break;

                        // за вчера
                        case 'lt-2d':
                            $fromDate = Gm::$app->formatter->toDateInterval('now', '-P1D', 'Y-m-d 00:00:00', Gm::$app->dataTimeZone);
                            $toDate   = Gm::$app->formatter->toDateInterval('now', '-P1D', 'Y-m-d 23:59:59', Gm::$app->dataTimeZone);
                            break;

                        // за неделю
                        case 'lt-1w':
                            $fromDate = Gm::$app->formatter->toDateInterval('now', '-P1W', 'Y-m-d 00:00:00', Gm::$app->dataTimeZone);
                            break;

                        // за месяц
                        case 'lt-1m':
                            $fromDate = Gm::$app->formatter->toDateInterval('now', '-P1M', 'Y-m-d 00:00:00', Gm::$app->dataTimeZone);
                            break;

                        // за год
                        case 'lt-1y':
                            $fromDate = Gm::$app->formatter->toDateInterval('now', '-P1Y', 'Y-m-d 00:00:00', Gm::$app->dataTimeZone);
                            break;
                    }
                    if ($fromDate && $toDate) {
                        $operator->where
                            ->nest()
                                ->Between($this->dataManager->getFieldOptions(self::COL_CREATED_DATE)['direct'], $fromDate, $toDate)
                                ->OR
                                ->Between($this->dataManager->getFieldOptions(self::COL_UPDATED_DATE)['direct'], $fromDate, $toDate)
                            ->unnest();
                    }
                    break;
            } // end switch
        } // end foreach
    }

    /**
     * Добавление "быстрой фильтрации" и "прямого фильтра" в конструктор запроса {@see buildQuery()}.
     *
     * @param Sql\Select|Sql\Query $operator Оператор инструкции SQL.
     *
     * @return void
     */
    public function buildFilter(Sql\AbstractSql $operator): void
    {
        if ($this->fastFilter) {
            $this->buildFastFilter($operator, $this->fastFilter);
        }
        if ($this->directFilter) {
            $this->buildFastFilter($operator, $this->directFilter);
        }
        /** @var \Gm\Data\DataManager $manager */
        $manager = $this->getDataManager();
        // добавление условия фильтрации записей для доступа на уровне записей (RLS - Record-Level Sharing),
        // если указано в разрешении роли пользователя. Записи буду доступны пользователю только те, которые он добавил.
        if ($manager->canUseRecordRls()) {
            $manager->addFilterRecordRls($operator);
        }
    }

    /**
     * Строит и выполняет инструкцию SQL.
     *
     * @param Sql\AbstractSql $operator Оператор инструкции SQL.
     *
     * @return AbstractCommand
     * 
     * @throws \Gm\Db\Adapter\Driver\Exception\CommandException
     */
    public function buildQuery(Sql\AbstractSql $operator)
    {
        /** @var \Gm\Db\Adapter\Adapter $db */
        $db = $this->getDb();

        $this->buildOrder($operator);
        $this->buildLimit($operator);
        $this->buildOffset($operator);
        $this->buildFilter($operator);
        /** @var AbstractCommand $command */
        $command = $db->createCommand(
            $operator->getSqlString($db->getPlatform())
        );
        $this->beforeSelect($command);
        $command->query();
        return $command;
    }

    /**
     * Этот метод вызывается перед выполнением запроса в {@see buildQuery()}.
     * 
     * Возможность изменить конструкцию $command перед выполнением запроса.
     *
     * @param AbstractCommand $command
     * 
     * @return void
     */
    public function beforeSelect(mixed $command = null): void
    {
    }

    /**
     * Этот метод вызывается после выполнения запроса в {@see selectBySql()} или в {@see selectAll()}.
     * 
     * @param array $rows Массив записей, как результат запроса.
     * @param AbstractCommand $command
     * 
     * @return array Имеет вид:
     *     [
     *         "total" => 10, // количество записей в запросе
     *         "rows"  => [...] // записи запроса
     *     ]
     */
    public function afterSelect(array $rows, mixed $command = null): array
    {
        $d = $command->getFoundRows();
        $this->trigger(self::EVENT_AFTER_SELECT, ['rows' => $rows, 'command' => $command]);
        return [
            'total' => $d,
            'rows'  => $rows
        ];
    }

    /**
     * Выполнение SQL запроса к базе данных.
     * 
     * @see GridModel::afterSelect()
     * 
     * @param string $sql SQL запрос к базе данных.
     *
     * @return array
     * 
     * @throws \Gm\Db\Adapter\Driver\Exception\CommandException
     */
    public function selectBySql(string $sql): array
    {
        $command = $this->commandBySql($sql);
        $this->beforeFetchRows();
        $rows = $this->fetchRows($command);
        $rows = $this->afterFetchRows($rows);
        return $this->afterSelect($rows, $command);
    }

    /**
     * Выполнение SQL запроса к базе данных с использованием конструктора запроса {@see buildQuery()}.
     *
     * @param string $sql SQL запрос к базе данных.
     *
     * @return AbstractCommand
     * 
     * @throws \Gm\Db\Adapter\Driver\Exception\CommandException
     */
    public function commandBySql(string $sql)
    {
        /** @var \Gm\Db\Sql\Query $query */
        $query = $this->builder()->sql($sql);
        /** @var AbstractCommand $command */
        return $this->buildQuery($query);
    }

    /**
     * Возвращает записи из таблицы $tableName с использованием конструктора запроса {@see buildQuery()}.
     *
     * @param null|string $tableName Название таблицы.
     *    Если null, используется менеджер данных.
     * 
     * @return array Результат запроса {@see afterSelect()}.
     * 
     * @throws \Gm\Db\Adapter\Driver\Exception\CommandException
     * @throws Sql\Exception\InvalidArgumentException
     */
    public function selectAll(string $tableName = null): array
    {
        /** @var \Gm\Db\Sql\Select $select */
        $select = $this->builder()->select($this->dataManager->tableName);
        $select->quantifier(new \Gm\Db\Sql\Expression('SQL_CALC_FOUND_ROWS'));
        $select->columns(['*']);

        /** @var AbstractCommand $command */
        $command = $this->buildQuery($select);
        $this->beforeFetchRows();
        $rows = $this->fetchRows($command);
        $rows = $this->afterFetchRows($rows);
        return $this->afterSelect($rows, $command);
    }

    /**
     * Выполнение команды адаптера {@see \Gm\Db\Adapter\Adapter} базы данных.
     *
     * @param object $builder Оператор конструктора запросов.
     *
     * @return mixed Выполнение SQL запроса оператора.
     */
    public function command($builder)
    {
        /** @var \Gm\Db\Adapter\Adapter $db */
        $db = $this->getDb();

        return $db->createCommand($builder->getSqlString($db->getPlatform()));
    }

    /**
     * Возвращает указатель на созданный адаптером базы данных конструктор запросов.
     *
     * @return \Gm\Db\Sql\QueryBuilder
     */
    public function builder()
    {
        return $this->getDb()->getQueryBuilder();
    }

    /**
     * Обновляет столбцы аудита записи.
     * 
     * Выставляет столбцам аудита часовой пояс пользователя.
     * 
     * @see \Gm\I18n\Formatter::toDateTime()
     * 
     * @param void
     */
    protected function auditRow(array &$row): void
    {
        if (!empty($row[self::COL_UPDATED_DATE])) {
            $row[self::COL_UPDATED_UTC]  = Gm::$app->formatter->toTimestamp($row[self::COL_UPDATED_DATE]);
            $row[self::COL_UPDATED_DATE] = Gm::$app->formatter->toDateTime(
                $row[self::COL_UPDATED_DATE],
                'php:Y-m-d H:i:s',
                true,
                Gm::$app->user->getTimeZone()
            );
        }
        if (!empty($row[self::COL_CREATED_DATE])) {
            $row[self::COL_CREATED_UTC]  = Gm::$app->formatter->toTimestamp($row[self::COL_CREATED_DATE]);
            $row[self::COL_CREATED_DATE] = Gm::$app->formatter->toDateTime(
                $row[self::COL_CREATED_DATE],
                'php:Y-m-d H:i:s',
                true,
                Gm::$app->user->getTimeZone()
            );
        }
    }

    /**
     * Возвращает записи полученные в результате запроса к базе данных из {@see selectBySql()}, {@see selectAll()}.
     *
     * @param null|array|AbstractCommand $receiver Получатель записей.
     *
     * @return array Если $receiver null, возвращает пустой маccив, иначе массив записей запроса.
     */
    public function fetchRows(mixed $receiver = null): array
    {
        $mask = $this->maskedRow();
        // если метод \Gm\Panel\Data\Model\DataModel::maskedRow() будет перегружен, то 
        // будет отсутствовать маска для полей аудита записи, таким образом устраняем ошибку,
        // Хоть изначально, такая маска для полей ранее добавлена через \Gm\Data\DataManager::addLockFields(), 
        // {@see \Gm\Data\DataManager::addAuditFields()}
        if ($this->dataManager->lockRows) {
            $this->dataManager->addLockFieldsToMask($mask);
        }
        if ($this->dataManager->useAudit) {
            $this->dataManager->addAuditFieldsToMask($mask);
        }
        $primaryKey   = $this->dataManager->primaryKey;
        $fieldOptions = $this->dataManager->fieldOptions;
        $useAudit     = $this->dataManager->useAudit && $this->dataManager->canViewAudit();
        $mask[$primaryKey] = $primaryKey;
        $rows = [];
        if ($receiver === null) {
            return $rows;
        }
        while ($row = $receiver->fetch()) {
            if ($this->collectRowsId) {
                $this->rowsId[] = $row[$primaryKey];
            }
            $this->beforeFetchRow($row);
            $row = $this->fetchRow($row);
            if ($row === null) {
                continue;
            }
            if ($mask)
                $row = $this->maskedFetchRow($row, $mask, $fieldOptions);
            // если в конфигурации модели данных указан аудит записей "useAudit" и есть
            // разрешение на просмотр аудита записей, то выполняется обработка столбцов аудита,
            // установка соответствующего часового пояса
            if ($useAudit) {
                $this->auditRow($row);
            }
            $this->prepareRow($row);
            $this->afterFetchRow($row, $rows);
        }
        return $rows;
    }

    /**
     * Возвращает значения $row ввиде маски $mask с использованием рендера
     * (если название рендера указано в параметре поля).
     *
     * @param array $row Имена полей с их значениями, полученные на каждом шаге итерации {@see fetchRow()}.
     * @param array $mask Маска полей {@see maskedRow()}.
     * @param array $options Настройки полей из менеджера данных {@see \Gm\Data\DataManager::$fieldOptions}.
     *
     * @return array Маска полей с их значениями.
     */
    public function maskedFetchRow(array $row, array $mask, array $options): array
    {
        if (empty($row) || empty($mask)) return $row;

        $masked = [];
        foreach ($mask as $alias => $field) {
            $value = isset($row[$field]) ? $row[$field] : null;
            if (isset($options[$alias]['render'])) {
                $render = $options[$alias]['render'];
                $value = $this->{$render}($value, $row, $options[$alias]);
            }
            if ($value !== null) {
                // чтобы следующее поле в маске видело измнения совершенные предыдущем полем,
                // если у поля был параметр "render".
                $row[$field] = $value;
                $masked[$alias] = $value;
            } else {
                $masked[$alias] = $value;
            }
        }
        return $masked;
    }

    /**
     * Подготавливает запись к дальнейшему выводу.
     * 
     * @param array $row Имена полей с их значениями, полученные по маске из {@see maskedFetchRow()}.
     *    Если одно из полей записи имеет значение null, $row его не будет включать. 
     * 
     * @return void
     */
    public function prepareRow(array &$row): void
    {
    }

    /**
     * Возвращает запись запроса для {@see fetchRows()}.
     *
     * @param array $row Запись из запроса {@see beforeFetchRow()}.
     *
     * @return array
     */
    public function fetchRow(array $row): array
    {
        return $row;
    }

    /**
     * Возвращает запись запроса для {@see fetchRow()}.
     *
     * @param array $row Запись запроса.
     *
     * @return void
     */
    public function beforeFetchRow(array &$row): void
    {
    }

    /**
     * Добавляет запись запроса полученная из {@see fetchRow()} или {@see maskedFetchRow()}
     * к результирующим записям.
     *
     * @param array $row Запись запроса.
     * @param array $rows Все записи запроса.
     *
     * @return void
     */
    public function afterFetchRow(array $row, array &$rows): void
    {
        $rows[] = $row;
    }

    /**
     * Событие, возникающие перед получением записей.
     * 
     * Получение записей {@see fetchRows()}.
     * 
     * @see GridMode::selectBySql()
     * @see GridMode::selectAll()
     *
     * @return void
     */
    public function beforeFetchRows(): void
    {
    }

    /**
     * Метод вызывается после выполнения запроса в {@see selectBySql()}, {@see selectAll()} и получения записей {@see fetchRows()}.
     *
     * @param array $rows Записи полученные в результате запроса.
     *
     * @return array
     */
    public function afterFetchRows(array $rows): array
    {
        return $rows;
    }

    /**
     * Возвращает одну запись методом {@see selectByPk()} по значению первичного ключа таблицы.
     *
     * @return array
     */
    public function getRow(): array
    {
        return [];
    }

    /**
     * Возвращает записи с использованием конструктора запроса {@see buildQuery()}.
     *
     * @return array
     */
    public function getRows(): array
    {
        return $this->selectAll();
    }

    /**
     * Возвращает дополнительные записи.
     *
     * @return array
     */
    public function getSupplementRows(): array
    {
        return [];
    }

    /**
     * Процесс подготовки условий для удаления записи.
     * 
     * @see GridModel::deleteProcess()
     * 
     * @param array $where Условие удаления записи.
     * 
     * @return void
     */
    protected function deleteProcessCondition(array &$where): void
    {
        $db = $this->getDb();

        // если в запросе указан идентификатор
        $identifier = $this->getIdentifier();
        if ($identifier) {
            $where[$db->rawExpression($this->dataManager->fullPrimaryKey())] = $identifier;
        }
        // если есть поле "_lock" в таблице
        if ($this->dataManager->lockRows) {
            $where[] = $db->rawExpression($this->tableName() . '._lock <> 1');
        }
    }

    /**
     * Процесс подготовки условий для удаления всех записей.
     * 
     * @see GridModel::deleteAll()
     * 
     * @param array $where Условие удаления записей.
     * 
     * @return void
     */
    protected function deleteAllProcessCondition(array &$where): void
    {
        // если есть поле "_lock" в таблице
        if ($this->dataManager->lockRows) {
            $where[] = $this->getDb()->rawExpression($this->tableName() . '._lock <> 1');
        }
    }

    /**
     * Удаление текущей записи.
     * 
     * @return false|int Если была ошибка - false, иначе количество удаленных записей.
     */
    public function delete(): false|int
    {
        $result = false;
        if ($this->beforeDelete()) {
            // условие запроса удаления записей
            $condition = [];
            $this->deleteProcessCondition($condition);
            // удаление записей в зависимых таблицах по внешнему ключу
            $dependencies = $this->dataManager->getDependency('delete');
            // если указаны методы удаления зависимых записей 
            if (isset($dependencies['callable'])) {
                $callables = $dependencies['callable'];
                unset($dependencies['callable']);
            } else
                $callables = [];
            if ($dependencies) {
                $this->deleteDependencies($dependencies, $condition);
            }
            // если указаны методы удаления зависимых записей 
            if ($callables) {
                if ($id = $this->getIdentifier()) {
                    foreach ($callables as $callable) {
                        $this->$callable($id);
                    }
                }
            }
            $result = $this->deleteRecord($condition);
            $this->afterDelete(true, $result);
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAll(string $tableName = null): false|int
    {
        $result = false;
        if ($this->beforeDelete(false)) {
            // условие запроса удаления записей
            $condition = [];
            $this->deleteAllProcessCondition($condition);
            $dependencies = $this->dataManager->getDependency('deleteAll');
           
            // если указаны методы удаления зависимых записей 
            if (isset($dependencies['callable'])) {
                $callables = $dependencies['callable'];
                unset($dependencies['callable']);
            } else
                $callables = [];
            if ($dependencies) {
                // удаление записей в зависимых таблицах по внешнему ключу
                $this->deleteDependencies($dependencies, $condition);
            }
            // если указаны методы удаления зависимых записей 
            if ($callables) {
                foreach ($callables as $callable) {
                    $this->$callable();
                }
            }
            $result = $this->deleteRecord($condition);
            // сброс значений первичных ключей
            $this->resetIncrements();
            $this->afterDelete(false, $result);
        }
        return $result;
    }

    /**
     * Сброс значений последовательности (автоинкримента) первичных ключей таблиц {@see tableResetIncrements()}, 
     * полученных из менеджера данных {@see \Gm\Data\DataManager::$resetIncrements}.
     *
     * @return void
     */
    public function resetIncrements(): void
    {
        $tables = $this->tableResetIncrements();
        foreach ($tables as $tableName)
            $this->resetIncrement(1, $tableName);
    }

    /**
     * Этот событие вызывается перед удалением записи.
     *
     * @param bool $someRecords Значение `true`, если удаление выбранных записей, иначе все записи.
     *
     * @return bool Должна ли запись быть удалена. По умолчанию - true.
     */
    public function beforeDelete(bool $someRecords = true): bool
    {
        /** @var bool $canDelete возможность удаления записи определяет событие */
        $canDelete = true;
        $this->trigger(
            self::EVENT_BEFORE_DELETE,
            [
                'someRecords' => $someRecords,
                'canDelete'   => &$canDelete
            ]
        );
        return $canDelete;
    }

    /**
     * Этот событие вызывается после удаления записи.
     *
     * @param bool $someRecords Если `true`, удаление выбранных (выделенных) записей.
     * @param bool|int Если `false`, ошибка удаления записей. Иначе, количество удаленных записей.
     *
     * @return void
     */
    public function afterDelete(bool $someRecords = true, $result = null)
    {
        $this->trigger(
            self::EVENT_AFTER_DELETE,
            [
                'someRecords' => $someRecords,
                'result'      => $result,
                'message'     => $this->deleteMessage($someRecords, (int) $result)
            ]
        );
    }

    /**
     * Возвращает количество выделенных (выбранных) записей из запроса.
     *
     * @return int
     */
    public function getSelectedCount(): int
    {
        return ($id = $this->getIdentifier()) ? sizeof($id) : 0;
    }

    /**
     * Возвращет текст сообщения о удалении записей.
     *
     * @param string $type Вид действия, может иметь значение:
     *     - 'partiallySome', выбранные записи удалены частично;
     *     - 'successfullySome', выбранные записи удалены полностью;
     *     - 'unableSome', выбранные записи не удалены;
     *     - 'partiallyAll', все записи удалены частично;
     *     - 'successfullyAll', все записи удалены полностью;
     *     - 'unableAll', все выбранные записи не удалены.
     * @param array $params Параметры перевода (локализации сообщения).
     * 
     * @return string Текст сообщения о удалении записей.
     */
    protected function deleteMessageText(string $type, array $params): string
    {
        switch ($type) {
            // выбранные записи удалены частично
            case 'partiallySome':
                return Gm::t(
                    BACKEND,
                    'The records were partially deleted, from the selected {nSelected} {selected, plural, =1{record} other{records}}, {nDeleted} were deleted, the rest were omitted',
                    $params
                );
            // выбранные записи удалены полностью
            case 'successfullySome':
                return Gm::t(
                    BACKEND,
                    'Successfully deleted {N} {n, plural, =1{record} other{records}}',
                    $params
                );
            // выбранные записи не удалены
            case 'unableSome':
                return Gm::t(
                    BACKEND,
                    'Unable to delete {N} {n, plural, =1{record} other{records}}, no records are available',
                    $params
                );
            // все записи удалены частично
            case 'partiallyAll':
                return Gm::t(
                    BACKEND,
                    'Records have been partially deleted, {nDeleted} deleted, {nSkipped} {skipped, plural, =1{record} other{records}} skipped',
                    $params
                );
            // все записи удалены полностью
            case 'successfullyAll':
                return Gm::t(
                    BACKEND,
                    'Successfully deleted {N} {n, plural, =1{record} other{records}}',
                    $params
                );
            // все выбранные записи не удалены
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

    /**
     * Возвращает информацию о удалении записей.
     *
     * @see GridModel::afterDelete()
     * 
     * @param bool $someRecords Если `true`, удаление выбранных записей.
     * @param int $result Количество удаленных записей.
     * 
     * @return array Информация.
     */
    public function deleteMessage(bool $someRecords, int $result): array
    {
        $type     = 'accept';
        $message  = '';
        // удаление выбранных записей
        if ($someRecords) {
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
                            ['n' => $selected, 'N' => $selected]
                        );
                // записи не удалены
                } else {
                    $message = $this->deleteMessageText(
                        'unableSome',
                        ['n' => $selected, 'N' => $selected]
                    );
                    $type = 'error';
                }
        // удаление всех записей
        } else {
            $missed   = $this->selectCount();
            $selected = $result;
            // записи удалены
            if ($result > 0) {
                // записи удалены частично
                if ($missed > 0) {
                     $message = $this->deleteMessageText(
                        'partiallyAll',
                        [
                            'deleted' => $result, 'nDeleted' => $result,
                            'skipped' => $missed, 'nSkipped' => $missed
                        ]
                    );
                    $type = 'warning';
                // записи удалены полностью
                } else {
                    $message = $this->deleteMessageText(
                        'successfullyAll',
                        ['n' => $result, 'N' => $result]
                    );
                }
            // записи не удалены
            } else {
                $message = $this->deleteMessageText(
                    'unableAll',
                   ['n' => $selected]
                );
                $type = 'error';
            }
        }
        return [
            'selected' => $selected, // количество выделенных записей в списке
            'deleted'  => $result, // количество удаленных записей
            'missed'   => $missed, // количество пропущенных записей
            'success'  => $missed == 0, // успех удаления записей
            'message'  => $message, // сообщение
            'title'    => Gm::t(BACKEND, 'Deletion'), // загаловок сообщения
            'type'     => $type // тип сообщения
        ];
    }

    /**
     * Возвращает идентификаторы записей полученные HTTP-запросом с помощью метода POST.
     * 
     * @return array<int, int> Массив идентификаторов записей.
     */
    public function getIdentifier(): array
    {
        if ($this->identifier === null) {
            $identifier = Gm::$app->request->getPost('id');
            $this->identifier = $identifier ? explode(',', $identifier) : [];
        }
        return $this->identifier;
    }

    /**
     * Проверяет, получены ли идентификаторы записей HTTP-запросом с помощью метода POST.
     * 
     * @see GridModel::getIdentifier()
     * 
     * @return bool
     */
    public function hasIdentifier(): bool
    {
        $identifier = $this->getIdentifier();
        return !empty($identifier);
    }

    /**
     * Возвращает значение первичного ключа.
     * 
     * @param mixed $value Значение поля установленного методом {@see maskedFetchRow()}.
     * @param array $row Имена полей с их значениями полученные методом {@see fetchRow()}.
     * @param array $options Настройки полей из раздела менеджера данных {@see $fieldOptions}.
     * 
     * @return mixed
     */
    public function renderPrimaryKey(mixed $value, array $row, array $options): mixed
    {
        return $row[$this->dataManager->primaryKey];
    }

    /**
     * Возвращает значение даты и времени, полученное в результате преобразования часового пояса UTC в часовой 
     * пояс клиента.
     * 
     * @param mixed $value Значение даты и времени в часовом поясе UTC.
     * @param string $format Формат даты и времени.
     * 
     * @return mixed Значение даты и времени в часовом поясе клиента.
     */
    public function fetchDateTimeField(mixed $value, string $format = 'Y-m-d H:i:s'): mixed
    {
        static $userTZ;

        if ($userTZ === null) $userTZ = Gm::$app->user->getTimeZone();
        return empty($value) ? $value : $this->formatter->toDateTimeZone($value, $format, false, Gm::$app->dataTimeZone, $userTZ);
    }

    /**
     * Возвращает собранные идентификаторы строк.
     * 
     * Собранные идентификаторы строк применяются для вспомогатльных запросов.
     * Такие идентификаторы необходимо собирать с помощью метода {@see GridModel::fetchRows()}.
     * 
     * @see GridModel::$rowsId
     * 
     * @return array
     */
    public function getCollectedRowsId(): array
    {
        return $this->rowsId;
    }
}
