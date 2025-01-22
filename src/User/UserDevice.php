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
use Gm\Helper\Browser;

/**
 * Устройство пользователя.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\User
 * @since 1.0
 */
class UserDevice extends UserStorage
{
    /**
     * {@inheritdoc}
     */
    protected string $storageMember = 'device';

    /**
     * Возвращает устройство пользователя
     */
    public function find(bool $toArray = false)
    {
        $device = $this->_identity->visitedDevice ?? null;
        if ($device) {
            if ($toArray) {
                return explode('|', $device);
            } else
                return $device;
        } else {
            return $toArray ? [] : '';
        }
    }

    /**
     * Определяет устройство с которого была последняя авторизация пользователя.
     * 
     * @param bool $toArray Если значение `true`, то результат в виде массива, иначе 
     *     строка.
     * @param bool $inDetail Если значение `true`, детальная информация о устройстве.
     * @param bool string $separator Разделитель параметров устройства, если результат 
     *     возвращается в виде строки (по умолчанию '|').
     * 
     * @return array|string
     */
    public function define(bool $toArray = false, bool $inDetail = false, string $separator = '|'): array|string
    {
        $device = [];
        // IP-адрес
        $device[] = Gm::$app->request->getUserIp() ?: '';
        // ОС
        $device[] = ($inDetail ? Browser::platformName() : Browser::platformFamily()) ?: '';
        // браузер
        $device[] = ($inDetail ?  Browser::browserName() : Browser::browserFamily()) ?: '';
        return $toArray ? $device : implode($separator, $device);
    }

    /**
     * Установка устройства с которого была авторизация пользователя.
     * 
     * @param string $value Устройство.
     * 
     * @return $this
     */
    public function update(string $value)
    {
        $this->_identity->visitedDevice = $value;
        $this->_identity->update();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function write()
    {
        $this->browserName   = Browser::browserName();
        $this->browserFamily = Browser::browserFamily();
        $this->osName        = Browser::platformName();
        $this->osFamily      = Browser::platformFamily();

        parent::write();
    }
}
