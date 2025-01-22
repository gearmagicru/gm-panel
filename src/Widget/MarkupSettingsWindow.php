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
 * Виджет для формирования интерфейса окна настроек разметки компонента приложения.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class MarkupSettingsWindow extends Window
{
    /**
     * Виджет для формирования интерфейса формы.
     * 
     * @var Form
     */
    public Form $form;

    /**
     * {@inheritdoc}
     */
    public array $requires = [
        'Gm.view.window.Window',
        'Gm.view.form.Panel'
    ];

    /**
     * {@inheritdoc}
     */
    protected function init(): void
    {
        parent::init();

        // панель формы (Gm.view.form.Panel GmJS)
        $this->form = new Form([
            'id'          => 'settings-form',
            'buttons'     => ExtForm::buttons([
                'help' => ['subject' => 'markupsettings'], 'save', 'cancel'
            ]),
            'bodyPadding' => 5,
            'router'      => [
                'id'    => '0',
                'route' => Gm::alias('@match', '/settings'),
                'state' => Form::STATE_CUSTOM,
                'rules' => [
                    'update' => '{route}/update/{id}',
                    'data'   => '{route}/data/{id}'
                ]
            ],
            'loadDataAfterRender' => false
        ], $this);

        $this->id      = 'settings-window';
        $this->cls     = 'g-window_settings';
        $this->iconCls = 'g-icon-svg g-icon-m_markup';
        $this->layout  = 'fit';
        $this->width   = 460;
        $this->items   = [$this->form];
    }
}
