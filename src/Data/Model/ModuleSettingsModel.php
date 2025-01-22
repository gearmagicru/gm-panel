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
use Gm\Panel\Data\Model\FormModel;

/**
 * Модель данных настроек модуля (при взаимодействии с представлением, использующий 
 * компонент формы Gm.view.form.Panel GmJS).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Data\Model
 * @since 1.0
 */
class ModuleSettingsModel extends FormModel
{
    /**
     * Настройки модуля.
     * 
     * @var Config
     */
    protected Config $settings;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this->settings = $this->getModuleSettings();
    }

    /**
     * Возвращает настройки модуля.
     * 
     * @return Config
     */
    public function getModuleSettings(): Config
    {
        return $this->module->getSettings();
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
        if ($names === null)
            $names = array_keys($this->attributes);
        $attributes = array();
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
     * @return null|ModuleSettingsModel
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
