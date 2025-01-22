<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Extension;

use Gm;
use Gm\Data\Model\BaseModel;
use Gm\Helper\Str;

/**
 * Модель данных вывода расширений в панель навигации.
 * 
 * Для вывода элементов в панель навигации, необходимо, чтобы параметр 'expandable' 
 * в конфигурации модуля имел значение `true`.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Extension
 * @since 1.0
 */
class ExtensionNavigation extends BaseModel
{
    /**
     * Идентификатор модуля расширения.
     * 
     * @see ExtensionNavigation::configure()
     * 
     * @var string
     */
    public string $id;

    /**
     * Количество символов в названии расширения.
     * 
     * @see ExtensionNavigation::getNodes()
     * 
     * @var int
     */
    public int $textLength = 27;

    /**
     * Возвращает элементы (расширения модуля) панели навигации.
     * 
     * @param bool $accessible Если значение `true`, проверит доступность роли 
     *     пользователя к расширению модуля.
     * 
     * @return array
     */
    public function getNodes(bool $accessible = true): array
    {
        $nodes = [];
        // если не указан идентификатор модуля
        if (empty($this->id)) {
            return $nodes;
        }

        /** @var \Gm\ModuleManager\ModuleRegistry $installed Установленные модули */
        $installed = Gm::$app->modules->getRegistry();

        /** @var null|array$ moduleConfig Параметры конфигурации установленного модуля */
        $moduleConfig = $installed->get($this->id);
        if (empty($moduleConfig)) {
            return $nodes;
        }
        $moduleRowId = $moduleConfig['rowId'];

        /** @var \Gm\ExtensionManager\ExtensionRegistry $installed Установленные расширения */
        $installed = Gm::$app->extensions->getRegistry();
        $extensions = $installed->getListInfo(true, $accessible);
        foreach ($extensions as $id => $extension) {
            // если расширение не доступно
            if (!$extension['enabled']) continue;
            // получить расширение только для указанного модуля 
            if ($extension['moduleRowId'] == $moduleRowId) {
                $nodes[] = [
                    'text'        => Str::ellipsis($extension['name'], 0, $this->textLength),
                    'description' => $extension['description'],
                    'icon'        => $extension['smallIcon'],
                    'handler'     => 'loadWidget',
                    'widgetUrl'   => Gm::alias('@backend', '/' . $extension['baseRoute']),
                    'leaf'        => true
                ];
            }
        }
        return $nodes;
    }
}