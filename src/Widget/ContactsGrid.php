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
 * Виджет для формирования интерфейса сетки контактных данных.
 * 
 * Интерфейс сетки контактных данных реализуется с помощью Ext.grid.Panel Sencha ExtJS.
 * 
 * @see https://docs.sencha.com/extjs/5.1.2/api/Ext.grid.Panel.html
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class ContactsGrid extends Widget
{
    /**
     * {@inheritdoc}
     */
    public Collection|array $params = [
        /**
         * @var string Уникальный идентификатор виджета для всего приложения.
         */
        'id' => 'contacts',
        /**
         * @var string Короткое название класса виджета.
         */
        'xtype' => 'grid',
        /**
         * @var string Класс CSS, который будет добавлен к виджету.
         */
        'cls' => 'g-grid g-grid_editor',
        /**
         * @var bool Заблокировать выделение записей.
         */
        'disableSelection' => true,
        /**
         * @var bool Убрать линии между строк.
         */
        'rowLines' => false,
        /**
         * @var bool Убрать линии между столбцами.
         */
        'columnLines' => false,
        /**
         * @var array|Collection Конфигурация хранения записей сетки (Ext.data.Store).
         */
        'store' => [
            'fields' => [
                [
                    'name' => 'id',
                    'type' => 'int'
                ],
                [
                    'name' => 'classifier',
                    'type' => 'int'
                ],
                [
                    'name' => 'name',
                    'type' => 'string'
                ],
                [
                    'name' => 'contact',
                    'type' => 'string'
                ],
                [
                    'name' => 'editorConfig'
                ],
                [
                    'name' => 'iconConfig'
                ],
                [
                    'name' => 'type',
                    'type' => 'string'
                ]
            ],
            'data' => [],
            'dataTpl' => []
        ],
        /**
         * @var array Столбцы сетки (Ext.grid.column.Column).
         */
        'columns' => [
            [
                'xtype'     => 'templatecolumn',
                'text'      => '#Contact type',
                'dataIndex' => 'name',
                'tpl'       => '{name}: ',
                'align'     => 'right',
                'width'     => 200,
                'tdCls'     => 'g-gridcolumn-label__td',
            ],
            [
                'xtype'       => 'g-gridcolumn-medialink',
                'iconIndex'   => 'type',
                'dataIndex'   => 'contact',
                'configIndex' => 'iconConfig'
            ],
            [
                'xtype'       => 'g-gridcolumn-editor',
                'text'        => '#Contact',
                'sortable'    => false,
                'dataIndex'   => 'contact',
                'configIndex' => 'editorConfig',
                'flex'        => 1,
                'sortable' => true,
            ],
            [
                'xtype'     => 'g-gridcolumn-delete-action',
                'iconFaCls' => 'fas fa-backspace'
            ]
        ],
        /**
         * @var array|Collection Панель инструментов.
         */
        'tbar' => [
            'ui'    => 'tools',
            'items' => [
                [
                    'xtype'     => 'g-gridbutton-addmediarecord',
                    'ui'        => 'form-tool',
                    'iconCls'   => 'g-icon-svg g-icon_size_17g-icon-svg_size_15 g-icon-m_add g-icon-m_color_base',
                    'minWidth'  => 40,
                    'minHeight' => 26,
                    'tooltip'   => '#Add contact',
                ],
                [
                    'xtype'     => 'g-gridbutton-removerecord',
                    'ui'        => 'form-tool',
                    'iconCls'   => 'g-icon-svg g-icon_size_17  g-icon-svg_size_17 g-icon_cleanup',
                    'minWidth'  => 40,
                    'minHeight' => 26,
                    'tooltip'   => '#Clear contacts'
                ]
            ]
        ],
        /**
         * @var array Плагины сетки.
         */
        'plugins' => [
            ['ptype' => 'cellediting']
        ]
    ];

    /**
     * {@inheritdoc}
     */
    protected function init(): void
    {
        parent::init();

        $this->store = Collection::createInstance($this->store);
        $this->tbar = Collection::createInstance($this->tbar);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeRender(): bool
    {
        $this->makeViewID();
        $this->store->dataTpl = $this->types;
        return true;
    }

    /**
     * Устанавливает виды контактов.
     * 
     * @see ContactsGrid::$types
     * 
     * @param array<string, mxied> $contactTypes Виды контактов.
     * 
     * @return void
     */
    public function setTypes(array $contactTypes): void
    {
        $rows = [];
        foreach ($contactTypes as $type) {
            $rows[] = [
                'name'       => $type['name'],
                'type'       => $type['type'],
                'classifier' => $type['id'],
                'editorConfig' => [
                    'xtype' => $type['xtype']
                ],
                // конфигурация для заголовка столбца сетки "Медия ссылка"
                'iconConfig' => [
                    'handler'  => $type['handler'],
                    'uri'      => $type['uri'],
                    'tooltip'  => $type['name']
                ]
            ];
        }
        $this->types = $rows;
    }
}
