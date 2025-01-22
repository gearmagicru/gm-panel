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
 * Виджет для формирования интерфейса формы.
 * 
 * Интерфейс формы реализуется с помощью Gm.view.form.Panel GmJS.
 * 
 * @see https://docs.sencha.com/extjs/5.1.4/api/Ext.form.Panel.html
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class FormPanel extends Widget
{
    /**
     * {@inheritdoc}
     */
    public Collection|array $params = [
        /**
         * @var string Короткое название класса виджета.
         */
        'xtype' => 'form',
        /**
         * @var array Массив виджетов формы.
         */
        'items' => []
    ];
}
