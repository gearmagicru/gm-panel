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
 * Виджет для формирования интерфейса окна настроек.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class SettingsWindow extends Window
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
                'help' => ['subject' => 'settings'], 'reset', 'save', 'cancel'
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
            ]
        ], $this);

        $this->id       = 'settings-window';
        $this->cls      = 'g-window_settings';
        $this->title    = Gm::t(BACKEND, 'Module settings {0}', [$this->creator->t('{name}')]);
        $this->titleTpl = $this->title;
        $this->iconCls  = 'g-icon-svg g-icon-m_wrench';
        $this->layout   = 'fit';
        $this->width    = 460;
        $this->items    = [$this->form];
    }
}
