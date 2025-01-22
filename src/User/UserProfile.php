<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\User;

use Gm\Db\Sql\Where;
use Gm\User\UserData;

/**
 * Профиль пользователя.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\User
 * @since 1.0
 */
class UserProfile extends UserData
{
    /**
     * Изображение пользователя.
     * 
     * @see UserProfile::getPicture()
     * 
     * @var UserProfilePicture
     */
    protected UserProfilePicture $_picture;

    /**
     * {@inheritdoc}
     */
    protected string $storageMember = 'profile';

    /**
     * {@inheritdoc}
     */
    public function tableName(): string
    {
        return '{{user_profile}}';
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
            'id'             => 'id', // идентификатор
            'userId'         => 'user_id', // идентификатор учтёной записи
            'firstName'      => 'first_name', // имя
            'secondName'     => 'second_name', // фимилия
            'patronymicName' => 'patronymic_name', // отчество
            'callName'       => 'call_name', // обращение
            'photo'          => 'photo', // файл фото
            'gender'         => 'gender', // пол
            'dateOfBirth'    => 'date_of_birth', // дата рождения
            'phone'          => 'phone', // телефон
            'email'          => 'email', // электронная почта
            'timeZone'       => 'timezone' // часовой пояс
        ];
    }

    /**
     * {@inheritdoc}
     * 
     * @return UserProfile|null
     */
    public function find()
    {
        return $this->selectOne(['user_id' => $this->_identity->id]);
    }

    /**
     * Выполняет поиск профиля пользователя.
     * 
     * @param Where|\Closure|string|array $where Условие выполнения запроса. Если это 
     *     тип `int`, то условие - идентификатор пользователя.
     * 
     * @return array|null Возврашает информацию о профиле пользователя в виде пар 
     *     "ключ - значение". Иначе значение `null`, если профиль не найден.
     */
    public function findOne($where): ?array
    {
        $select = $this->select(['*'], is_numeric($where) ? ['user_id' => (int) $where] : $where);
        return $this
            ->getDb()
                ->createCommand($select)
                    ->queryOne();
    }

    /**
     * Возвращает изображение пользователя.
     * 
     * @return UserProfilePicture
     */
    public function getPicture(): UserProfilePicture
    {
        if (!isset($this->_picture)) {
            $this->_picture = new UserProfilePicture($this);
        }
        return $this->_picture;
    }

    /**
     * Возвращает обращение.
     *
     * @return string
     */
    public function getCallName(): string
    {
        $value = $this->attributes['callName'] ?? '';
        if ($value) return $value;
        //if ($this->callName) return $this->callName;

        $name = [];
        if ($this->secondName)
            $name[] = $this->secondName;
        if ($this->firstName)
            $name[] = $this->firstName;
        if ($this->patronymicName)
            $name[] = $this->patronymicName;
        return implode(' ', $name);
    }
}

