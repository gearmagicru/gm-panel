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
use Gm\Data\Model\DataModel;

/**
 * Модель данных структуры вложенного дерева.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Data\Model
 * @since 1.0
 */
class NodesModel extends DataModel
{
    /**
     * Название параметра, указывающий на идентификатор узла дерева.
     * 
     * @var string
     */
    public string $nodeParam = 'node';

    /**
     * Идентификатор узла дерева.
     * 
     * @var string|array|null
     */
    protected string|array|null $nodeId;

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        if (!isset($this->nodeId)) {
            $this->nodeId = Gm::$app->request->post($this->nodeParam);
        }
        return $this->nodeId;
    }

    /**
     * Возращение записи базы данных (для предварительной обработки).
     * 
     * @param array $record Массив полей с их значениями.
     * @see GridMode::select()
     * 
     * @return array
     */
    public function getRecord(array $record): array
    {
        return $record;
    }

    /**
     * Возращает массив записей (узлов дерева).
     * 
     * Результат имеет вид: `['total' => 100, 'rows' => [...]]`, где:
     *     - 'total', количество записей;
     *     - 'rows', узлы дерева.
     * 
     * @return array
     */
    public function getNodes(): array
    {
        return [];
    }
}
