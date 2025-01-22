<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Http;

use Gm;
use Gm\Http\Response as BaseResponse;

/**
 * Класс HTTP-ответа Панели управления.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Http
 * @since 1.0
 */
class Response extends BaseResponse
{
    /**
     * @var string Формат ответа 'jsong'.
     */
    public const FORMAT_JSONG = 'jsong';

    /**
     * {@inheritdoc}
     */
    public array $formatters = [
        self::FORMAT_JSONG  => '\Gm\Panel\Http\Response\JsongResponseFormatter'
    ];

    /**
     * {@inheritdoc}
     */
    public function defineFormat(): static
    {
        /** @var \Gm\Http\Request $request */
        $request = Gm::$app->request;

        if ($request->isConsole()) {
            $this->setFormat(self::FORMAT_RAW);
        } elseif ($request->isAjax()) {
            if ($request->IsPjax()) {
                $this->setFormat(self::FORMAT_JSONP);
            }
            elseif ($request->IsGjax()) {
                $this->setFormat(self::FORMAT_JSONG);
            } else {
                $this->setFormat(self::FORMAT_JSON);
            }
        } else
            $this->setFormat(self::FORMAT_HTML);
        return $this;
    }
}
