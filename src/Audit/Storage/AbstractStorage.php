<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Audit\Storage;

use Gm\Stdlib\BaseObject;

/**
 * Абстрактный класс хранилища журнала аудита.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Audit\Storage
 * @since 1.0
 */
class AbstractStorage extends BaseObject
{
    /**
     * Максимально количество записей в журнале.
     *
     * @var int
     */
    public int $limit = 1000;

    /**
     * Возвращает маску атрибутов.
     * 
     * Маска необходима для безопасного формирования атрибутов с их значениями, те
     * атрибуты которые не прошли через маску, являются "небезопасными".
     * 
     * Пример установки маски:
     * ```php
     * return [
     *     'mask'  => 'field',
     *     'mask1' => 'field1',
     *     // ...
     * ]
     * ```
     * 
     * @return array<string, string>
     */
    public function maskedAttributes(): array
    {
        return [];
    }

    /**
     * Уберает маску из указанных атрибутов.
     * 
     * Если имена атрибутов не находятся в маске, они не будут возвращены.
     * 
     * Пример установки атрибутов: `['attribute' => 'value', 'attribute1' => 'value1'...]`.
     * 
     * @param array<string, string> $attributes Атрибуты в виде пары `имя => значение`.
     * 
     * @return array<string, mixed>
     */
    public function unmaskedAttributes(array $attributes): array
    {
        $mask = $this->maskedAttributes();
        if ($mask) {
            $result = [];
            foreach($attributes as $key => $value) {
                if (isset($mask[$key]))
                    $result[$mask[$key]] = $value;
            }
            return $result;
        }
        return $attributes;
    }

    /**
     * Проверка, достигнут ли лимит записей в журнале аудита.
     * 
     * @return bool Если значение `true`, достигнут лимит записей в журнале аудита.
     */
    public function hasLimitRows(): bool
    {
        return false;
    }

    /**
     * Удаление всех записей из журнала аудита.
     * 
     * @return void
     */
    public function clear(): void
    {
    }

    /**
     * Запись в журнал аудита.
     * 
     * @param array<string, string> $attributes Атрибуты в виде пары `имя => значение`.
     * 
     * @return void
     */
    public function write(array $attributes = []): void
    {
    }
}
