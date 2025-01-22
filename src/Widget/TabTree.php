<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Widget;

use Gm;

/**
 * Виджет для формирования вкладки c древовидным интерфейсом данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class TabTree extends TabWidget
{
    /**
     * Виджет древовидного интерфейса данных.
     * 
     * @var Tree
     */
    public Tree $tree;

    /**
     * {@inheritdoc}
     */
    protected function init(): void
    {
        parent::init();

        // панель дерева (Ext.tree.Panel Sencha ExtJS)
        $this->tree = new Tree([
            'router' => [
                'rules' => [
                    'clear'  => '{route}/clear',
                    'delete' => '{route}/delete/{id}',
                    'update' => '{route}/update/{id}',
                    'data'   => '{route}/data/{id}',
                ],
                'route' => Gm::alias('@route', '/tree')
            ]
        ], $this);

        // панель навигации
        $this->navigator = new Navigator([], $this);
        $this->navigator->show = ['g-navigator-modules', 'g-navigator-info'];

        $this->title   = '#{name}';
        $this->tooltip = [
            'icon'  => $this->imageSrc('/icon.svg'),
            'title' => '#{name}',
            'text'  => '#{description}'
        ];
        $this->icon  = $this->imageSrc('/icon_small.svg');
        $this->items = [$this->tree];
    }
}
