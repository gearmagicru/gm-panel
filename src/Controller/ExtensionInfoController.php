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
use Gm\Panel\Widget\TabExtensionInfo;
use Gm\Panel\Controller\BaseController;

/**
 * Контроллер вывода информации о расширении модуля.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Controller
 * @since 1.0
 */
class ExtensionInfoController extends BaseController
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
                return Gm::t(BACKEND, '{info extension action}');

            default:
                return parent::translateAction($params, $default);
        }
    }

    /**
     * Возвращает информацию о расширении модуля.
     * 
     * @return array
     * 
     * @throws Exception\NotDefinedException Ошибка определения модели данных.
     */
    protected function getExtensionInfo(): array
    {
        // идентификатор модуля
        $id = $this->module->id;
        if ($id === null) {
            throw new Exception\NotDefinedException(Gm::t('app', 'Could not define extension id'));
        }

        /** @var \Gm\ExtensionManager\ExtensionManager extensions Менеджер расширений */
        $extensions = Gm::$app->extensions;
        /** @var \Gm\ExtensionManager\ExtensionRegistry $registry Реестр расширений */
        $registry = $extensions->getRegistry();

        /** @var array|null $extensionInfo Информация о модуле */
        $extensionInfo = $registry->getInfo($id, true);
        if ($extensionInfo === null) {
            throw new Exception\NotDefinedException(Gm::t('app', 'Could not define extension information'));
        }
        // расширение для которого формируется информация
        $extensionInfo['extension'] = $this->module;

        /* Раздел "Заголовок" */
        $name = $this->module->t('{name}');
        // если указано имя в локализации
        if ($name !== '{name}') {
            $extensionInfo['name'] = $name;
        }
        $description = $this->module->t('{description}');
        // если указано описание в локализации
        if ($description !== '{description}') {
            $extensionInfo['description'] = $description;
        }

        /* Раздел "Расширение установлено" */
        // дата установки расширения
        $extensionInfo['createdDate'] = null;
        // пользователь устанавивший расширение
        $extensionInfo['createdUser'] = null;
        // расширение из базы данных
        $extension = $extensions->selectOne($id, true);
        //Gm::dump($extension);
        if ($extension) {
            if ($extension['createdDate']) {
                $extensionInfo['createdDate'] = Gm::$app->formatter->toDateTime($extension['createdDate']);
            }
            if ($extension['createdUser']) {
                $userId = (int) $extension['createdUser'];
                /** @var \Gm\Panel\User\UserIdentity $user */
                $user = Gm::userIdentity();
                /** @var \Gm\Panel\User\UserProfile $profile */
                $profile = Gm::userIdentity()->getProfile();
                // переопределяем
                $extensionInfo['createdUser'] = [
                    'user'    => $user->findOne(['id' => $userId ]),
                    'profile' => $profile->findOne(['user_id' => $userId])
                ];
            }
        }

        /* Раздел "Права доступа" */
        if ($permissions = $extensionInfo['permissions']) {
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
            $extensionInfo['permissions'] = [];
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
                $extensionInfo['permissions'][$permission] = [$name, $description];
            }
        }

        /* Роли пользователей */
        $extensionInfo['roles'] = [];
        /** @var \Gm\Backend\UserRoles\Model\ExtensionPermission $modExtensionPermission  */
        $modExtensionPermission = Gm::$app->modules->getModel('ExtensionPermission', 'gm.be.user_roles');
        if ($modExtensionPermission) {
            /** 
             * @var array $extensionsPermissions Права доступа ролей для указанного расширения.
             * Имеет вид: `({roleId} => ['any', 'read', ...], ...)`.
             **/
            $extensionsPermissions = $modExtensionPermission->getExtensionsPermissions($extensionInfo['rowId']);
            if ($extensionsPermissions) {
                // идентификаторы ролей пользователей для которых доступен модуль (1, 2, 3, ...)
                $rolesId = array_keys($extensionsPermissions);
                /** @var \Gm\Backend\UserRoles\Model\Role $modRole  */
                $modRole = Gm::$app->modules->getModel('Role', 'gm.be.user_roles');
                /** @var array $roles Имена ролей пользователей ({roleId} => ['name' => 'Role', 'id' => 1], ...) */
                $roles = $modRole->fetchAll('id', ['id', 'name'], ['id' => $rolesId]);
                if ($roles) {
                    foreach ($extensionsPermissions as $roleId => $permissions) {
                        $extensionInfo['roles'][] = [
                            'role'        => isset($roles[$roleId]) ? $roles[$roleId]['name'] : SYMBOL_NONAME,
                            'permissions' => $permissions
                        ];
                    }
                }
            }
            
        }
        return $extensionInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function createWidget(): TabExtensionInfo
    {
        /** @var TabExtensionInfo $tab */
        $tab = new TabExtensionInfo();

        // панель вкладки компонента (Gm.view.tab.Components GmJS)
        $tab->setTitle(Gm::t(BACKEND, 'Extension information {0}', [$this->module->t('{name}')]));

        // панель (Ext.panel Sencha ExtJS)
        $tab->panel->html = $this->getViewManager()->renderPartial('info', $this->getExtensionInfo());
        return $tab;
    }

    /**
     * Действие "view" выводит интерфейс расширения модуля.
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

        /** @var TabExtensionInfo|false $widget */
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
