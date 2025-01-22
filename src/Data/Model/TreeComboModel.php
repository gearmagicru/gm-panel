<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Data\Model;

use Gm;

/**
 * Модель данных элементов выпадающего списка дерева.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Data\Model
 * @since 1.0
 */
class TreeComboModel extends TreeGridModel
{
    /**
     * Добавить в выпадающий список 1-й элемент "без выбора".
     * 
     * @var string
     */
    protected bool $useNoneRow = false;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        /** @var \Gm\Http\Request $request */
        $request = Gm::$app->request;
        // "быстрый" фильтр через строку ввода
        $this->fastFilter = $this->defineFastFilter($request->getQuery('filter'));
        // добавление записи "без выбора"
        $noneRow = $request->getQuery('noneRow', null);
        if ($noneRow !== null) {
            $this->useNoneRow = $noneRow == 1;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeId(): string|int|null
    {
        if ($this->nodeId === null) {
            $this->nodeId = Gm::$app->request->getQuery('node', null);
        }
        return $this->nodeId;
    }

    /**
     * {@inheritdoc}
     */
    public function getNodes(): array
    {
        $result = $this->selectNodes();
        if ($this->useNoneRow) {
            if ($result) {
                $result['total'] = $result['total'] + 1;
                array_unshift($result['nodes'], $this->noneRow());
            } else {
                $result = [
                    'total' => 1, 
                    'nodes' => $result['nodes']
                ];
            }
        }
        return $result;
    }

    /**
     * Возвращает элемент дерева "без выбора".
     * 
     * @return array
     */
    public function noneRow(): array
    {
        return [
            'id'       => 'null',
            'name'     => Gm::t(BACKEND, '[None]'),
            'count'    => 0,
            'leaf'     => true,
            'expanded' => false
        ];
    }
}
