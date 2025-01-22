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
use Gm\Helper\Json;
use Gm\User\UserStorage;

/**
 * Настройки пользователя - это настройки ряда модулей панели управления GM Panel в 
 * формате JSON.
 * 
 * Имя свойства {@see UserSettings::$storageName} модели пользователя {@see UserIdentity}, 
 * которое определяет настройки пользователя.
 * 
 * Настройки имеют вид:
 * ```json
 * {
 *     "идентификатор модуля 1": {"параметр": "значение",...},
 *     "идентификатор модуля 2": {"параметр": "значение",...},
 *     ...
 * }
 * ```
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\User
 * @since 1.0
 */
class UserSettings extends UserStorage
{
    /**
     * {@inheritdoc}
     */
    protected string $storageMember = 'settings';

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $container = $this->_identity->{$this->storageMember};
        if ($container) {
            $this->container = is_string($container) ? $this->decode($container) : $container;
        }
        return $this->container;
    }

    /**
     *  Возвращает строковое представление текущего объекта.
     * 
     * @see UserSettings::toString()
     * 
     * @return string Строковое (string) представление текущего объекта.
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Преобразует настройки в строковый формат JSON.
     * 
     * @see UserSettings::encode()
     * 
     * @return string Если ошибка в кодировке, то пустая строка.
     */
    public function toString(): string
    {
        return $this->encode($this->container);
    }

    /**
     * Обновляет настройки пользователя.
     * 
     * @param array<string, mixed> $settings Настройки пользователя, которые заменят текущие.
     * 
     * @return UserSettings
     */
    public function update(array $settings = []): static
    {
        if ($settings) {
            $this->merge($settings);
        }
        /** @var \Gm\Db\Adapter\Driver\AbstractCommand $command */
        $command = Gm::$app->db->createCommand();
        $command->update(
            $this->_identity->tableName(),
            [
                $this->storageMember => $this->toString()
            ],
            [
                'id' => $this->_identity->id
            ]
        );
        $command->execute();
        return $this;
    }

    /**
     * Возвращает настройки в JSON-представлении.
     * 
     * @param array<string, mixed> $settings Настройки.
     * 
     * @return string JSON-представление.
     */
    protected function encode(array $settings): string
    {
        $json  = Json::encode($settings);
        $error = Json::error();
        if ($error) {
            //throw new Exception\JsonFormatException(Gm::t('app', 'Could not JSON decode: {0}', [$error]));
            // TODO: 
            $json = '';
        }
        return $json;
    }

    /**
     * Преобразует JSON-представления в массив настроек.
     * 
     * @param string $settings строка в JSON формате.
     * 
     * @return array
     */
    protected function decode(string $settings): array
    {
        $result = Json::tryDecode($settings, true);
        if ($result === false) {
            Gm::error(Json::error());
            return [];
        }
        return $result;
    }
}
