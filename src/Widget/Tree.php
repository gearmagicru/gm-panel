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
 * Виджет для формирования древовидного интерфейса данных.
 * 
 * Интерфейс древовидных данных реализуется с помощью Ext.tree.Panel Sencha ExtJS.
 * 
 * @see https://docs.sencha.com/extjs/5.1.2/api/Ext.tree.Panel.html
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\View
 * @since 1.0
 */
class Tree extends Widget
{
    /**
     * {@inheritdoc}
     */
    public Collection|array $params = [
        /**
         * @var string Короткое название класса виджета.
         */
        'xtype' => 'treepanel',
        /**
         * @var bool false, чтобы скрыть корневой узел.
         */
        'rootVisible' => true,
        /**
         * @var array Корневой узел дерева (Ext.data.Model | Ext.data.TreeModel).
         */
        'root' => [
            'id'       => 'root',
            'expanded' => true,
            'text'     => '',
            'leaf'     => false
        ],
        /**
         * @var array|Collection Конфигурация маршрутизатора узлов дерева.
         */
        'router' => [],
    ];

    /**
     * {@inheritdoc}
     */
    protected function init(): void
    {
        parent::init();

        $this->root = Collection::createInstance($this->root);
        $this->router = Collection::createInstance($this->router);
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
