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

/**
 * Виджет для формирования интерфейса вкладки панели виджетов.
 * 
 * Интерфейс вкладки реализуется с помощью Ext.panel.Panel Sencha ExtJS.
 * 
 * @see https://docs.sencha.com/extjs/5.1.2/api/Ext.panel.Panel.html
 * @see https://docs.sencha.com/extjs/5.1.2/api/Ext.tab.Tab.html
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class TabWidget extends Widget
{
    /**
     * {@inheritdoc}
     */
    public Collection|array $params = [
        /**
         * @var string Уникальный идентификатор виджета для всего приложения.
         */
        'id' => 'tab',
        /**
         * @var string Короткое название класса виджета.
         */
        'xtype' => 'panel',
        /**
         * @var array|string Вид макета панели.
         */
        'layout' => 'fit',
        /**
         * @var bool Позволяет пользователю закрыть вкладку.
         */
        'closable' => true,
        /**
         * @var string Класс CSS, который будет добавлен к виджету.
         */
        'cls' => 'g-widget-tab',
        /**
         * @var string|int Отступ внутри контейнера виджета.
         */
        'bodyPadding' => 0,
        /**
         * @var bool Прокрутка содержимого виджета.
         */
        'scrollable' => false,
        /**
         * @var array Параметры контейнера (приёмника) для рендера вкладки виджета.
         */
        'dockTo' => [
            'container' => 'g-widgets',
            'destroy'   => true
        ],
        /**
         * @var array Массив виджетов панели вкладки.
         */
        'items' => []
    ];

    /**
     * Устанавливает заголовок вкладке.
     * 
     * @param string $title Название заголовка.
     * 
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->params->title = $title;
        $this->params->tooltip['title'] = $title;
    }

    /**
     * Устанавливает подсказку вкладке.
     * 
     * @param array $tooltip Подсказка.
     * 
     * @return void
     */
    public function setTooltip(array $tooltip): void
    {
        if (empty($this->params->tooltip))
            $this->params->tooltip = $tooltip;
        else
            $this->params->tooltip = array_merge($this->params->tooltip, $tooltip);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeRender(): bool
    {
        $this->makeViewID();
        return true;
    }
}
