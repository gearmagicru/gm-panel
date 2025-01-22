<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Data\Model;

use Gm;
use Gm\Exception;
use Gm\Helper\Json;
use Gm\Data\Model\BaseModel;

/**
 * Модель контактной информации объекта.
 * 
 * Под объектом понимают таблицу записей (персон), где каждая "персона" имеет 
 * множество контактной информации (записей) в таблице {@see ContactsModel::$tableName}.
 * 
 * Каждый объект может иметь поле для хранения своей контактной информации из таблицы 
 * {@see ContactsModel::$tableName} в формате JSON.
 * 
 * Модель выполняет действия над записями контактной информации объекта и делает 
 * преобразование значений полей формы в соответствующий JSON формат.
 * 
 * Для преобразования записей контактной информации полученных из значения одного из 
 * полей формы, используется метод {@see ContactsModel::fieldValueToFormat()}.
 * 
 * Для преобразования значения контактной информации полученного из поля объекта в 
 * список записей компонента {@see \Gm\Panel\View\ContactsGridView}, используется 
 * метод {@see ContactsModel::rowsToFieldValue()}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Data\Model
 * @since 1.0
 */
class ContactsModel extends BaseModel
{
    /**
     * Имя поля для связи с таблицей классификаторов контактной информации.
     * 
     * @var string|null
     */
    public $classifierKey = 'classifier';

    /**
     * Имя поля для хранения контактной информации.
     * 
     * @var string
     */
    public $contactKey = 'contact';

    /**
     * Идентификатор классификатора группы контактной информации.
     * 
     * Классификатор контактной информации разбивается на группы (справочники).
     * 
     * @var int|null
     */
    public $classifierGroup;

    /**
     * $patternName
     * 
     * @var string
     */
    public $patternName = 'contactsClassifier';

    /**
     * getQueryPattern
     * 
     * @return array
     */
    public function getQueryPattern(): array
    {
        return [
            'query' => 
                'SELECT `types`.*, `contacts`.`name`, `contacts`.`id`, `contacts`.`parent_id` '
              . 'FROM {{classifier_contacts}} `contacts` '
              . 'JOIN {{classifier_contact_types}} `types` ON `types`.`id`=`contacts`.`type_id` AND `contacts`.`enabled`=1 '
              . 'ORDER BY `contacts`.`index` ASC',
            'primaryKey' => 'id',
            'groupBy'    => 'parent_id',
            'hashing'    => true,
            'expiry'     => 0
        ];
    }

    /**
     * Возвращает классификатор контакной информации по указанной группе.
     * 
     * Если служба кэширования {@see \Gm\Cache\Cache} или служба кэш-таблиц 
     * {@see \Gm\Cache\CacheTable} отключены, то будет выполнен запрос к базе 
     * данных через службу кэш-таблиц.
     * 
     * @see \Gm\Cache\CacheTable
     * 
     * @param int|string $groupId Идентификатор группы контакной информации.
     * 
     * @return array
     * ```php
     * return [
     *     '{id}' => [
     *         'id'            => '{string}',
     *         'name'          => '{string}',
     *         'type'          => '{string}',
     *         'xtype'         => '{string}',
     *         'handler'       => '{string}',
     *         'uri'           => '{string}',
     *         'configuration' => '{string|NULL}',
     *         'parent_id'     => '{string}'
     *     ],
     *     // ...
     * ];
     * ```
     */
    public function getClassifier($classifierGroup = null): array
    {
        if ($classifierGroup === null) {
            $classifierGroup = $this->classifierGroup;
        }
        /** @var \Gm\Cache\CacheTable $table */
        $table = Gm::$app->tables;
        // если есть шаблон запроса для кэш-таблицы "классификатор контактов"
        if (!$table->pattern($this->patternName)) {
            $table
                ->setPattern($this->patternName, $this->getQueryPattern())
                ->pattern($this->patternName);
        }
        $row = $table->getRow($classifierGroup); // если !enabled, то будет fetchRow()
        return $row ?: [];
    }

    /**
     * Возвращает все группы классификатора контакной информации.
     * 
     * Если служба кэширования {@see \Gm\Cache\Cache} или служба кэш-таблиц 
     * {@see \Gm\Cache\CacheTable} отключены, то будет выполнен запрос к базе 
     * данных через службу кэш-таблиц.
     * 
     * @see \Gm\Cache\CacheTable
     * 
     * @return null|array
     */
    public function getAllClassifier(): ?array
    {
        /** @var \Gm\Cache\CacheTable $table */
        $table = Gm::$app->tables;
        // если есть шаблон запроса для кэш-таблицы "классификатор контактов"
        if (!$table->pattern($this->patternName)) {
            $table
                ->setPattern($this->patternName, $this->getQueryPattern())
                ->pattern($this->patternName);
        }
        return $table->getAll(); // если !enabled, то будет fetchAll()
    }

    /**
     * Преобразует записи контактной информации в формат данных компонента ContactsGridView.
     * 
     * @see \Gm\Panel\View\ContactsGridView
     * @see ContactsModel::getClassifier()
     * 
     * @param array $arr Массив контакной информации.
     * @param string|int|null $classifierGroup Идентификатор классификатора группы 
     *     контактной информации. Если значение `null`, то будет значение 
     *     подставлено из {@see ContactsModel::$classifierGroup}.
     * 
     * @return string|null
     * 
     * @throws Exception\FormatException
     */
    public function arrayToFieldValue($arr, $classifierGroup = null): ?string
    {
        if ($classifierGroup === null) {
            $classifierGroup = $this->classifierGroup;
        }

        // контактная информация
        $contacts = $arr;
        // классификатор контактной информации
        $classifier = $this->getClassifier($classifierGroup);
        $clsKey = $this->classifierKey;
        $cntKey = $this->contactKey;
        $missing = [];
        $json    = [];
        foreach ($contacts as $index => $contact) {
            $item = $classifier[$contact[$clsKey]] ?? null;
            if ($item === null) {
                continue;
            }
            $json[] = [
                'id'         => $index + 1,
                'name'       => $item['name'],
                'contact'    => $contact[$cntKey],
                'type'       => $item['type'],
                'classifier' => $contact[$clsKey],
                // для Gm.view.grid.column.Editor GmJS
                'editorConfig' => [
                    'xtype' => $item['xtype']
                ],
                // для Gm.view.grid.column.MediaLink GmJS
                'iconConfig' => [
                    'handler'  => $item['handler'],
                    'uri'      => $item['uri'],
                    'tooltip'  => $item['name']
                ]
            ];
        }
        if ($missing) {
            throw new Exception\FormatException(sprintf('Can\'t convert contacts rows to JSON, some rows are missing: "%s"', implode(', ', $missing)));
        }
        return Json::encode($json);
    }
}
