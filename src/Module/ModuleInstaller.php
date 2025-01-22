<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Module;

use Gm;
use Gm\Mvc\Module\BaseModule;

/**
 * Установщик модуля.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Module\Installer
 * @since 1.0
 */
class ModuleInstaller extends \Gm\ModuleManager\ModuleInstaller
{
    /**
     * Модуль, контроллер которого выполняет установку.
     * 
     * @see ModuleInstaller::configure()
     * 
     * @var BaseModule
     */
    public BaseModule $module;

    /**
     * {@inheritdoc}
     */
    public function afterInstall(array $params): void
    {
        $post = Gm::$app->request->getPost(['roles' => null, 'partitionbar' => null]);

        /** @var \Gm\ModuleManager\ModuleManager Менеджер модулей */
        $modules = Gm::$app->modules;

        // панель разделов, куда устанавливается модуль
        if ($post['partitionbar'] && $post['partitionbar'] !== 'null') {
            /** @var null|\Backend\Partitionbar\Model\PartitionbarModule $pbarModule */
            $pbarModule = $modules->getModel('PartitionbarModule', 'gm.be.partitionbar');
            if ($pbarModule) {
                $pbarModule->partitionId = (int) $post['partitionbar'];
                $pbarModule->moduleId    = (int) $params['rowId'];
                $pbarModule->insert();
            }
        }

        // роли пользователей, которым доступен модуль
        if ($post['roles']) {
            /** @var null|\Gm\Backend\UserRoles\Model\RolePermission $rolePermission */
            $rolePermission = $modules->getModel('RolePermission', 'gm.be.user_roles');
            if ($rolePermission) {
                foreach ($post['roles'] as $value => $checked) {
                    list($roleId, $permissions) = explode(':', $value);
                    $rolePermission->moduleId    = $params['rowId'];
                    $rolePermission->roleId      = $roleId;
                    $rolePermission->permissions = $permissions;
                    $rolePermission->insert();
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function afterUnmount(): void
    {
        /** @var \Gm\ModuleManager\ModuleManager Менеджер модулей */
        $modules = Gm::$app->modules;

        // Удаление модуля из всех панелей разделов
        /** @var null|\Backend\Partitionbar\Model\PartitionbarModule $pbarModule */
        $pbarModule = $modules->getModel('PartitionbarModule', 'gm.be.partitionbar');
        if ($pbarModule) {
            $pbarModule->deleteByModule($this->info['rowId']);
        }

        // Удаление модуля из ролей пользователей
        /** @var null|\Backend\Role\Model\RolePermission $rolePermission */
        $rolePermission = $modules->getModel('RolePermission', 'gm.be.user_roles');
        if ($rolePermission) {
            $rolePermission->deleteByModule($this->info['rowId']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function afterUninstall(): void
    {
        /** @var \Gm\ModuleManager\ModuleManager Менеджер модулей */
        $modules = Gm::$app->modules;

        // Удаление модуля из всех панелей разделов
        /** @var null|\Backend\Partitionbar\Model\PartitionbarModule $pbarModule */
        $pbarModule = $modules->getModel('PartitionbarModule', 'gm.be.partitionbar');
        if ($pbarModule) {
            $pbarModule->deleteByModule($this->info['rowId']);
        }

        // Удаление модуля из ролей пользователей
        /** @var null|\Backend\Role\Model\RolePermission $rolePermission */
        $rolePermission = $modules->getModel('RolePermission', 'gm.be.user_roles');
        if ($rolePermission) {
            $rolePermission->deleteByModule($this->info['rowId']);
        }
    }

    /**
     * Перевод (локализация) сообщения или сообщений.
     * 
     * Перевод сообщений выполняет модуль {@see ModuleInstaller::$module}, которому 
     * передали управление.
     * 
     * Перевод выполняется в представлении @see ModuleInstaller::getView()}.
     * 
     * @param string|array<int, string> $message Текст сообщения (сообщений).
     * @param array<int, string> $params Параметры локализация (по умолчанию `[]`).
     * @param string $locale Код локализации, например: 'ru_RU', 'en_GB'. Если значение '', 
     *     то применяется текущая локализация (по умолчанию '').
     * 
     * @return string|array<int, string> Локализованное сообщение (сообщения).
     */
    public function t($message, array $params = [], string $locale = ''): string|array
    {
        return Gm::$app->translator->translate($this->module->id, $message, $params, $locale);
    }
}
