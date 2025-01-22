<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Controller;

use Gm;
use Gm\Panel\Widget\SettingsWindow;
use Gm\Panel\Controller\FormController;

/**
 * Контроллер реализующий представление в виде формы настроек модуля с 
 * последующей их обработкой.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Controller
 * @since 1.0
 */
class ModuleSettingsController extends FormController
{
    /**
     * {@inheritdoc}
     */
    protected string $defaultModel = 'Settings';

    /**
     * {@inheritdoc}
     */
    public function translateAction(mixed $params, string $default = null): ?string
    {
        switch ($this->actionName) {
            // вывод интерфейса
            case 'view':
            // просмтор найстроек
            case 'data':
                return Gm::t(BACKEND, "{{$this->actionName} settings action}");

            default:
                return parent::translateAction(
                    $params,
                    $default ?: Gm::t(BACKEND, "{{$this->actionName} settings action}")
                );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createWidget(): SettingsWindow
    {
        return new SettingsWindow();
    }
}
