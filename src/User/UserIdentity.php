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
use Gm\Db\Sql\Where;
use Gm\User\User;
use Gm\User\UserIdentity as Identity;

/**
 * Класс предоставляющий информацию о идентификации пользователя для Gm\Panel.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\User
 * @since 1.0
 */
class UserIdentity extends Identity
{
    /**
     * {@inheritdoc}
     */
    public array $excludeSetters = [
        'side' => true
    ];

    /**
     * {@inheritdoc}
     */
    public array $excludeGetters = [
        'id'       => true,
        'username' => true,
        'side'     => true,
        'settings' => true
    ];

    /**
     * Профиль пользователя.
     * 
     * @see UserIdentity::getProfile()
     * 
     * @var UserProfile|null
     */
    protected UserProfile|null $_profile;

    /**
     * Устройство пользователя.
     * 
     * @see UserIdentity::getDevice()
     * 
     * @var UserDevice
     */
    protected UserDevice $_device;

    /**
     * Настройки пользователя.
     * 
     * @see UserIdentity::getSettings()
     * 
     * @var UserSettings
     */
    protected UserSettings $_settings;

    /**
     * Роли пользователя.
     * 
     * @var UserRoles|null
     */
    protected $_roles;

    /**
     * {@inheritdoc}
     */
    public function tableName(): string
    {
        return '{{user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function primaryKey(): string
    {
        return 'id';
    }

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            'id'              => 'id', // идентификатор
            'username'        => 'username', // имя пользователя
            'password'        => 'password', // пароль
            'visitedDate'     => 'visited_date', // дата и время авторизации
            'visitedTrial'    => 'visited_trial', // количество попыток авторизации
            'visitedDisabled' => 'visited_disabled', // дата и время блокировки учетной записи
            'visitedDevice'   => 'visited_device', // имя устройства с которого была авторизация 
            'status'          => 'status', // статус учетной записи
            'process'         => 'process', // дата и время проверки учетной записи
            'settings'        => 'settings', // настройки пользователя
            'side'            => 'side', // принадлежность пользователя: backend (0), frontend (1)
            'lock'            => '_lock' // учетная запись системная
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function writableAttributes(): array
    {
        return [
            'id',
            'username',
            'visitedDate',
            'visitedTrial',
            'visitedDisabled',
            'visitedDevice',
            'status',
            'process',
            'settings',
            'side',
            'lock',
            'loginSide'
        ];
    }

    /**
     * Возвращает профиль пользователя.
     * 
     * @see UserProfile::find()
     * 
     * @return UserProfile|null
     */
    public function getProfile(): ?UserProfile
    {
        if (!isset($this->_profile)) {
            if ($this->_storage)
                $this->_profile = new UserProfile($this);
            else
                $this->_profile = (new UserProfile($this))->find();
        }
        return $this->_profile;
    }

    /**
     * Выполняет поиск профиля пользователя.
     * 
     * @see UserProfile::findOne()
     * 
     * @param Where|\Closure|string|array $where Условие выполнения запроса. Если это 
     *     тип `int`, то условие - идентификатор пользователя.
     * 
     * @return array|null Возврашает информацию о профиле пользователя в виде пар 
     *     "ключ - значение". Иначе значение `null`, если профиль не найден.
     */
    public function findProfile($where): ?array
    {
        return $this->getProfile()->findOne($where);
    }

    /**
     * Возвращает созданный экземпляр класса профиля пользователя.
     * 
     * @return UserProfile Профиль пользователя.
     */
    public function createProfile(): UserProfile
    {
        return new UserProfile($this);
    }

    /**
     * Возвращает устройство пользователя.
     * 
     * @return UserDevice
     */
    public function getDevice(): UserDevice
    {
        if (!isset($this->_device)) {
            $this->_device = new UserDevice($this);
        }
        return $this->_device;
    }

    /**
     * Возвращает настройки пользователя.
     * 
     * @return UserSettings
     */
    public function getSettings(): UserSettings
    {
        if (!isset($this->_settings)) {
            $this->_settings = new UserSettings($this);
        }
        return $this->_settings;
    }

    /**
     * Возвращает настройки пользователя для указанного модуля.
     * 
     * @see UserSettings::get()
     * 
     * @param string $moduleId Идентификатор модуля, например 'gm.be.users'.
     * 
     * @return array<string, mixed>|null Если значение `null`, то настройки для 
     *     указанного модуля отсутствуют, иначе настройки в виде пар "ключ - значение".
     */
    public function findSetting(string $moduleId): ?array
    {
        return $this->getSettings()->get($moduleId);
    }

    /**
     * {@inheritdoc}
     */
    public function write()
    {
        parent::write();

        // запись в хранилище профиля пользователя
        $this->getProfile()->write();
        // запись в хранилище устройства пользователя
        $this->getDevice()->write();
        // запись в хранилище ролей пользователя
        $this->getRoles()->write();
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername(): string
    {
        return $this->username ?: '';
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return $this->status === User::STATUS_ACTIVE;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(string $permission, bool $extension = false): bool
    {
        return $this->getBac()->isGranted($permission, $extension);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissions(bool $extension = false): array
    {
        return $this->getBac()->getPermissions($extension);
    }

    /**
     * {@inheritdoc}
     */
    public function getModules(bool $toArray = false, string $permission = null): string|array
    {
        return $this->getBac()->getModules($toArray, $permission);
    }

    /**
     * {@inheritdoc}
     */
    public function getViewableModules(bool $toArray = false): string|array
    {
        return $this->getBac()->getViewableModules($toArray);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(bool $toArray = false): string|array
    {
        return $this->getBac()->getExtensions($toArray);
    }

    /**
     * Возвращает роли пользователя.
     * 
     * @return UserRoles
     */
    public function getRoles()
    {
        if ($this->_roles === null) {
            $this->_roles = new UserRoles($this);
        }
        return $this->_roles;
    }

    /**
     * Выполняет поиск ролей пользователя.
     * 
     * @param null|int $userId Идентификатор пользователя. Если значение `null`, 
     *     то идентификатор авторизованного пользователя.
     * 
     * @return array<int, array> Роли доступные пользователю в виде пар "идентификатор - роль". 
     */
    public function findRoles(?int $userId = null): array
    {
        return $this->getRoles()->find($userId);
    }
}
