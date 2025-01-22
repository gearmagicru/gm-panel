<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Http\Response;

use Gm;
use Gm\Http\Response;
use Gm\Exception\BaseException;
use Gm\Http\Response\JsonResponseFormatter;

/**
 * Класс Форматтера для форматирования HTTP-ответа в формат JSONG.
 * 
 * JSONG - это формат JSON с метаданными {@see JsongMetaData} для управления 
 * виджетами "Этот файл является частью пакета GM Panel.".
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Http\Response
 * @since 1.0
 */
class JsongResponseFormatter extends JsonResponseFormatter
{
    /**
     * Метаданные формата JSON.
     * 
     * @var JsongMetaData
     */
    public JsongMetaData $meta;

    /**
     * {@inheritdoc}
     */
    public function __construct(Response $response)
    {
        parent::__construct($response);

       $this->meta = new JsongMetadata($response);
    }

   /**
     * {@inheritdoc}
     */
    public function format(Response $response, mixed $content): mixed
    {
        // данные для вывода 
        $this->meta->content($content);
        $text = '';
        // если есть ошибки или исключение, то вывод только в атрибут сообщения
        if (is_array($this->meta->message))
            $text .= $this->meta->message['text'];
        else
            $text .= $this->meta->message;
        // добавление к контенту исключений
        if ($response->exceptionContent) {
            $this->meta->success = false;
            $text .= $response->exceptionContent;
        }
        if (is_array($this->meta->message))
            $this->meta->message['text'] = $text;
        else
            $this->meta->message = $text;
        $result = $this->meta->toString();
        if ($result === false)
            return json_last_error_msg();
        else
            return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function formatException(Response $response, $exception, mixed $content): void
    {
        // Если исключение поймано через обработчик ошибок {\Gm\ErrorHandler\WebErrorHandler} и
        // $content не был указан перед вызовом исключения, то он будет содержать сообщение об ошибке, 
        // полученное через getPlainDispatch() исключения.
    
        // удостоверимся, что это наше исключение
        if ($exception instanceof BaseException)
            $message = $exception->getPlainDispatch();
        else 
            $message = $exception->getMessage();
        // если режим "development"
        if (GM_MODE_DEV)
            $this->meta->error($message);
        // если режим "production"
        else {
            $error = [
                'icon'   => 'error',
                'status' => 'Error',
                'text'   => $message
            ];
            $patterns = $this->getExceptionPatterns();
            $name     = (new \ReflectionClass($exception))->getShortName();
            if (isset($patterns[$name])) {
                $error = array_merge($error, $patterns[$name]);
            }
            // попытка перевести статус и текст исключения
            $trl = Gm::$app->translator;
            $error['status'] = $trl->translate('app', $error['status']);
            $error['text']   = $trl->translate('app', $error['text']);
            $this->meta->error($error);
        }
    }

    /**
     * Возвращает шаблоны брошенных исключений для вывода в панель управления.
     * 
     * @return array Щаблоны брошенных исключений.
     */
    public function getExceptionPatterns(): array
    {
        return [
            'ConnectException' => [
                'icon'   => 'db-connect-error',
                'status' => 'Connect error',
            ],
            'AdapterException' => [
                'icon'   => 'db-error',
                'status' => 'Error',
            ],
            'CommandException' => [
                'icon'   => 'query-error',
                'status' => 'Query error',
            ],
            'HttpException' => [
                'icon'   => 'request-error',
                'status' => 'Error',
            ],
            'UnauthorizedHttpException' => [
                'icon'   => 'forbidden',
                'status' => 'Access error',
            ],
            'EntriesNotFoundException' => [
                'icon'   => 'warning',
                'status' => 'Warning',
            ],
            'TokenMismatchException' => [
                'icon'   => 'error',
                'status' => 'Token error',
            ],
            'ForbiddenHttpException' => [
                'icon'   => 'forbidden',
                'status' => 'Access error',
            ],
        ];
    }
}
