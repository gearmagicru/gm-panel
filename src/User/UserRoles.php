<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\User;

use Gm;
use Gm\User\UserStorage;

/**
 * Роли пользователя.
 * 
 * Роли имеют вид:
 * ```php
 * [
 *     "идентификатор роли 1" => ["id" => 1, "name" => "роль 1",...],
 *     "идентификатор роли 2" => ["id" => 2, "name" => "роль 2",...],
 *     ...
 * ]
 * ```
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\User
 * @since 1.0
 */
class UserRoles extends UserStorage
{
    /**
     * {@inheritdoc}
     */
    protected string $storageMember = 'roles';

    /**
     * Идентификаторы ролей пользователя через разделитель ",".
     * 
     * @see UserRoles::ids()
     * 
     * @var string
     */
    private string $_ids;

    /**
     * Возвращает идентификаторы ролей пользователя через разделитель ",".
     * 
     * @param bool $toString Идентификаторы ролей пользователя в виде строки, иначе 
     *     в виде массива (по умолчанию `true`).
     * 
     * @return string|array
     */
    public function ids(bool $toString = true): string|array
    {
        if ($toString) {
            if (!isset($this->_ids)) {
                $this->_ids = $this->keysToString();
            }
            return $this->_ids;
        }
        return array_keys($this->container);
    }

    /**
     * Определяет, был ли установлен идентификатор(ы) ролей.
     * 
     * @see \Gm\Stdlib\Collection::has()
     * 
     * @param int|array<int> $roleId Идентификатор(ы) ролей.
     * 
     * @return bool Возвращает `true`, если идентификатор(ы) ролей коллекции определён 
     *     и его значение отлично от `null`, и false в противном случае. 
     */
    public function has($roleId): bool
    {
        if (is_array($roleId)) {
            foreach ($roleId as $id) {
                if (isset($this->container[$id])) return true;
            }
            return false;
        }
        return isset($this->container[$roleId]);
    }

    /**
     * Поиск ролей доступных пользователю.
     * 
     * @param null|int $userId Идентификатор пользователя. Если значение `null`, 
     *     то идентификатор авторизованного пользователя.
     * 
     * @return array<int, array> Роли доступные пользователю в виде пар "идентификатор - роль".
     */
    public function find(?int $userId = null)
    {
        if ($userId === null) {
            $userId = $this->_identity->id;
        }

        /** @var \Gm\Db\Adapter\Adapter $db */
        $db = Gm::$app->db;
        /** @var \Gm\Db\Adapter\Driver\AbstractCommand $command */
        $command = $db->createCommand(
            'SELECT `role`.`id`,`role`.`name`,`role`.`shortname`,`role`.`description` '
          . 'FROM {{user_roles}} `urole` JOIN {{role}} `role` ON `role`.`id` = `urole`.`role_id` WHERE `urole`.`user_id`=:user_id'
        );
        $command
            ->bindValue(':user_id', $userId)
            ->query();
        $rows = [];
        while ($row = $command->fetch()) {
            $rows[$row['id']] = $row;
        }
        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function write()
    {
        $this->container = $this->find();
        $this->_identity->getBac()->createStorage($this->container);

        parent::write();
    }
}
