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
use Gm\Stdlib\Collection;

/**
 * Виджет для формирования интерфейса вкладки справочной информации.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class TabGuide extends TabWidget
{
    /**
     * Разделы справки (Gm.view.guide.TreePanel GmJS)
     * 
     * @var Collection
     */
    public Collection $tree;

    /**
     * Панель фрейма справки (Ext.panel.Panel Sencha ExtJS).
     * 
     * @var Collection
     */
    public Collection $panel;

    /**
     * Фрейм справочной информации (Gm.view.guide.IFrame GmJS).
     * 
     * @var Collection
     */
    public Collection $frame;

    /**
     * {@inheritdoc}
     */
    protected function init(): void
    {
        parent::init();

        // разделы справки (Gm.view.guide.TreePanel GmJS)
        $this->tree = Collection::createInstance([
            'xtype'       => 'gm-guide-tree',
            'region'      => 'east',
            'title'       => '#{name}',
            'width'       => 300,
            'collapsible' => true,
            'split'       => true,
            'frameConfig' => [
                'id'       => $this->creator->viewId('frame'),
                'url'      => '',
                'nodesUrl' => Gm::alias('@match', '/nodes/data'),
                'params'   => []
            ],
            'store' => [
                'model' => 'Gm.be.guide.DataModel',
                'root'  => ['expanded' => true, 'children' => []]
            ]
        ]);

        // фрейм справочной информации (Gm.view.guide.IFrame GmJS)
        $this->frame = Collection::createInstance([
            'xtype' => 'gm-guide-iframe',
            'id'    => $this->creator->viewId('frame'),
            'src'   => ''
        ]);

        // панель фрейма справки (Ext.panel.Panel Sencha ExtJS)
        $this->panel = Collection::createInstance([
            'xtype'   => 'panel',
            'layout'  => 'fit',
            'region'  => 'center',
            'bodyCls' => 'gm-guide-mask',
            'items'   => [$this->frame]
        ]);

        $this->title   = '#{name}';
        $this->tooltip = [
            'icon'  => $this->imageSrc('/icon.svg'),
            'title' => '#{name}',
            'text'  => '#{description}'
        ];
        $this->layout = 'border';
        $this->icon   = $this->imageSrc('/icon_small.svg');
        $this->items  = [$this->tree, $this->panel];
    }
}
