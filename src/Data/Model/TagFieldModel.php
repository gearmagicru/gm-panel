<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Data\Model;

use Gm\Data\Model\BaseModel;

/**
 * Модель данных поля тегов (реализуемых представленим с использованием компонента 
 * Ext.form.field.Tag ExtJS).
 * 
 * Модель данных реализует связь "многие-ко-многим" и предполагает возможность связи 
 * одного или нескольких элементов из одной таблицы с одним или несколькими элементами 
 * из другой таблицы с вожностью выбора с выпадающего списка.
 * 
 * Пример: В таблице "Теги" указаны теги, где каждый тег может соответстовать нескольким
 * статьям.
 * 
 * Схема связи:
 * Таблица тегов (tagTableName)    Таблица "многие-ко-многим" (junctionTableName) Таблица использующая теги
 * Первичный ключ tagPrimaryKey => Внешний ключ тегов tagForeignKey
 * Название тега  tagNameKey       Внешний ключ       foreignKey               <= $foreignValue
 * 
 * Таблица использующая теги не указывается. Указывается, только значение первичного 
 * ключа {@see TagFieldModel::$foreignValue} для связи с внешним ключем таблицы "многие-ко-многим".
 * Пример:
 * Таблица "Теги"    Таблица "многие-ко-многим"    Таблица "Статьи"
 * id             =>        tag_id
 * name                     article_id           <=    id
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Data\Model
 * @since 1.0
 */
class TagFieldModel extends BaseModel
{
    /**
     * Значение первичного ключа таблицы использующей теги.
     * 
     * Например, идентификатор статьи.
     *
     * @var null|int
     */
    public $foreignValue;

    /**
     * Имя внешнего ключа таблицы "многие-ко-многим" (junctionTableName) для 
     * связи с первичным ключем таблицы использующей теги.
     * 
     * Например, если имя таблицы использующей теги "articles", а имя ёё первичного
     * ключа "id", то имя внешеного ключа будет "article_id".
     * 
     * @return string
     */
    public function foreignKey(): string
    {
        return '';
    }

    /**
     * Имя таблицы тегов.
     * 
     * @return string
     */
    public function tagTableName(): string
    {
        return '';
    }

    /**
     * Имя первичного ключа таблицы тегов.
     * 
     * @return string По умолчанию возвращает 'id'.
     */
    public function tagPrimaryKey(): string
    {
        return 'id';
    }

    /**
     * Имя поля, определяющие название тега в таблице тегов.
     * 
     * @return string По умолчанию возвращает 'name'.
     */
    public function tagNameKey(): string
    {
        return 'name';
    }

    /**
     * Имя внешнего ключа таблицы "многие-ко-многим" (junctionTableName) для 
     * связи с первичным ключем таблицы тегов.
     * 
     * Например, если имя таблицы тегов "tags", а имя ёё первичного
     * ключа "id", то имя внешеного ключа будет "tag_id".
     * 
     * @return string
     */
    public function tagForeignKey(): string
    {
        return 'role_id';
    }

    /**
     * Имя таблицы "многие-ко-многим".
     * 
     * Например, если название таблицы тегов "tags", а имя таблицы использующей 
     * теги "articles", то название будет - "article_tags".
     * 
     * @return string
     */
    public function junctionTableName(): string
    {
        return '';
    }

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
        /** @var \Gm\Db\Adapter\Adapter $db */
        $db = $this->getDb();

        /** @var \Gm\Db\Sql\Select $select */
        $select = $db
            ->select($this->junctionTableName())
            ->columns([$this->tagForeignKey()])
            ->where([$this->foreignKey() => $this->foreignValue]);

        /** @var \Gm\Db\Adapter\Driver\AbstractCommand $command */
        $command = $db->createCommand($select);

        /** @var array $rows */
        $rows = $command->queryColumn();
        return $toString ? implode(',', $rows) : $rows;
    }

    /**
     * Возвращает все теги из таблицы тегов.
     *
     * @return array<int, array>
     */
    public function getAllTags(): array
    {
        /** @var \Gm\Db\Adapter\Adapter $db */
        $db = $this->getDb();

        /** @var \Gm\Db\Sql\Select $select */
        $select = $db
            ->select($this->tagTableName())
            ->columns([$this->tagPrimaryKey(), $this->tagNameKey()]);

        /** @var \Gm\Db\Adapter\Driver\AbstractCommand $command */
        $command = $db->createCommand($select);
        return $command->queryTo();
    }

    /**
     * Сохраняет (добавляет или удаляет) теги текущей записи после их изменения.
     * 
     * @see TagFieldModel::getTags()
     * @see TagFieldModel::addTags()
     * @see TagFieldModel::deleteTags()
     * 
     * @param array<int, int> $tags Идентификаторы тегов, которые необходимо сохранить.
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
     * @param array<int, int> $tags Идентификаторы тегов, которые будут добавлены.
     *
     * @return void
     */
    public function addTags(array $tags): void
    {
        $foreignKey    = $this->foreignKey();
        $tagForeignKey = $this->tagForeignKey();
        $tableName     = $this->junctionTableName();
        foreach ($tags as $id) {
            $this->insertRecord([
                $foreignKey    => $this->foreignValue,
                $tagForeignKey => $id
            ], $tableName);
        }
    }

    /**
     * Удаляет теги.
     *
     * @param array<int, int> $tags Идентификаторы тегов, которые будут удалены.
     *
     * @return false|int
     */
    public function deleteTags(array $tags = []): false|int
    {
        if ($tags) {
            $where = [
                $this->foreignKey()    => $this->foreignValue,
                $this->tagForeignKey() => $tags
            ];
        } else {
            $where = [
                $this->foreignKey() => $this->foreignValue
            ];
        }
        return $this->deleteRecord($where, $this->junctionTableName());
    }
}
