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
 * Виджет для формирования интерфейса диалогового окна с выбором.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class BrowseDialog extends Widget
{
    /**
     * Виджет формы.
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
    public Collection|array $params = [
        /**
         * @var string Уникальный идентификатор виджета для всего приложения.
         */
        'id' => 'browse-window',
        /**
         * @var string Короткое название класса виджета.
         */
        'xtype' => 'g-window',
        /**
         * @var string Стиль пользовательского интерфейса виджета.
         */
        'ui' => 'light',
        /**
         * @var string Вид макета страницы.
         */
        'layout' => 'fit',
        /**
         * @var int|string Ширина окна.
         */
        'width' => 990,
        /**
         * @var int|string Высота окна.
         */
        'height' => 400,
        /**
         * @var string Класс CSS для иконки.
         */
        'iconCls' => 'g-icon-svg g-icon-m_nodes g-icon-m_color_default',
        /**
         * @var bool Позволяет пользователю разварачивать окно.
         */
        'maximizable' => true,
        /**
         * @var bool Позволяет пользователю закрыть окно.
         */
        'closable' => true,
        /**
         * @var bool Делает окно модальным.
         */
        'modal' => true,
        /**
         * @var array Массив виджетов окна.
         */
        'items' => []
    ];

    /**
     * {@inheritdoc}
     */
    protected function init(): void
    {
        parent::init();

        // панель формы (Gm.view.form.Panel GmJS)
        $this->form = new Form([
            'id'     => 'browse-form',
            'layout' => 'fit',
            'router' => [
                'id'    => 0,
                'route' => Gm::alias('@match', '/browse'),
                'state' => Form::STATE_CUSTOM,
                'rules' => [
                    'pickup' => '{route}/pickup',
                ]
            ],
            'items' => [
                [
                    'xtype' => 'hidden',
                    'name'  => 'pickup'
                ]
            ]    
        ], $this);

        $this->items = [$this->form];
    }
}
