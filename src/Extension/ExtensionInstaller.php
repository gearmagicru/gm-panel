<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Extension;

use Gm;
use Gm\Mvc\Module\BaseModule;

/**
 * Установщик расширения модуля.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Extension\Installer
 * @since 1.0
 */
class ExtensionInstaller extends \Gm\ExtensionManager\ExtensionInstaller
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
            /** @var null|\Backend\Partitionbar\Model\PartitionbarExtensione $pbarExtension */
            $pbarExtension = $modules->getModel('PartitionbarExtension', 'gm.be.partitionbar');
            if ($pbarExtension) {
                $pbarExtension->partitionId = (int) $post['partitionbar'];
                $pbarExtension->extensionId = (int) $params['rowId'];
                $pbarExtension->insert();
            }
        }

        // роли пользователей, которым доступно расширение
        if ($post['roles']) {
            /** @var null|\Backend\Role\Model\ExtensionPermission $extPermission */
            $extPermission = $modules->getModel('ExtensionPermission', 'gm.be.user_roles');
            if ($extPermission) {
                foreach ($post['roles'] as $value => $checked) {
                    list($roleId, $permissions) = explode(':', $value);
                    $extPermission->extensionId = $params['rowId'];
                    $extPermission->roleId      = $roleId;
                    $extPermission->permissions = $permissions;
                    $extPermission->insert();
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
        /** @var null|\Backend\Partitionbar\Model\PartitionbarExtension $pbarExtension */
        $pbarExtension = $modules->getModel('PartitionbarExtension', 'gm.be.partitionbar');
        if ($pbarExtension) {
            $pbarExtension->deleteByExtension($this->info['rowId']);
        }

        // Удаление расширения из ролей пользователей
        /** @var null|\Backend\Role\Model\ExtensionPermission $extPermission */
        $extPermission = $modules->getModel('ExtensionPermission', 'gm.be.user_roles');
        if ($extPermission) {
            $extPermission->deleteByExtension($this->info['rowId']);
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
        /** @var null|\Backend\Partitionbar\Model\PartitionbarExtension $pbarExtension */
        $pbarExtension = $modules->getModel('PartitionbarExtension', 'gm.be.partitionbar');
        if ($pbarExtension) {
            $pbarExtension->deleteByExtension($this->info['rowId']);
        }

        // Удаление расширения из ролей пользователей
        /** @var null|\Backend\Role\Model\ExtensionPermission $extPermission */
        $extPermission = $modules->getModel('ExtensionPermission', 'gm.be.user_roles');
        if ($extPermission) {
            $extPermission->deleteByExtension($this->info['rowId']);
        }
    }

    /**
     * Перевод (локализация) сообщения или сообщений.
     * 
     * Перевод сообщений выполняет модуль {@see ExtensionInstaller::$module}, которому 
     * передали управление.
     * 
     * Перевод выполняется в представлении @see ExtensionInstaller::getView()}.
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
