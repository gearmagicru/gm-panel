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
 * Виджет для формирования интерфейса диалогового окна с просмотром записей сетки 
 * данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class GridDialog extends Window
{
    /**
     * Виджет сетки данных.
     * 
     * @var Grid
     */
    public Grid $grid;

    /**
     * {@inheritdoc}
     */
    public array $requires = [
        'Gm.view.window.Window',
        'Gm.view.grid.Grid',
        'Gm.view.grid.button.Button',
        'Gm.view.plugin.PageSize'
    ];

    /**
     * {@inheritdoc}
     */
    protected function init(): void
    {
        parent::init();

        // панель сетки данных (Gm.view.grid.Grid GmJS)
        $this->grid = new Grid([
            'id'     => 'grid-dialog',
            'router' => [
                'rules' => [
                    'clear'     => '{route}/clear',
                    'delete'    => '{route}/delete',
                    'data'      => '{route}/data',
                    'updateRow' => '{route}/update/{id}',
                    'deleteRow' => '{route}/delete/{id}'
                ]
            ]
        ], $this);

        $this->id       = 'window-dialog';
        $this->title    = '#{dialog.title}';
        $this->titleTpl = '#{dialog.titleTpl}';
        $this->items    = [$this->grid];
    }
}
