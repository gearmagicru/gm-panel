<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Data\Model\Combo;

use Gm\Data\Model\BaseModel;

/**
 * Модель данных элементов выпадающего списка тегов 
 * (реализуемых представленим с использованием компонента Ext.form.field.Tag ExtJS).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Data\Model\Combo
 * @since 1.0
 */
class TagComboModel extends BaseModel
{
    /**
     * Возвращает идентификаторы тегов для текущей записи.
     *
     * @param bool $toString Если указано значение `true`, идентификаторы будут 
     *     возвращены ввиде строки через разделитель ",", например, '1,2,3,4,...'.
     *     Иначе массив `[1, 2, 3, 4,...]`.
     *
     * @return array|string
     */
    public function getTags(bool $toString = false): array|string
    {
        return '';
    }

    /**
     * Сохраняет (добавляет или удаляет) теги текущей записи после их изменения.
     * 
     * @see TagComboModel::getTags()
     * @see TagComboModel::addTags()
     * @see TagComboModel::deleteTags()
     * 
     * @param array $tags Идентификаторы тегов, которые необходимо сохранить.
     *
     * @return void
     */
    public function saveTags(array $tags): void
    {
        if (empty($tags)) return;

        $allTags = $this->getTags();
        $toAdd = array_diff($tags, $allTags);
        // если необходимо добавить
        if ($toAdd) {
            $this->addTags($toAdd);
        }
        $toDelete = array_diff($allTags, $tags);
        // если необходимо удалить
        if ($toDelete) {
            $this->deleteTags($toDelete);
        }
    }

    /**
     * Добавляет теги.
     *
     * @param array $tags Идентификаторы тегов, которые будут добавлены.
     *
     * @return void
     */
    public function addTags(array $tags): void
    {
        foreach ($tags as $id) {
            $this->insertRecord(array(
                'user_id' => $this->userId,
                'role_id' => $id
            ), '{{user_roles}}');
        }
    }

    /**
     * Удаляет теги.
     *
     * @param array $tags Идентификаторы тегов, которые будут удалены.
     *
     * @return false|int Если значение `false`, то ошибка удаления, иначе количество 
     *     удалённых тегов.
     */
    public function deleteTags(array $tags): false|int
    {
        return false;
    }
}
