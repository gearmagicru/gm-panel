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
 * Виджет для формирования интерфейса окна редактирования записи.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Widget
 * @since 1.0
 */
class EditWindow extends Window
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
        $rowId = $this->getRowID();

        parent::init();

        // панель формы (Gm.view.form.Panel GmJS)
        $this->form = new Form([
            'router' => [
                'id'    => $rowId,
                'route' => Gm::alias('@match', '/form'),
                'state' => $rowId ? Form::STATE_UPDATE : Form::STATE_INSERT,
                'rules' => [
                    'submit' => '{route}/data/{id}',
                    'update' => '{route}/update/{id}',
                    'delete' => '{route}/delete/{id}',
                    'add'    => '{route}/add',
                    'data'   => '{route}/data/{id}'
                ] 
            ]
        ], $this);

        $this->cls      = 'g-window_profile';
        $this->title    = '#{form.title}';
        $this->titleTpl = '#{form.titleTpl}';
        $this->iconCls  = 'g-icon-svg g-icon-m_' . ($rowId ? 'edit' : 'add');
        $this->items    = [$this->form];
    }

    /**
     * Возвращает идентификатор редактируемой записи из запроса.
     * 
     * Если в запросе нет идентификатора, тогда возвратит `0`.
     * 
     * @return int
     */
    public function getRowID(): int
    {
        return (int) Gm::$app->router->get('id');
    }

    /**
     * Интерфейс окна редактирования записи находится в режиме "Добавить".
     * 
     * @return bool
     */
    public function isInsertMode(): bool
    {
        return $this->getRowID() === 0;
    }

    /**
     * Интерфейс окна редактирования записи находится в режиме "Обновить".
     * 
     * @return bool
     */
    public function isUpdateMode(): bool
    {
        return $this->getRowID() > 0;
    }
}
