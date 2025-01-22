<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Controller;

use Gm;
use Gm\Panel\Http\Response;
use Gm\Mvc\Controller\Exception;
use Gm\Panel\Widget\TabModuleInfo;
use Gm\Panel\Controller\BaseController;

/**
 * Контроллер вывода информации о модуле.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Controller
 * @since 1.0
 */
class ModuleInfoController extends BaseController
{
    /**
     * Вызывать события приложения при обращении к действиям контроллера.
     *
     * @var bool
     */
    public bool $useAppEvents = false;

    /**
     * {@inheritdoc}
     */
    protected string $defaultAction = 'view';

    /**
     * {@inheritdoc}
     */
    public function translateAction(mixed $params, string $default = null): ?string
    {
        switch ($this->actionName) {
            // вывод интерфейса
            case 'view':
                return Gm::t(BACKEND, '{info module action}');

            default:
                return parent::translateAction($params, $default);
        }
    }

    /**
     * Возвращает информацию о модуле.
     * 
     * @return array
     * 
     * @throws Exception\NotDefinedException Ошибка определения модели данных.
     */
    protected function getModuleInfo(): array
    {
        // идентификатор модуля
        $id = $this->module->id;
        if ($id === null) {
            throw new Exception\NotDefinedException(Gm::t('app', 'Could not define module id'));
        }

        /** @var \Gm\ModuleManager\ModuleManager $modules Менеджер модулей */
        $modules = Gm::$app->modules;
        /** @var \Gm\ModuleManager\ModuleRegistry $registry Установленные модули */
        $registry = $modules->getRegistry();

        /** @var array|null $moduleInfo Информация о модуле */
        $moduleInfo = $registry->getInfo($id, true);
        if ($moduleInfo === null) {
            throw new Exception\NotDefinedException(Gm::t('app', 'Could not define module information'));
        }
        // модуль для которого формируется информация
        $moduleInfo['module'] = $this->module;

        /* Раздел "Заголовок" */
        $name = $this->module->t('{name}');
        // если указано имя в локализации
        if ($name !== '{name}') {
            $moduleInfo['name'] = $name;
        }
        $description = $this->module->t('{description}');
        // если указано описание в локализации
        if ($description !== '{description}') {
            $moduleInfo['description'] = $description;
        }

        /* Раздел "Модуль установлен" */
        // дата установки модуля
        $moduleInfo['createdDate'] = null;
        // пользователь устанавливавший модуль
        $moduleInfo['createdUser'] = null;
        // модуль из базы данных
        $module = $modules->selectOne($id, true);
        if ($module) {
            if ($module['createdDate']) {
                $moduleInfo['createdDate'] = Gm::$app->formatter->toDateTime($module['createdDate']);
            }
            if ($module['createdUser']) {
                $userId = (int) $module['createdUser'];
                /** @var \Gm\Panel\User\UserIdentity $user */
                $user = Gm::userIdentity();
                /** @var \Gm\Panel\User\UserProfile $profile */
                $profile = Gm::userIdentity()->getProfile();
                // переопределяем
                $moduleInfo['createdUser'] = [
                    'user'    => $user->findOne(['id' => $userId ]),
                    'profile' => $profile->findOne(['user_id' => $userId])
                ];
            }
        }

        /* Раздел "Права доступа" */
        if ($permissions = $moduleInfo['permissions']) {
            $permissions = explode(',', $permissions);
            $transPermissions = $this->module->t('{permissions}');
            // если не указаны разрешения в локализации
            if ($transPermissions === '{permissions}') {
                $transPermissions = [];
            }
            // особые разрешения (info, settings, recordRls, writeAudit, viewAudit) менеджера данных для текущей локализации
            $specPermissions  = Gm::t('backend', '{dataManagerPermissions}');
            $transPermissions = $transPermissions ? array_merge($transPermissions, $specPermissions) : $specPermissions;
            // разрешения модуля
            $moduleInfo['permissions'] = [];
            foreach ($permissions as $permission) {
                if ($transPermissions) {
                    // если в локализации не забыли указать разрешение
                    if (isset($transPermissions[$permission])) {
                        // имя разрешения и описание
                        $names = $transPermissions[$permission];
                        $name = $names[0];
                        $description = $names[1] ?? '';
                    // если в локализации забыли указать разрешение
                    } else {
                        $name = ucfirst($permission);
                        $description = '';
                    }
                } else {
                    $name = ucfirst($permission);
                    $description = '';
                }
                $moduleInfo['permissions'][$permission] = [$name, $description];
            }
        }

        /* Роли пользователей */
        $moduleInfo['roles'] = [];
        /** @var \Gm\Backend\UserRoles\Model\RolePermission $modRolePermission  */
        $modRolePermission = Gm::$app->modules->getModel('RolePermission', 'gm.be.user_roles');
        if ($modRolePermission) {
            /** @var array $modulesPermissions Права доступа ролей для указанного модуля ({roleId} => ['any', 'read', ...], ...) */
            $modulesPermissions = $modRolePermission->getModulesPermissions($moduleInfo['rowId']);
            if ($modulesPermissions) {
                // идентификаторы ролей пользователей для которых доступен модуль (1, 2, 3, ...)
                $rolesId = array_keys($modulesPermissions);
                /** @var \Gm\Backend\UserRoles\Model\Role $modRole  */
                $modRole = Gm::$app->modules->getModel('Role', 'gm.be.user_roles');
                /** @var array $roles Имена ролей пользователей ({roleId} => ['name' => 'Role', 'id' => 1], ...) */
                $roles = $modRole->fetchAll('id', ['id', 'name'], ['id' => $rolesId]);
                if ($roles) {
                    foreach ($modulesPermissions as $roleId => $permissions) {
                        $moduleInfo['roles'][] = [
                            'role'        => isset($roles[$roleId]) ? $roles[$roleId]['name'] : SYMBOL_NONAME,
                            'permissions' => $permissions
                        ];
                    }
                }
            }
            
        }
        return $moduleInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function createWidget(): TabModuleInfo
    {
        /** @var TabModuleInfo $tab */
        $tab = new TabModuleInfo();

        // панель вкладки компонента (Gm.view.tab.Components GmJS)
        $tab->setTitle(Gm::t(BACKEND, 'Module information {0}', [$this->module->t('{name}')]));

        // панель (Ext.panel Sencha ExtJS)
        $tab->panel->html = $this->getViewManager()->renderPartial('info', $this->getModuleInfo());
        return $tab;
    }

    /**
     * Действие "view" выводит информацию о модуле.
     * 
     * @return Response
     */
    public function viewAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        // т.к. "frontend" может использовать контроллер, то подключаем
        if (!Gm::$app->translator->categoryExists(BACKEND)) {
            Gm::$app->translator
                ->addCategory(BACKEND)
                    ->addLocalePattern(BACKEND);
        }

        /** @var TabModuleInfo|false $widget */
        $widget = $this->getWidget();
        // если была ошибка при формировании виджета
        if ($widget === false) {
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName(), [$this, $widget]);
        }

        $response
            ->setContent($widget->run())
            ->meta
                ->addWidget($widget);
        return $response;
    }
}