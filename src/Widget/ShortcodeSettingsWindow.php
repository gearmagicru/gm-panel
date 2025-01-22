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
 * Виджет для формирования интерфейса окна настроек шорткода.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class ShortcodeSettingsWindow extends Window
{
    /**
     * Виджет для формирования интерфейса формы.
     * 
     * @var FormPanel
     */
    public FormPanel $form;

    /**
     * {@inheritdoc}
     */
    public array $requires = [
        'Gm.view.window.Window'
    ];

    /**
     * {@inheritdoc}
     */
    protected function init(): void
    {
        parent::init();

        // панель формы (Ext.form.Panel ExtJS)
        $this->form = new FormPanel([
            'buttons' => ExtForm::buttons([
                // кнопка "button"
                '' => [
                    'text' => Gm::t(BACKEND, 'Add'), 
                    'handler' => 'insertShortcode'
                ],
                'cancel'
            ]),
            'bodyPadding' => 5
        ], $this);

        // окно компонента (Ext.window.Window Sencha ExtJS)
        $this->cls      = 'g-window_settings';
        $this->iconCls  = 'g-icon-svg g-icon-m_shortcode';
        $this->layout   = 'fit';
        $this->width    = 460;
        $this->items    = [$this->form];
    
        /**
         * Шаблон шорткода, где {0} - атрибуты добавляемые из настроек.
         */
        $this->shortcodeTpl = '[foobar {0}][foobar/]';
    }
}
