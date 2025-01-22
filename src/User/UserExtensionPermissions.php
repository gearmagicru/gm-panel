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
 * Разрешения (права доступа) пользователя к плагинам.
 * 
 * Разрешения имеют вид:
 * ```php
 * [
 *     '{pluginCode}.{permission}'   => true,
 *     '{pluginCode1}.{permission1}' => true,
 *     '{pluginCode2}.{permission2}' => true,
 *     ...
 * ]
 * ```
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\User
 * @since 1.0
 */
class UserExtensionPermissions extends UserStorage
{
    /**
     * {@inheritdoc}
     */
    protected $storageMember = 'extensionPermissions';

    /**
     * Проверяет, есть ли разрешение для указанного кода плагинов.
     * 
     * @param string $code Код плагинов.
     * @param string $name Имя разрешения.
     * 
     * @return bool Возвращает `true`, если разрешение присутствует. 
     */
    public function hasPermission(string $name): bool
    {
        return isset($this->container[$name]);
    }

    public function isGranted(string $name): bool
    {
        return isset($this->container[$name]);
    }

    /**
     * Возвращает все разрешения плагинов.
     * 
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->container;
    }

    /**
     * Поиск разрешений расширений доступных пользователю.
     * 
     * @return array Роли доступные пользователю в виде пар "идентификатор - роль". Иначе, 
     *     пустой массив.
     */
    public function find()
    {
        /** @var \Gm\ModuleManager\ExtensionRegistry $installed */
        $installed = Gm::$app->extensions->getRegistry();
        /** @var \Gm\Db\Adapter\Adapter $db */
        $db = Gm::$app->db;

        /** @var \Gm\Db\Sql\Select $select */
        $select = $db
            ->select('{{extension_permissions}}')
            ->columns(['*'])
            ->where(['role_id' => [$this->_identity->getRoles()->ids()]]);
        /** @var \Gm\Db\Adapter\Driver\AbstractCommand $command */
        $command = $db->createCommand($select);
        $command->query();
        $rows = [];
        while ($row = $command->fetch()) {
            $extension = $installed->getAtMap($row['extension_id']);
            if (empty($extension)) {
                continue;
            }
            $permissions = $row['permissions'];
            if ($permissions) {
                $permissions = explode(',', $permissions);
                foreach ($permissions as $permission) {
                    $rows[$extension['id'] . '.' . $permission] = true;
                }
            }
        }
        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function write()
    {
        $this->container =  $this->find();

        parent::write();
    }
}
