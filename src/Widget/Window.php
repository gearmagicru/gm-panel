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
 * Виджет для формирования интерфейса окна.
 * 
 * Интерфейс окна реализуется с помощью Ext.window.Window Sencha ExtJS.
 * 
 * Виджет представлен в виде специализированной панели, предназначенной для использования 
 * в качестве окна приложения. По умолчанию окна перемещаются, изменяются в размерах и 
 * перетаскиваются. Окна можно развернуть, свернуть и восстановить их прежний размер.
 * 
 * @see https://docs.sencha.com/extjs/5.1.2/api/Ext.window.Window.html
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class Window extends Widget
{
    /**
     * {@inheritdoc}
     */
    public array $requires = ['Gm.view.window.Window'];

    /**
     * {@inheritdoc}
     */
    public Collection|array $params = [
        /**
         * @var string Уникальный идентификатор виджета для всего приложения.
         */
        'id' => 'window',
        /**
         * @var string Короткое название класса виджета.
         */
        'xtype' => 'g-window',
        /**
         * @var string Класс CSS, который будет добавлен к виджету.
         */
        'cls'=> 'g-window',
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
    public function beforeRender(): bool
    {
        $this->makeViewID();
        return true;
    }
}
