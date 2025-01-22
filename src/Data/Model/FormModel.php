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
use Gm\Data\DataManager;
use Gm\Data\Model\RecordModel;

/**
 * Модель данных формы (при взаимодействии с 
 * представлением, использующий компонент Gm.view.form.Panel GmJS).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Data\Model
 * @since 1.0
 */
class FormModel extends RecordModel
{
    /**
     * {@inheritdoc}
     */
    protected ?string $assignType = DataManager::AT_FIELD;

    /**
     * Последнее сообщение вызываемого события.
     * 
     * Последнее сообщение будет установлено если возникают события:
     * - сохранение записи {@see FormModel::afterSave()};
     * - удаление записи {@see FormModel::afterDelete()}.
     * 
     * @var array|null $lastMessage
     */
    protected ?array $lastEventMessage = null;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        /**
         * Здесь можно выполнить инициализацию событий модели:
         * Пример событий: выборки, сохранения и удаление записи.
         * 
         * $this
         *  ->on(self::EVENT_AFTER_SELECT, function () {
         *       $identifier = $this->getIdentifier(); // идентификатор записи
         *       ...
         *   })
         *  ->on(self::EVENT_AFTER_SAVE, function ($isInsert, $columns) {
         *      if ($columns)
         *         $columnsToLog = \Gm\Helper\Str::implodeParameters($columns, ' = "', '"' . PHP_EOL, 256);
         *      ...
         *   })
         *  ->on(self::EVENT_AFTER_DELETE, function () {
         *      $identifier = $this->getIdentifier(); // идентификатор записи
         *      ...
         *  });
         */
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): mixed
    {
        return (int) Gm::$app->router->get('id');
    }

    /**
     * {@inheritdoc}
     */
    public function get(mixed $identifier = null): ?static
    {
        if ($identifier === null) {
            $identifier = $this->getIdentifier();
        }
        return $identifier ? $this->selectByPk($identifier) : null;
    }

    /**
     * Возвращает заголовок, описывающий значение полученной записи из запроса.
     * 
     * Заголовок может иметь значение основного атрибута записи или состоять из 
     * конкатенации значений указанных атрибутов. Такой заголовок используется 
     * для логирования действий пользователя или учёта аудита записи.
     * 
     * Метод должен быть переопределён в потомках, где будет указан алгоритм 
     * получения заголовка. Если метод не переопределён, возвратит идентификатор 
     * записи.
     * 
     * @return string Заголовок записи.
     */
    public function getActionTitle(): string
    {
        return (string) $this->getIdentifier();
    }

    /**
     * Возвращает последнее сообщение вызываемого события.
     * 
     * @see FormModel::$lastEventMessage
     * 
     * @return array|null Последнее сообщение вызываемого события.
     */
    public function getLastEventMessage(): ?array
    {
        return $this->lastEventMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        static $labels = null;

        if ($labels === null) {
            $labels = [];
            if ($this->dataManager) {
                foreach ($this->dataManager->fieldOptions as $fieldAlias => $options) {
                    if (isset($options['label'])) {
                        $labels[$fieldAlias] = $this->module->t($options['label']);
                    }
                }
            }
        }
        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function tableDependencies(): ?array
    {
        // правила для удаление только одной выбранной записи
        return $this->dataManager->dependencies['delete'] ?? null;
    }

    /**
     * Получает значение каждого атрибута для вывода его в элемент интерфейса.
     * 
     * Т.к. для элемента интерфейса значение атрибута может быть отличным от хранения 
     * в базе данных, то для его получения применяется метод атрибута.
     * 
     * Например, атрибут 'category' в базе данных имеет значение '5', но в элементе
     * интерфейса (выпадающий список) должно быть значение `[5, 'Название элемента списка']`.
     * Для этого, можно добавить метод 'outCategory', где 'out' - приставка к названию атрибута.
     * 
     * Например:
     * ```php
     * function outCategory(value) { return [value, 'Название элемента списка']; }
     * ```
     * 
     * @return void
     */
    public function outAttributes(): void
    {
        foreach ($this->attributes as $name => $value) {
            $getter = 'out' . $name;
            if (method_exists($this, $getter)) {
                $value = $this->$getter($value);
                if ($value !== null) {
                    $this->attributes[$name] = $value;
                }
            }
        }
    }

    /**
     * Предварительная обработка модели перед возвратом ее
     * атрибутов {@see \Gm\Data\Model\AbstractModel::getAttributes()} контроллеру.
     *
     * @return void
     */
    public function processing(): void
    {
        if ($this->useLocalizer()) {
            $this->getLocalizer()->fillAttributes();
        }

        $this->outAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave(
        bool $isInsert, 
        array $columns = null, 
        false|int|string|null $result = null
    ): void
    {
        if ($this->useLocalizer()) {
            $this->getLocalizer()->save();
        }

        // при добавлении, может быть составной первичный ключ и идентификатор новой 
        // записи всегда будет '0', чтобы сообщение адекватно выводилось указываем '1'
        if ($isInsert && $result === 0) {
            $result = 1;
        }

        /** @var bool|int $result */
        $this->trigger(
            self::EVENT_AFTER_SAVE,
            [
                'isInsert'   => $isInsert,
                'columns'    => $columns,
                'result'     => $result,
                'message'    => $this->lastEventMessage = $this->saveMessage($isInsert, (int) $result)
            ]
        );
    }

    /**
     * Возвращает локализованные сообщения в виде пар "ключ - значение".
     * 
     * Ключ применяют {@see FormModel::saveMessage()} и {@see FormModel::deleteMessage()}
     * для формирования сообщений на действие над записью.
     *
     * @return array
     */
    protected function getActionMessages(): array
    {
        return [
            'titleAdd'           => Gm::t(BACKEND, 'Adding record'),
            'titleUpdate'        => Gm::t(BACKEND, 'Update record'),
            'titleDelete'        => Gm::t(BACKEND, 'Deletion'),
            'msgSuccessAdd'      => Gm::t(BACKEND, 'Record successfully added'),
            'msgUnsuccessAdd'    => Gm::t(BACKEND, 'Unable to add record'),
            'msgSuccessUpdate'   => Gm::t(BACKEND, 'Record successfully update'),
            'msgUnsuccessUpdate' => Gm::t(BACKEND, 'Unable to update record'),
            'msgSuccessDelete'   => Gm::t(BACKEND, 'Record successfully deleted'),
            'msgUnsuccessDelete' => Gm::t(BACKEND, 'Unable to delete record'),
        ];
    }

    /**
     * Возвращает сообщение полученное при сохранении записи
     * события {@see EVENT_AFTER_SAVE} метода {@see afterSave()}.
     *
     * @param bool $isInsert Если true, метод вызывается при вставке записи, иначе
     *     при обновлении записи.
     * @param int $result Если результат больше чем 0, запись обновлена или добавлена.
     * 
     * @return array Сообщение имеет вид:
     *     [
     *         "success" => true,
     *         "message" => "Record successfully added",
     *         "title"   => "Adding record",
     *         "type"    => "accept"
     *     ]
     */
    public function saveMessage(bool $isInsert, int $result): array
    {
        $messages = $this->getActionMessages();

        $type     = 'accept';
        $message  = '';
        if ($result > 0) {
            $message = $messages[$isInsert ? 'msgSuccessAdd' : 'msgSuccessUpdate'];
        } else {
            $message = $messages[$isInsert ? 'msgUnsuccessAdd' : 'msgUnsuccessUpdate'];
            $type = $isInsert ? 'error' : 'warning';
        }
        return [
            'success'  => $result > 0, // успех измнения записи
            'message'  => $message, // сообщение
            'title'    => $messages[$isInsert ? 'titleAdd' : 'titleUpdate'], // заголовок сообщения
            'type'     => $type // тип сообщения
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete(false|int|null $result = null): void
    {
        /** @var bool|int $result */
        $this->trigger(
            self::EVENT_AFTER_DELETE, 
            [
                'result'  => $result,
                'message' => $this->lastEventMessage = $this->deleteMessage((int) $result)
            ]
        );
    }

    /**
     * Возвращает сообщение полученное при сохранении записи
     * события {@see EVENT_AFTER_DELETE} метода {@see afterDelete()}.
     *
     * @param int $result Количество удаленных записей.
     * 
     * @return array Сообщение имеет вид:
     *     [
     *         "success" => true,
     *         "message" => "Record successfully deleted",
     *         "title"   => "Deletion",
     *         "type"    => "accept"
     *     ]
     */
    public function deleteMessage(int $result): array
    {
        $messages = $this->getActionMessages();

        $type     = 'accept';
        $message  = '';
        // запись удалена
        if ($result > 0) {
            $message = $messages['msgSuccessDelete'];
        // запись не удалена
        } else {
            $message = $messages['msgUnsuccessDelete'];
            $type    = 'error';
        }
        return [
            'success'  => $result > 0, // успех удаления записи
            'message'  => $message, // сообщение
            'title'    => $messages['titleDelete'], // заголовок сообщения
            'type'     => $type // тип сообщения
        ];
    }

   /**
     * Возвращает URL-адрес аудита записи.
     * 
     * @param string $action Действие, например: 'updated', 'created'.
     * @param string $title Заголовок для отображения информации о записи.
     * 
     * @return string
     */
    public function getAuditUrl(string $action, string $title): string
    {
        $fldAction  = ucfirst($action);
        $fldDate = 'log' . $fldAction . 'Date';
        $fldUser = 'log' . $fldAction . 'User';
        if (empty($this->{$fldDate}) || empty($this->{$fldUser})) {
            return '';
        }

        /** @var null|array $auditLog Модуль аудита записей */
        $auditLog = Gm::$app->modules->getRegistry()->get('gm.be.audit_log');
        // если модуль не установлен
        if ($auditLog === null) return '';

        return
            '@backend/' . $auditLog['route'] . '/row/view' . $this->id  .'?' .
            http_build_query([
                'action' => $action,
                'row'    => $this->{$fldUser},
                'date'   => gmdate('d-m-Y_H-I-s', strtotime($this->{$fldDate})),
                'title'  => $title
            ]);
    }
}
