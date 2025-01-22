<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Widget;

use Gm\Stdlib\Collection;

/**
 * Виджет для формирования интерфейса вкладок панели навигации.
 * 
 * По умолчанию виджет имеет вкладки:
 * - Gm.view.navigator.Info
 * - Gm.view.navigator.Modules
 * 
 * @see https://docs.sencha.com/extjs/5.1.2/api/Ext.form.Panel.html
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class Navigator extends Widget
{
    /**
     * {@inheritdoc}
     */
    public Collection|array $params = [
        'show'   => null,
        'active' => null,
        'info'   => [
            'id'     => 'g-navigator-info',
            'active' => false,
            'tpl'    => ''
        ],
        'filter' => [
            'id'   => 'g-navigator-filter',
            'node' => []
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function beforeRender(): bool
    {
        if ($this->info['tpl']) {
            $this->info['tpl'] =  '<div class="g-navinfo__wrap">' . $this->info['tpl'] . '</div>';
        }
        return true;
    }
}
