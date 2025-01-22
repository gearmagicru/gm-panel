<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Audit;

use Gm;
use Gm\Stdlib\Service;
use Gm\Panel\Audit\Storage\AbstractStorage;

/**
 * Журнал аудита действий пользователей.
 * 
 * Журнал аудита предназначен для записи всех действий контроллеров вызываемых 
 * пользователями с помощью поведения {@see \Gm\Panel\Audit\Behaviors\AuditBehavior}.
 * 
 * Результат действия контроллера - это атрибуты информации распределённые по разделам,
 * где можно указать, какие разделы будут добавлены в журнал аудита.
 * Например:
 * ```php
 *  return [
 *      'audit' => [
 *          // ...
 *          'sections' => ['user', 'controller', 'device']
 *      ]
 * ];
 * ```
 * Здесь указано, что будет добавлена информация о пользователе, его устройстве и контроллере.
 * 
 * Информация добавляется в хранилище {@see Audit::$storage}, где хранилище может быть: база данных, 
 * файл, память и т.д.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Audit
 * @since 1.0
 */
class Audit extends Service
{
    /**
     * {@inheritdoc}
     */
    protected bool $useUnifiedConfig = true;

    /**
     * Параметры конфигурации хранилища журнала аудита.
     * 
     * Эти параметры будут задействованы при создании хранилища 
     * с помощью {@see \Gm::createObject()} и могут иметь вид:
     * ```php
     *  return [
     *      'audit' => [
     *          // ...
     *          'storage' => [
     *              'class'     => '\Gm\Panel\Audit\Storage\DbStorage',
     *              'tableName' => '{{audit}}',
     *              'limit'     => 1000
     *           ]
     *      ]
     * ];
     * ```
     * 
     * @var array<string, mixed>
     */
    public array $storage;

    /**
     * Имена разделов атрибутов информации, которые будет записаны в журнал аудита.
     * 
     * Доступны следующие разделы:
     * - `user`, информация о пользователе;
     * - `controller`, информация о контроллере;
     * - `module`, информация о модуле;
     * - `request`, информация о запросе пользователя;
     * - `device`, информация об устройстве пользователя.
     *
     * @see Audit::$properties
     * 
     * @var array<int, string>
     */
    public array $sections = [];

    /**
     * Атрибуты информации с указанными разделами.
     *
     * @var array<string, array<int, string>>
     */
    public array $properties = [
        'user' => [
            'userId', 'userName', 'userDetail', 'permission'
        ],
        'controller' => [
            'controllerName', 'controllerAction', 'controllerEvent'
        ],
        'module' => [
            'moduleId', 'moduleName'
        ],
        'device' => [
            'browserName', 'browserFamily', 'osName', 'osFamily'
        ],
        'request' => [
            'requestMethod', 'requestCode', 'requestUrl', 'ipaddress', 'query', 'queryId', 
            'date', 'status', 'success', 'error', 'errorCode', 'errorParams', 'note', 'comment'
        ],
    ];

    /**
     * Объект получения и обработки разделов, атрибутов информации.
     * 
     * @see Audit::init()
     * 
     * @var Info
     */
    public Info $info;

    /**
     * {@inheritdoc}
     */
    public function getObjectName(): string
    {
        return 'audit';
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        if (!isset($this->storage)) {
            throw new Exception\InvalidConfigException('Audit::storage properties must be set.');
        }
        $this->info = new Info();
    }

    /**
     * Подготавливает (собирает) информацию перед выполнением записи в 
     * журнал аудита.
     * 
     * Значение каждого атрибута информации будет определено в {@see \Gm\Panel\Audit\Info::defineProperty()}.
     * Каждый раздел атрибутов информации инициализируется методом объекта информации {@see \Gm\Panel\Audit\Info}.
     * 
     * @param array $sections Имена разделов атрибутов информации, которые будет 
     *     записаны в журнал аудита. Если разделы не указаны, то будут использованы 
     *     {@see Audit::$sections}.
     * 
     * @return $this
     */
    public function prepare(array $sections = []): static
    {
        if (empty($sections)) {
            $sections = $this->sections;
        }
        foreach ($sections as $name) {
            $method = $name . 'Section';
            if (method_exists($this->info, $method)) {
                $this->info->$method();
            }
            if (isset($this->properties[$name])) {
                foreach ($this->properties[$name] as $property) {
                    $this->info->defineProperty($property);
                }
            }
        }
        return $this;
    }

    /**
     * Хранилище журнала аудита.
     *
     * @var AbstractStorage
     */
    protected AbstractStorage $_storage;

    /**
     * Возвращает хранилище журнала аудита.
     * 
     * Если хранилище не создано, создаёт его с указанными параметрами 
     * конфигурации хранилища {@see Audit::$storage}.
     *
     * @return AbstractStorage Хранилище журнала аудита.
     */
    public function getStorage(): AbstractStorage
    {
        if (!isset($this->_storage)) {
            $this->_storage = Gm::createObject($this->storage);
        }
        return $this->_storage;
    }

    /**
     * Устанавливает журналу аудита хранилище.
     *
     * @param AbstractStorage $storage Хранилище журнала аудита.
     * 
     * @return $this
     */
    public function setStorage(AbstractStorage $storage): static
    {
        $this->_storage = $storage;
        return $this;
    }

    /**
     * Проверяет, имеется ли раздел атрибутов информации, который будет записаны в 
     * журнал аудита.
     * 
     * @param string $name Имя раздела.
     * 
     * @return bool
     */
    public function hasSection(string $name): bool
    {
        return in_array($name, $this->sections);
    }

    /**
     * Выполняет запись действий пользователя в журнал аудита.
     * 
     * Перед выполнением записи, информация подготавливается {@see Audit::prepare()} и
     * определяется хранилище {@see Audit::getStorage()} для записи.
     * 
     * @see Audit::prepare()
     * @see Audit::getStorage()
     * 
     * @return $this
     */
    public function write(): static
    {
        if (!$this->enabled) {
            return $this;
        }

        $this->prepare();
        $this->getStorage()->write($this->info->all());
        return $this;
    }
}
