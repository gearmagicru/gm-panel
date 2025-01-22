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
 * Виджет для формирования интерфейса окна отображения параметров.
 * 
 * Для доступа к параметрам используется свойство {@see OptionsWindow::$options} класса.
 * Параметры устанавливают в конструкторе класса:
 * ```php
 * new OptionsWindow([
 *     'options' => [
 *         'key' => 'value',
 *         // ...
 *     ]
 * ]);
 * ```
 * или методом {@see OptionsWindow::setOptions()}.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class OptionsWindow extends Window
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
            'id'          => 'options-form',
            'buttons'     => ExtForm::buttons(['help', 'reset', 'save', 'cancel']),
            'bodyPadding' => 5,
            'router'      => [
                'id'    => '0',
                'route' => Gm::alias('@match', '/options'),
                'state' => Form::STATE_CUSTOM,
                'rules' => [
                    'update' => '{route}/update/{id}'
                ]
            ],
            'loadDataAfterRender' => false
        ], $this);

        $this->id      = 'options-window';
        $this->cls     = 'g-window_settings';
        $this->iconCls = 'g-icon-svg g-icon-m_wrench';
        $this->layout  = 'fit';
        $this->width   = 460;
        $this->items   = [$this->form];
    }

    /**
     * Возвращает параметры в виде пар "ключ - значение".
     * 
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options ?: [];
    }

    /**
     * Устанавливает параметры.
     * 
     * @param array $options Параметры в виде пар "ключ - значение".
     * 
     * @return void
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }
}
