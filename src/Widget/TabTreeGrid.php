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
 * Виджет для формирования вкладки c древовидным интерфейсом сетки 
 * древовидных данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class TabTreeGrid extends TabWidget
{
    /**
     * Виджет древовидного интерфейса сетки древовидных данных.
     * 
     * @var TreeGrid
     */
    public TreeGrid $treeGrid;

    /**
     * {@inheritdoc}
     */
    public array $requires = [
        'Gm.view.grid.Tree',
        'Gm.view.grid.button.Button',
        'Gm.view.plugin.PageSize'
    ];

    /**
     * {@inheritdoc}
     */
    protected function init(): void
    {
        parent::init();

        // панель древовидной сетки данных (Gm.view.grid.Tree GmJS)
        $this->treeGrid = new TreeGrid([
            'router' => [
                'rules' => [
                    'clear'      => '{route}/clear',
                    'delete'     => '{route}/delete',
                    'data'       => '{route}/data',
                    'supplement' => '{route}/supplement',
                    'updateRow'  => '{route}/update/{id}',
                    'deleteRow'  => '{route}/delete/{id}',
                    'expandRow'  => '{route}/expand/{id}'
                ],
                'route' => Gm::alias('@route', '/grid')
            ]
        ]);

        // панель навигации
        $this->navigator = new Navigator();
        $this->navigator->show = ['g-navigator-modules', 'g-navigator-info'];

        $this->title   = '#{name}';
        $this->tooltip = [
            'icon'  => $this->imageSrc('/icon.svg'),
            'title' => '#{name}',
            'text'  => '#{description}'
        ];
        $this->icon  = $this->imageSrc('/icon_small.svg');
        $this->items = [$this->treeGrid];
    }
}
