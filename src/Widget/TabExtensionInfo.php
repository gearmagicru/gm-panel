<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Widget;

/**
 * Виджет для формирования вкладки c информацией о модуле.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class TabExtensionInfo extends TabWidget
{
    /**
     * Панель вкладки (Ext.panel.Panel Sencha ExtJS).
     * 
     * @var Widget
     */
    public Widget $panel;

    /**
     * {@inheritdoc}
     */
    protected function init(): void
    {
        parent::init();

        // панель вкладки (Ext.panel.Panel Sencha ExtJS)
        $this->panel = new Widget([
            'bodyCls'    => 'g-extension-info__body',
            'scrollable' => true
        ], $this);

        $this->id      = 'tab-info';
        $this->tooltip = [
            'icon'  => $this->creator->getAssetsUrl() . '/images/icon.svg',
            'text'  => $this->creator->t('#{description}')
        ];
        $this->bodyPadding = 0;
        $this->icon  = $this->creator->getAssetsUrl() . '/images/icon_small.svg';
        $this->cls   = 'g-module-info g-panel_background';
        $this->items = [$this->panel];
    }
}
