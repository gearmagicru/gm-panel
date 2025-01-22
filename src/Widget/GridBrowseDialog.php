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
use Gm\Panel\Helper\ExtForm;

/**
 * Виджет для формирования интерфейса диалогового окна с выбором записи из сетки данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class GridBrowseDialog extends BrowseDialog
{
    /**
     * Виджет сетки данных.
     * 
     * @var Grid
     */
    public Grid $grid;

    /**
     * Параметры выбора записей из сетки диалогового окна.
     * 
     * Параметры добавляются в конфигурацию кнопки "Browse".
     * Параметры имееют вид:
     * ```php
     * [
     *     'selectOne'        => false, // выбрать только одну запись
     *     'msgMustSelect'    => 'Entry must be selected', // сообщение "Необходимо выбрать запись!"
     *     'msgMustSelectOne' => 'Only one entry needs to be selected' // сообщение "Необходимо выбрать только одну запись!"
     * ]
     * ```
     * 
     * @var array
     */
    public array $browseGrid = [];

    /**
     * {@inheritdoc}
     */
    public array $requires = [
        'Gm.view.window.Window',
        'Gm.view.form.Panel',
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

        $this->browseGrid = [
            'selectOne'        => false,
            'msgMustSelect'    => Gm::t(BACKEND, 'Entry must be selected'),
            'msgMustSelectOne' => Gm::t(BACKEND, 'Only one entry needs to be selected')
        ];

        // панель сетки данных (Gm.view.grid.Grid GmJS)
        $this->grid = new Grid([
            'id' => 'browse-grid',
            'router' => [
                'rules' => [
                    'data'       => '{route}/data',
                    'supplement' => '{route}/supplement'
                ],
                'route' => Gm::alias('@route')
            ]
        ], $this);

        // панель формы (Gm.view.form.Panel GmJS)
        $this->form->items[] = $this->grid;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeRender(): bool
    {
        parent::beforeRender();

        if (empty($this->form->buttons)) {
            if ($this->browseGrid)
                $browseButton = ['handlerArgs' => ['browseGrid' => $this->browseGrid]];
            else
                $browseButton = [];
            $this->form->buttons = ExtForm::buttons(['info', 'browse' => $browseButton, 'cancel']);
        }
        return true;
    }
}
