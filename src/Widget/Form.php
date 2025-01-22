<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Widget;

use Gm\Stdlib\Collection;
use Gm\Panel\Helper\ExtForm;

/**
 * Виджет для формирования интерфейса формы.
 * 
 * Интерфейс формы реализуется с помощью Gm.view.form.Panel GmJS.
 * 
 * @see https://docs.sencha.com/extjs/5.1.4/api/Ext.form.Panel.html
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class Form extends Widget
{
    /**
     * @var string Состояние формы "Обновить".
     */
    public const STATE_UPDATE = 'update';

    /**
     * @var string Состояние формы "Добавить".
     */
    public const STATE_INSERT = 'insert';

    /**
     * @var string Состояние формы "Информация".
     */
    public const STATE_INFO = 'info';

    /**
     * @var string Состояние формы "Заказной".
     */
    public const STATE_CUSTOM = 'custom';

    /**
     * Набор кнопок управления формой в зависимости от ёё состояния.
     * 
     * Используется в том случаи, если необходим свой набор.
     * Пример:
     * ```php
     * [
     *     self::STATE_UPDATE => ['info', 'reset', 'save', 'delete', 'cancel'],
     *     ...
     * ]
     * ```
     * 
     * @var array
     */
    public array $stateButtons = [];

    /**
     * {@inheritdoc}
     */
    public array $requires = ['Gm.view.form.Panel'];

    /**
     * {@inheritdoc}
     */
    public Collection|array $params = [
        /**
         * @var string Уникальный идентификатор виджета для всего приложения.
         */
        'id' => 'form',
        /**
         * @var string Короткое название класса виджета.
         */
        'xtype' => 'g-formpanel',
        /**
         * @var string Имя контроллера (обработчика) элементов формы.
         */
        'formController' => '',
        /**
         * @var bool Загружать данные формы с поомощью AJAX-запроса после рендера формы.
         */
        'loadDataAfterRender' => true,
        /**
         * @var array|Collection Конфигурация маршрутизатора формы.
         */
        'router' => [],
        /**
         * @var array Массив виджетов формы.
         */
        'items' => []
    ];

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this->router = Collection::createInstance($this->router);
    }

    /**
     * Проверяет состояние формы.
     * 
     * @param string $state Проверяемое состояние (`STATE_UPDATE`, `STATE_INSERT`, 
     *     `STATE_INFO`, `STATE_CUSTOM`).
     * 
     * @return bool Если `true`, текущее состояние формы не совпадает с указанным.
     */
    public function hasState(string $state): bool
    {
        return $this->router->state === $state;
    }

    /**
     * Проверяет, имеет ли форма состояние `STATE_INSERT`.
     * 
     * @return bool
     */
    public function isInsertState(): bool
    {
        return $this->router->state === self::STATE_INSERT;
    }

    /**
     * Проверяет, имеет ли форма состояние `STATE_UPDATE`.
     * 
     * @return bool
     */
    public function isUpdateState(): bool
    {
        return $this->router->state === self::STATE_UPDATE;
    }

    /**
     * Проверяет, имеет ли форма состояние `STATE_CUSTOM`.
     * 
     * @return bool
     */
    public function isCustomState(): bool
    {
        return $this->router->state === self::STATE_CUSTOM;
    }

    /**
     * Добавляет набор кнопок для состояния формы.
     * 
     * @param string $state Состояние формы (`STATE_UPDATE`, `STATE_INSERT`, `STATE_INFO`, 
     *     `STATE_CUSTOM`).
     * @param array $buttons Набор кнопок, например `['info', 'reset', 'save', 'delete', 'cancel']`.
     * 
     * @return void
     */
    public function setStateButtons(string $state, array $buttons): void
    {
        $this->stateButtons[$state] = $buttons;
    }

    /**
     * Определяет набор кнопок в зависимости от состояния формы.
     *
     * @return void
     */
    public function defineButtons(): void
    {
        // если ранее не указан свой набор кнопок
        if (empty($this->buttons)) {
            // если определён свой набор кнопок для состояния формы
            if (isset($this->stateButtons[$this->router->state])) {
                $this->buttons = ExtForm::buttons($this->stateButtons[$this->router->state]);
            } else {
                // определение состояния интерфейса панели формы
                switch ($this->router->state) {
                    case self::STATE_UPDATE:
                        $this->buttons = ExtForm::buttons(['help', 'reset', 'save', 'delete', 'cancel']);
                        break;

                    case self::STATE_INSERT:
                        $this->buttons = ExtForm::buttons(['help', 'add', 'cancel']);
                        break;

                    case self::STATE_INFO:
                        $this->buttonAlign = 'center';
                        $this->buttons = [ExtForm::closeButton(['text' => 'Ok'])];
                        break;

                    case self::STATE_CUSTOM:
                        break;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beforeRender(): bool
    {
        $this->makeViewID();
        $this->defineButtons();
        return true;
    }
}
