<?php
/**
 * GM Framework
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Behavior;

use Gm;
use Closure;
use Gm\Stdlib\Behavior;
use Gm\Stdlib\Component;
use Gm\Mvc\Controller\BaseController;

/**
 * AuditBehavior - это поведение для аудита действий пользователей.
 *
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Behavior
 * @since 1.0
 */
class AuditBehavior extends Behavior
{
    /**
     * Правило для проверки доступности действия.
     * 
     * Пример, запись действий аудита будет доступна:
     * - для всех действий контроллера
     * ```php
     * return [
     *     'audit' => [
     *         // ...
     *         'allowed' => '*',
     *         // ...
     *     ]
     * ]
     * ```
     * - для указанных действий контроллера
     * ```php
     * return [
     *     'audit' => [
     *         // ...
     *         'allowed' => ['', 'view', 'delete'],
     *         // ...
     *     ]
     * ]
     * ```
     * 
     * @see AuditBehavior::isAllowed()
     * 
     * @var array|string
     */
    public array|string|null $allowed = null;

    /**
     * Правило для проверки недоступности действия.
     * 
     * Пример, запись действий аудита будет не доступна:
     * - для всех действий контроллера
     * ```php
     * return [
     *     'audit' => [
     *         // ...
     *         'deny' => '*',
     *         // ...
     *     ]
     * ]
     * ```
     * - для указанных действий контроллера
     * ```php
     * return [
     *     'audit' => [
     *         // ...
     *         'deny' => ['', 'view', 'delete'],
     *         // ...
     *     ]
     * ]
     * ```
     * 
     * @see AuditBehavior::isDeny()
     * 
     * @var array|string
     */
    public array|string|null $deny = null;

    /**
     * Замыкание возвращающие комментарий действия пользователя.
     *
     * Устанавливается в том случаи если {@see \Gm\Panel\Audit\Info::getComment()} не 
     * возвращает должный результат.
     * 
     * @see \Gm\Panel\Audit\Info::$commentCallback
     * 
     * @var Closure
     */
    public ?Closure $commentCallback = null;

    /**
     * Поведение включено / выключено.
     * 
     * Если значение `false`, аудит действий пользователя будет не доступен.
     * Если значеие `true` или `null`, работа поведения зивист от службы аудита
     * действий пользователей {@see \Gm\Panel\Audit\Audit}.
     * 
     * @var bool
     */
    public ?bool $enabled = null;

    /**
     * Имена разделов атрибутов информации, которые будет записаны в журнал аудита.
     * 
     * Доступны следующие разделы:
     * - `user`, информация о пользователе;
     * - `controller`, информация о контроллере;
     * - `module`, информация о модуле;
     * - `request`, информация о запросе пользователя;
     * - `device`, информация об устройстве пользователя.
     *
     * @see \Gm\Panel\Audit\Audit::$properties
     * 
     * @var array<int, string>
     */
    public array $auditSections = [];

    /**
     * Служба аудита.
     * 
     * @var Component|Object
     */
    protected Component $audit;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->audit = Gm::$services->getAs('audit');
        if ($this->auditSections) {
            $this->audit->sections = $this->auditSections;
        }
        // если в настройках поведения установлен параметр `enabled = true` или 
        // параметр не указан, то работа поведения зависит от доступности 
        // службы аудита
        if ($this->enabled === null || $this->enabled === true) {
            $this->enabled = $this->audit->enabled;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attach(Component $owner): void
    {
        if ($this->enabled) {
            $this->owner = $owner;
            $owner->on(Component::EVENT_AFTER_RUN, [$this, 'beforeAudit']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detach(): void
    {
        if ($this->owner && $this->enabled) {
            $this->off(Component::EVENT_AFTER_RUN, [$this, 'beforeAudit']);
            $this->owner = null;
        }
    }

   /**
     * Выполняет запись действий пользователя в журнал аудита.
     * 
     * @see \Gm\Panel\Audit\Audit::write()
     * 
     * @return void
     */
    public function auditAction(): void
    {
        if ($this->enabled) {
            if ($this->commentCallback instanceof Closure) {
                $this->audit->info->commentCallback = $this->commentCallback;
            }
            $this->audit->write();
        }
    }

   /**
     * Проверяет недоступность действия.
     * 
     * Проверяет действие согласно правилу {@see AuditBehavior::$deny}. Если 
     * в правило попадает указанное действие, то оно недоступно.
     * 
     * @param string $actionName Действие контроллера.
     * 
     * @return bool Если значение `true`, указанное действие контроллера не доступно.
     */
    public function isDeny(string $actionName): bool
    {
        if (empty($this->deny)) return true;

        if (is_string($this->deny)) {
            return $this->deny === $actionName || $this->deny === '*';
        }

        if (is_array($this->deny)) {
            return in_array($actionName, $this->deny);
        }
        return false;
    }

   /**
     * Проверяет доступность действия.
     * 
     * Проверяет действие согласно правилу {@see AuditBehavior::$allowed}. Если 
     * в правило попадает указанное действие, то оно доступно.
     * 
     * @param string $actionName Действие контроллера.
     * 
     * @return bool Если значение `true`, указанное действие контроллера доступно.
     */
    public function isAllowed(string $actionName): bool
    {
        if (empty($this->allowed)) return true;

        if (is_string($this->allowed)) {
            return $this->allowed === $actionName || $this->allowed === '*';
        }

        if (is_array($this->allowed)) {
            return in_array($actionName, $this->allowed);
        }
        return false;
    }

    /**
     * Событие выполняемое перед запуском контроллера.
     * 
     * @param BaseController $controller Контроллер события.
     * @param string $actionName Имя действия контроллера в событии.
     * 
     * @return void
     */
    public function beforeAudit(BaseController $controller, string $actionName): void
    {
        if ($this->deny) {
            if (!$this->isDeny($actionName)) {
                $this->auditAction();
            }
        } elseif ($this->allowed) {
            if ($this->isAllowed($actionName)) {
                $this->auditAction();
            }
        }
    }
}
