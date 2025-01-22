<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Data\Model;

use Gm\Config\Config;
use Gm\Data\Model\RecordModel;

/**
 * Модель данных настроек виджета.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Data\Model
 * @since 1.0
 */
class WidgetSettingsModel extends RecordModel
{
    /**
     * Настройки виджета.
     * 
     * @var Config
     */
    protected Config $settings;

    /**
     * Абсолютный (полный) путь виджета.
     * 
     * Указывается параметром в конструкторе класса.
     * 
     * @var string
     */
    public string $basePath = '';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
        
        $this->settings = $this->getSettings();
    }

    /**
     * Возвращает настройки модуля.
     * 
     * @see Module::$settings
     * 
     * @return Config
     */
    public function getSettings(): ?Config
    {
        if (!isset($this->settings)) {
            $this->settings = new Config($this->basePath . DS . 'config' . DS . '.settings.php', true);
        }
        return $this->settings;
    }

    /**
     * {@inheritdoc}
     */
    public function isNewRecord(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getDirtyAttributes(array $names = null): array
    {
        if ($names === null) {
            $names = array_keys($this->attributes);
        }

        $attributes = [];
        if ($this->oldAttributes === null) {
            foreach ($names as $name) {
                if (isset($this->attributes[$name])) {
                     $attributes[$name] = $this->attributes[$name];
                }
            }
        } else {
            foreach ($names as $name) {
                if (isset($this->attributes[$name]) && isset($this->oldAttributes[$name])) {
                $attributes[$name] = $this->attributes[$name];
                }
            }
        }
        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    protected function updateProcess(array $attributes = null): false|int
    {
        if (!$this->beforeSave(false)) {
            return false;
        }
        // возвращает только те атрибуты, которые были изменены
        $dirtyAttributes = $this->getDirtyAttributes($attributes);
        if (empty($dirtyAttributes)) {
            $this->afterSave(false);
            return 0;
        }
        // возвращает атрибуты без псевдонимов (если они были указаны)
        $columns = $this->unmaskedAttributes($dirtyAttributes);
        $this->beforeUpdate($columns);

        // сохранение настроек модуля
        $this->saveSettings($columns);
        $this->setOldAttributes($this->attributes);
        $this->afterSave(false, $columns);
        return 1;
    }

    /**
     * Сохраняет настройки модуля.
     * 
     * @param array $parameters Параметры настроек модуля.
     * 
     * @return void
     */
    public function saveSettings(array $parameters): void
    {
        $this->settings
            ->setAll($parameters)
            ->save();
    }

    /**
     * Загружает настройки модуля в атрибуты модели.
     * 
     * @return null|WidgetSettingsModel
     */
    public function selectFromSettings(): ?static
    {
        $row = $this->settings->getAll();
        if ($row) {
            $this->reset();
            $this->afterSelect();
            $this->populate($this, $row);
            $this->afterPopulate();
            return $this;
        } else
            return null;
    }

    /**
     * {@inheritdoc}
     */
    public function get(mixed $identifier = null): ?static
    {
        return $this->selectFromSettings();
    }
}
