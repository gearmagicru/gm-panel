<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Version;

use  Gm\Version\BaseVersion;

/**
 * Версия панели управления.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Version
 * @since 1.0
 */
class Version extends BaseVersion
{
    /**
     * {@inheritdoc}
     */
    public string $number = '1.0';

    /**
     * {@inheritdoc}
     */
    public string $name = 'GM Panel';

    /**
     * {@inheritdoc}
     */
    public string $resource = 'https://apps.gearmagic.ru/gmpanel';

    /**
     * {@inheritdoc}
     */
    public string $date = '07/07/2017';
}
