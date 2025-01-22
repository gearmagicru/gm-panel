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
use Gm\Panel\Helper\HtmlGrid;
use Gm\Panel\Data\Model\GridModel;

/**
 * Виджет для формирования древовидного интерфейса сетки древовидных данных.
 * 
 * Интерфейс сетки древовидных данных реализуется с помощью Gm.view.grid.Tree GmJS.
 * 
 * @see https://docs.sencha.com/extjs/5.1.2/api/Ext.tree.Panel.html
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class TreeGrid extends Widget
{
    /**
     * {@inheritdoc}
     */
    public array $requires = [
        'Gm.view.grid.Tree',
        'Gm.view.grid.button.Button',
        'Gm.view.plugin.PageSize'
    ];

    /**
     * {@inheritdoc}
     */
    public Collection|array $params = [
        /**
         * @var string Уникальный идентификатор виджета для всего приложения.
         */
        'id' => 'grid',
        'logField' => '',
        /**
         * @var string Короткое название класса виджета.
         */
        'xtype' => 'g-gridtree',
        /**
         * @var string Класс CSS, который будет добавлен к виджету.
         */
        'cls' => 'g-grid g-gridtree',
        /**
         * @var bool Стрелки в стиле Vista в дереве.
         */
        'useArrows' => true,
        /**
         * @var bool Скрыть корень дерева.
         */
        'rootVisible'  => false,
        /**
         * @var bool Выделение нескольких строк.
         */
        'multiSelect' => true,
        /**
         * @var bool Показать границы строк.
         */
        'rowLines' => true,
        /**
         * @var bool Показать границы столбцов.
         */
        'columnLines' => true,
        /**
         * @var array Массив плагинов добовляемых к виджету.
         */
        'plugins' => 'gridfilters',
        /**
         * @var array|Collection Конфигурация маршрутизатора сетки (Gm.ActionRouter).
         */
        'router' => [],
        /**
         * @var array|Collection Конфигурация панели навигации записей (Gm.view.plugin.PageSize).
         */
        'pagingtoolbar' => [
            'xtype'       => 'pagingtoolbar',
            'dock'        => 'bottom',
            'displayInfo' => true,
            'plugins'     => ['pagesize']
        ],
        /**
         * @var array|Collection Конфигурация хранения записей сетки (Ext.data.TreeStore).
         */
        'store' => [
            'autoLoad'     => true,
            'model'        => 'dynamicTreeDataModel',
            'remoteFilter' => true,
            'folderSort'   => true,
            'proxy'        => [
                'type'          => 'ajax',
                'url'           => '',
                'async'         => true,
                'actionMethods' => ['read' => 'POST'],
                'reader'        => [
                    'type'            => 'json',
                    'rootProperty'    => 'data',
                    'totalProperty'   => 'total',
                    'successProperty' => 'success'
                ]
            ]
        ]
    ];

    /**
     * {@inheritdoc}
     */
    protected function init(): void
    {
        parent::init();

        $this->store = Collection::createInstance($this->store);
        $this->router = Collection::createInstance($this->router);
        $this->pagingtoolbar = Collection::createInstance($this->pagingtoolbar);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeRender(): bool
    {
        $this->makeViewID();
        return true;
    }

    /**
     * Добавляет столбцов аудита записи.
     * 
     * @return void
     */
    public function addAuditColumns(): void
    {
        /** @var null|array $auditLog Модуль аудита записей */
        $auditLog = Gm::$app->modules->getRegistry()->get('gm.be.audit_log');
        // если модуль не установлен
        if ($auditLog === null) return;

        // маршрут аудита записи
        $auditRoute = '@backend/' . $auditLog['route'] . '/row/view';

        $dateTimeFormat = Gm::$app->formatter->formatWithoutPrefix('dateTimeFormat');
        $this->params->columns[] = [
            'text'      => Gm::t(BACKEND, 'ID'),
            'dataIndex' => 'logId',
            'filter'    => ['type' => 'numeric'],
            'width'     => 70
        ];
        $route = $auditRoute . '/{' . GridModel::COL_CREATED_USER . '}?action=created&row={id}&date={' . GridModel::COL_CREATED_UTC . '}&title={' . $this->params->logField . ':encodeURI}';
        $this->params->columns[] = [
            'xtype'     => 'templatecolumn',
            'text'      => Gm::t(BACKEND, 'Date added'),
            'dataIndex' => GridModel::COL_CREATED_DATE,
            'format'    => $dateTimeFormat,
            'tpl'       => HtmlGrid::tpl(
                HtmlGrid::a(
                    '<span class="g-icon g-icon-svg g-icon_size_16 g-icon_gridcolumn-audit"></span> {' . GridModel::COL_CREATED_DATE . ':date("' . $dateTimeFormat . '")}', '#',
                    [
                        'class'   => 'g-grid-cell_log',
                        'onclick' => "Gm.getApp().widget.load('$route')"
                    ]
                ),
                ['if' => GridModel::COL_CREATED_DATE]
            ),
            'filter'    =>['type' => 'date', 'dateFormat' => 'Y-m-d'],
            'width'     => 170
        ];
        $route = $auditRoute . '/{' . GridModel::COL_UPDATED_USER . '}?action=updated&row={id}&date={' . GridModel::COL_UPDATED_UTC . '}&title={' . $this->params->logField . ':encodeURI}';
        $this->params->columns[] = [
            'xtype'     => 'templatecolumn',
            'text'      => Gm::t(BACKEND, 'Date edit'),
            'dataIndex' => GridModel::COL_UPDATED_DATE,
            'format'    => $dateTimeFormat,
            'tpl'       => HtmlGrid::tpl(
                HtmlGrid::a(
                    '<span class="g-icon g-icon-svg g-icon_size_16 g-icon_gridcolumn-audit"></span> {' . GridModel::COL_UPDATED_DATE . ':date("' . $dateTimeFormat . '")}', '#',
                    [
                        'class'   => 'g-grid-cell_log',
                        'onclick' => "Gm.getApp().widget.load('$route')"
                    ]
                ),
                ['if' => GridModel::COL_UPDATED_DATE]
            ),
            'filter'    => ['type' => 'date', 'dateFormat' => 'Y-m-d'],
            'width'     => 170
        ];
    }
}
