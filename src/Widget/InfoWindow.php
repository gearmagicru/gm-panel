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
 * Виджет для формирования интерфейса окна информации записи.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class InfoWindow extends Window
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
            'router' => [
                'id'    => $this->getRowID(),
                'route' => Gm::alias('@match', '/info'),
                'state' => Form::STATE_INFO,
                'rules' => [
                    'data' => '{route}/data/{id}'
                ]
            ]
        ], $this);

        $this->cls      = 'g-window_info';
        $this->title    = '#{form.title}';
        $this->titleTpl = '#{form.titleTpl}';
        $this->iconCls  = 'g-icon-svg g-icon-m_info-circle g-icon-m_color_active';
        $this->items    = [$this->form];
    }

    /**
     * Возвращает идентификатор записи из запроса.
     * 
     * Если в запросе нет идентификатора, тогда возвратит `null`.
     * 
     * @return string|int|null
     */
    public function getRowID(): string|int|null
    {
        return Gm::$app->router->get('id');
    }
}
