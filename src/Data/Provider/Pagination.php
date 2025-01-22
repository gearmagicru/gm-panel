<?php
/**
 * Этот файл является частью пакета GM Framework.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Data\Provider;

use Gm;
use Gm\Stdlib\BaseObject;

/**
 * Класс Pagination (пагинация) представляет информацию о разбивке на страницы элементов 
 * данных.
 * 
 * Используется тогда, когда необходимо отобразить данные с разбивкой на несколько страниц.
 * Для этого применяются такие свойства, как:
 * - количества выводимых элементов на странице {@see Pagination::$limit};
 * - номер текущей страницы {@see Pagination::$page}.
 * Эти свойства могут передаваться источником данных {@link https://docs.sencha.com/extjs/5.1.3/api/Ext.data.Store.html} 
 * виджета панели управления для разбивки его элементов с помощью панели навигации
 * {@link https://docs.sencha.com/extjs/5.1.3/api/Ext.toolbar.Paging.html}.

 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Data\Provider
 * @since 2.0
 */
class Pagination extends BaseObject
{
    /**
     * Метод запроса получения значений параметров.
     * 
     * Например: 'POST', 'GET'.
     *
     * @var string
     */
    public string $method = 'POST';

    /**
     * Количества выводимых элементов.
     * 
     * Значение определяется с помощью {@see Pagination:defineLimit()}, но есть возможность 
     * указать в конфигурации конструктора класса.
     * 
     * @see Pagination::configure()
     * 
     * @var int
     */
    public int $limit;

    /**
     * Параметр передаваемый HTTP-запросом, указывающий количество элементов на странице.
     * 
     * Параметр передаётся с помощью метода POST и определяется {@see Pagination::defineLimit()}.
     * Если значение параметра `false`, тогда будет применяться значение {@see Pagination::$defaultLimit}.
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
     * @var array<int, int>
     */
    public array $limitFilter = [];

    /**
     * Значение количества выводимых элементов по умолчанию.
     * 
     * Используется в том случаи, если значение параметра {@see BaseProvider::$limitParam} 
     * отсутствует в HTTP-запросе.
     * 
     * @var int
     */
    public int $defaultLimit = 10;

    /**
     * Максимальное допустимое количество выводимых элементов.
     * 
     * Применяется для проверки в том случаи, если значение отличное от `null` и не 
     * установлен фильтр количества элементов {@see BaseProvider::$limitFilter}.
     * 
     * @var int|null
     */
    public int $maxLimit = 0;

    /**
     * Номер страницы.
     * 
     * Значение определяется с помощью {@see Pagination:definePage()}, но есть возможность 
     * указать в конфигурации конструктора класса.
     * 
     * Pagination::configure()
     * 
     * @var int
     */
    public int $page;

    /**
     * Параметр передаваемый HTTP-запросом, указывающий номер страницы.
     * 
     * Параметр передаётся с помощью метода POST и определяется {@see Pagination::definePage()}.
     * Если значение параметра `false`, тогда значение будет '1'.
     * 
     * @var string|false
     */
    public string|false $pageParam = 'page';

    /**
     * Количество элементов, которые необходимо пропустить перед выводом.
     * 
     * @see Pagination::configure()
     * 
     * @var int
     */
    public int $start;

    /**
     * Параметр передаваемый HTTP-запросом, указывающий количество элементов, которые 
     * необходимо пропустить.
     * 
     * Параметр передаётся с помощью метода POST и определяется {@see Pagination::defineStart()}.
     * Если значение параметра `false`, тогда значение будет '0'.
     * 
     * @var string|false
     */
    public string|false $startParam = 'start';

    /**
     * @var int
     */
    public int $totalCount = 0;

    /**
     * Определяет, что парамтер $limit получен из HTTP-запроса.
     * 
     * @see Pagination::defineLimit()
     * 
     * @var bool
     */
    protected bool $hasLimit = false;

    /**
     * Определяет, что парамтер $page получен из HTTP-запроса.
     * 
     * @see Pagination::definePage()
     * 
     * @var bool
     */
    protected bool $hasPage = false;

    /**
     * Определяет, что парамтер $start получен из HTTP-запроса.
     * 
     * @see Pagination::defineStart()
     * 
     * @var bool
     */
    protected bool $hasStart = false;

    /**
     * {@inheritdoc}
     */
    public function configure(array $config): void
    {
        parent::configure($config);

        $this->limit = $this->defineLimit();
        $this->page = $this->definePage();
        $this->start = $this->defineStart();
    }

    /**
     * Определяет количество элементов на странице.
     * 
     * - если значение указано в параметрах конфигурации, тогда возвратит {@see Pagination::$limit};
     * - если не указан параметр запроса или сам параметр отсутствует в запросе, 
     * тогда возвратит {@see Pagination::$defaultLimit};
     * - если значение параметра не входит в указанный фильтр или является не допустимым, 
     * тогда возвратит {@see Pagination::$defaultLimit}.
     * 
     * @see Pagination::$limit
     * 
     * @return int
     */
    public function defineLimit(): int
    {
        // если значение указано в параметрах конфиграции
        if (isset($this->limit)) {
            return $this->limit;
        }
        // если запрещено получать значение из HTTP-запроса
        if ($this->limitParam === false) {
            return  $this->defaultLimit;
        }

        if ($this->method === 'POST')
            $limit = Gm::$app->request->getPost($this->limitParam, null);
        else
            $limit = Gm::$app->request->getQuery($this->limitParam, null);
        if ($limit === null) {
            return $this->defaultLimit;
        }
        // параметр был получен из запроса
        $this->hasLimit = true;

        $limit = (int) $limit;
        if ($limit <= 1) {
            return $this->defaultLimit;
        }

        if ($this->limitFilter) {
            if (!in_array($limit, $this->limitFilter)) {
                return $this->defaultLimit;
            }
        } else {
            if ($this->maxLimit && $limit > $this->maxLimit) {
                return $this->defaultLimit;
            }
        }
        return $limit;
    }

    /**
     * Определяет номер текущей страницы.
     * 
     * - если значение указано в параметрах конфигурации, тогда возвратит {@see Pagination::$page};
     * - если не указан параметр запроса или сам параметр отсутствует в запросе, 
     * тогда возвратит '1';
     * - если значение параметра является не допустимым, тогда возвратит '1'.
     * 
     * @see Pagination::$pageParam
     * 
     * @return int
     */
    public function definePage(): int
    {
        // если значение указано в параметрах конфиграции
        if (isset($this->page)) {
            return $this->page;
        }
        // если запрещено получать значение из HTTP-запроса
        if ($this->pageParam === false) {
            return  1;
        }

        if ($this->method === 'POST')
            $page = Gm::$app->request->getPost($this->pageParam, null);
        else
            $page = Gm::$app->request->getQuery($this->pageParam, null);
        if ($page === null) {
            return 1;
        }
        // параметр был получен из запроса
        $this->hasPage = true;

        $page = (int) $page;
        // если значение превышает допустимое количество
        if ($this->totalCount !== null) {
            if ($page > $this->getPageCount())
                return 1;
        }
        return $page < 1 ? 1 : $page;
    }

    /**
     * Определяет количество элементов, которые необходимо пропустить перед выводом.
     * 
     * - если значение указано в параметрах конфигурации, тогда возвратит {@see Pagination::$start};
     * - если не указан параметр запроса или сам параметр отсутствует в запросе, 
     * тогда возвратит '0';
     * - если значение параметра является не допустимым, тогда возвратит '0'.
     * 
     * @return int
     */
    public function defineStart(): int
    {
        // если значение указано в параметрах конфиграции
        if (isset($this->start)) {
            return $this->start;
        }
        // если запрещено получать значение из HTTP-запроса
        if ($this->startParam === false) {
            return  0;
        }

        if ($this->method === 'POST')
            $start = Gm::$app->request->getPost($this->startParam, null);
        else
            $start = Gm::$app->request->getQuery($this->startParam, null);
        if ($start === null) {
            return 0;
        }
        // параметр был получен из запроса
        $this->hasStart = true;

        $start = (int) $start;
        if ($start <= 1) {
            return 0;
        }
        return $start;
    }

    /**
     * Возврашает количество элементов выводимых на странице.
     * 
     * @see Pagination::$limit
     * 
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Возвращает количество элементов на странице.
     * 
     * @param int|null $totalCount Общее количество элементов. Если значение `null`,
     *     будет использовано {@see Pagination::$totalCount} (по умолчанию `null`).
     * 
     * @see Pagination::$totalCount
     * @see Pagination::$limit
     * 
     * @return int
     */
    public function getPageCount(?int $totalCount = null): int
    {
        if ($totalCount === null) {
            $totalCount = (int) $this->totalCount;
        }
        $totalCount = $totalCount < 0 ? 0 : (int) $totalCount;

        // если выводятся все элементы
        if ($this->limit === 0) {
            return $totalCount > 0 ? 1 : 0;
        }
        return intdiv($totalCount + $this->limit - 1, $this->limit);
    }

    /**
     * Возвращает параметры запроса (для поставщика данных) полученные из URL-адреса.
     * 
     * @see BaseProvider::getQueryParams()
     * 
     * @param int|null $page Количество элементов на странице. Если значение указано, 
     *     то оно обязательно будет в возвращаемом параметре (по умолчанию `null`).
     * @param int|null $start Количество элементов, которые необходимо пропустить 
     *     перед выводом (по умолчанию `null`).
     * @param int|null $limit Количество элементов выводимых на странице. Если значение 
     *     указано, то оно обязательно будет в возвращаемом параметре (по умолчанию `null`).
     * 
     * @return array Возвращаемые параметры могут иметь вид: `['page' => 1, 'limit' => 20]`.
     */
    public function getQueryParams(?int $page = null, ?int $start = null, ?int $limit = null): array
    {
        $params = [];
        if ($page)
            $params[$this->pageParam] = $page;
        else
        if ($this->hasPage)
            $params[$this->pageParam] = $this->page;

        if ($start)
            $params[$this->startParam] = $start;
        else
        if ($this->hasStart)
            $params[$this->startParam] = $this->start;

        if ($limit)
            $params[$this->limitParam] = $limit;
        else
        if ($this->hasLimit)
            $params[$this->limitParam] = $this->limit;
        return $params;
    }
}
