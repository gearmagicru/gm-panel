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
 * Виджет для формирования интерфейса панели данных.
 * 
 * Интерфейс окна реализуется с помощью Gm.view.data.Panel GmJS.
 * 
 * Виджет представлен в виде специализированной панели, предназначенной для использования 
 * в качестве контейнера отображения информации с пагинацией.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class DataPanel extends Widget
{
    /**
     * {@inheritdoc}
     */
    public array $requires = ['Gm.view.data.Panel'];

    /**
     * {@inheritdoc}
     */
    public Collection|array $params = [
        /**
         * @var string Короткое название класса виджета.
         */
        'xtype' => 'g-datapanel',
        /**
         * @var array|Collection Конфигурация маршрутизатора сетки (Gm.ActionRouter).
         */
        'router' => [],
        /**
         * @var array|Collection Конфигурация панели навигации записей (Gm.view.plugin.PageSize).
         */
        'pagingtoolbar' => [
            'xtype'       => 'pagingtoolbar',
            'dock'        => 'bottom',
            'displayInfo' => true,
            'plugins'     => ['pagesize']
        ],
        /**
         * @var array|Collection Конфигурация хранения записей сетки (Ext.data.Store).
         */
        'store' => [
            'autoLoad'     => true,
            'model'        => null,
            'pageSize'     => 25,
            'proxy'        => [
                'type'          => 'ajax',
                'url'           => '',
                'method'        => 'POST',
                'actionMethods' => ['read' => 'POST'],
                'reader'        => [
                    'type'          => 'json',
                    'rootProperty'  => 'data',
                    'totalProperty' => 'total'
                ]
            ]
        ],
        /**
         * @var array|Collection Шаблон данных (Gm.view.data.View).
         */
        'dataView' => [],
        /**
         * @var array Обработчик событий.
         */
        'listeners' => []
    ];

    /**
     * {@inheritdoc}
     */
    protected function init(): void
    {
        parent::init();

        // маршрутизатор запросов (Gm.ActionRouter GmJS)
        $this->router = Collection::createInstance($this->router);
        $this->router->rules = ['data' => '{route}/data'];

        // источник данных (Ext.data.Store Sencha ExtJS)
        $this->store = Collection::createInstance($this->store);

        // пагинация (Gm.view.plugin.PageSize)
        $this->pagingtoolbar = Collection::createInstance($this->pagingtoolbar);

        // шаблон данных (Gm.view.data.View GmJS)
        $this->dataView = Collection::createInstance($this->dataView);
    }
}
