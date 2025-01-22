<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\User;

use Gm;
use DateTimeZone;
use Gm\Panel\Http\Response;
use Gm\User\User as BaseUser;
use Gm\Exception\ForbiddenHttpException;

/**
 * {@inheritdoc}
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\User
 * @since 2.0
 */
class User extends BaseUser
{
    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this->loginUrl = [Gm::alias('@backend')];
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeZone(): DateTimeZone
    {
        static $timeZone;

        if ($timeZone === null) {
            /** @var UserIdentity $identity */
            $identity = $this->getIdentity();
            if ($identity) {
                $timeZone = $identity->getProfile()->timeZone;
            }
            if (empty($timeZone)) {
                $timeZone = date_default_timezone_get();
            }
            $timeZone = new \DateTimeZone($timeZone);
        }
        return $timeZone;
    }

    /**
     * {@inheritdoc}
     */
    public function isGuest(): bool
    {
        return !Gm::hasUserIdentity(BACKEND_SIDE_INDEX);
    }

    /**
     * {@inheritdoc}
     */
    public function loginRequired(): void
    {
        // если запрос из панели управления
        if (Gm::$app->request->isGjax()) {
            // создаем ответ, который будет передан в {@see \Gm\ErrorHandler\WebErrorHandler::_renderException()}
            Gm::$app->response
                ->setFormat(Response::FORMAT_JSONG)
                ->meta
                    ->cmdRedirect($this->loginUrl());
        } else {
            throw new ForbiddenHttpException(Gm::t('app', 'You are not allowed to perform this action'));
        }
    }
}
