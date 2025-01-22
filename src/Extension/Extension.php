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

/**
 * Класс расширения модуля Панели управления.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Extension
 * @since 1.0
 */
class Extension extends \Gm\Mvc\Extension\Extension
{
    /**
     * Кэширование данных расширения модуля, получаемых при запросе к базе данных и 
     * возвращаемых клиенту.
     * 
     * Каждый контроллер расширения модуля самостоятельно принимает решение о кэширование 
     * своих данных или использует свойство расширения модуля {@see Extension::$caching}.
     * 
     * Если значение `null`, кэширование данных расширения модуля определяется службой 
     * кэширования {@see \Gm\Cache\Cache}. Если служба не доступна (отключена), кэширование 
     * выполняться не будет.
     * 
     * Кэширование может быть установлено параметром 'caching' в конфигурации расширения модуля.
     * 
     * @var bool|null
     */
    public ?bool $caching = null;

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class'    => '\Gm\Filter\AccessControl',
                'autoInit' => true,
                'rules'    => $this->getConfigParam('accessRules'),
                // ВАЖНО: чтобы иметь доступ к расширению, необходимо указать модулю 
                // разрешение "extension" 
                'checkParentAccess' => true,
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function initCaching(): void
    {
        $this->caching = $this->config->caching;
        if ($this->caching === null)
            $this->caching = Gm::$app->cache->enabled;
        else
            // если служба кэширования не доступна, кэширование не будет выполняться
            $this->caching = !Gm::$app->cache->enabled ? false : $this->caching;
    }

    /**
     * {@inheritdoc}
     */
    public function getIconUrl(string $suffix = ''): string
    {
        return $this->getAssetsUrl() . '/images/icon' . $suffix . '.svg';
    }
}
