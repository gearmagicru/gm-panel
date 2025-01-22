<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Audit\Storage;

use Gm;

/**
 * DbStorage - хранение записей журнала аудита в базе данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Audit\Storage
 * @since 1.0
 */
class DbStorage extends AbstractStorage
{
    /**
     * Имя таблицы журнала в базе данных.
     * 
     * @var string
     */
    public string $tableName = '{{audit}}';

    /**
     * Последний порядковый номер в журнале аудита.
     *
     * @var int
     */
    protected int $index = 0;

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            'userId'           => 'user_id',
            'userName'         => 'user_name',
            'userDetail'       => 'user_detail',
            'permission'       => 'user_permission',
            'ipaddress'        => 'user_ipaddress',
            'moduleId'         => 'module_id',
            'moduleName'       => 'module_name',
            'controllerName'   => 'controller_name',
            'controllerAction' => 'controller_action',
            'controllerEvent'  => 'controller_event',
            'browserName'      => 'meta_browser_name',
            'browserFamily'    => 'meta_browser_family',
            'osName'           => 'meta_os_name',
            'osFamily'         => 'meta_os_family',
            'requestUrl'       => 'request_url',
            'requestMethod'    => 'request_method',
            'requestCode'      => 'request_code',
            'querySql'         => 'query_sql',
            'queryId'          => 'query_id',
            'query'            => 'query_params',
            'error'            => 'error',
            'errorCode'        => 'error_code',
            'errorParams'      => 'error_params',
            'success'          => 'success',
            'comment'          => 'comment',
            'date'             => 'date'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function hasLimitRows(): bool
    {
        if (empty($this->limit) || empty($this->index)) {
            return false;
        }
        return $this->index > $this->limit;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        /** @var \Gm\Db\Adapter\Driver\AbstractCommand $command */
        $command = Gm::$app->db->createCommand();
        $command
            ->delete($this->tableName)
            ->execute();
        $command
            ->resetIncrement($this->tableName)
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $attributes = []): void
    {
        /** @var \Gm\Db\Adapter\Adapter $db */
        $db = Gm::$app->db;

        $attributes = $this->unmaskedAttributes($attributes);
        if ($attributes) {
            /** @var \Gm\Db\Adapter\Driver\AbstractCommand $command */
            $command = $db->createCommand()
                ->insert($this->tableName, $attributes)
                ->execute();
            // последний порядковый номер
            $this->index = $db->getConnection()->getLastGeneratedValue();
            // если достигнут лимит записей в журнале аудита
            if ($this->hasLimitRows()) {
                $this->clear();
            }
        }
    }
}
