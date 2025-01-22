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

/**
 * Изображение профиля пользователя.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\User
 * @since 1.0
 */
class UserProfilePicture
{
    /**
     * Базовый URL-путь изображения профиля.
     * 
     * @var string
     */
    public string $baseUrl = '';

    /**
     * Базовый URL-путь изображения профиля по умолчанию.
     * 
     * @var string
     */
    public string $defaultUrl = '';

    /**
     * Абсолютный путь к изображениям профиля.
     * 
     * @var string
     */
    public string $basePath = '';

    /**
     * Абсолютный путь к изображениям профиля по умолчанию.
     * 
     * @var string
     */
    public string $defaultPath = '';

    /**
     * Профиль пользователя.
     *
     * @var UserProfile
     */
    protected UserProfile $_profile;

    /**
     * Свойство профиля пользователя определяющие его изображение.
     *
     * @var string
     */
    protected string $name = 'photo';

    /**
     * Расширение файла изображения профиля пользователя по умолчанию.
     * 
     * @see UserProfilePicture::defineSource()
     * 
     * @var string
     */
    protected string $defaultExtension = 'svg';

    /**
     * Изображение профиля пользователя по умолчанию.
     *
     * @var array
     */
    protected array $default = [
        // изображение мользователя (ж)
        [
            // изображение по умолчанию
            [
                'user-none-f_small', //уменьшенный размер
                'user-none-f' // оригинальный размер
            ],
            // изображение повреждено
            [
                'user-none-f_br_small', //уменьшенный размер
                'user-none-f_br' // оригинальный размер
            ]
        ],
        // изображение мользователя (м)
        [
            // изображение по умолчанию
            [
                'user-none_small', // уменьшенный размер
                'user-none' // оригинальный размер
            ],
            // изображение повреждено
            [
                'user-none_br_small', // уменьшенный размер
                'user-none_br' // оригинальный размер
            ]
        ]
    ];

    protected $source;

    /**
     * Конструктор класса.
     *
     * @param UserProfile $profile Профиль пользователя.
     * 
     * @return void
     */
    public function __construct(UserProfile $profile)
    {
        $this->_profile = $profile;
        $this->baseUrl  = Gm::$app->uploader->getUserUrl();
        $this->basePath =  Gm::$app->uploader->getUserPath();
        $this->defaultUrl = Gm::$app->theme->url . '/assets/icons/svg/access/';
        $this->defaultPath = Gm::$app->theme->path . '/assets/icons/svg/access/';
    }

    /**
     * Устанавливает изображение профиля пользователя по умолчанию.
     *
     * @var void
     */
    public function setDefault(array $default)
    {
        $this->default = $default;
    }

    /**
     * Возвращает изображение профиля пользователя по умолчанию.
     *
     * @var array Изображение профиля пользователя по умолчанию.
     */
    public function getDefault(): array
    {
        return $this->default;
    }

    /**
     * Проверяет, имеет ли профиль пользователя изображение.
     *
     * @var bool Если true, профиль пользователя имеет изображение.
     */
    public function isEmpty(): bool
    {
        return empty($this->_profile->{$this->name});
    }

    /**
     * Проверяет, повреждён ли ресурс изображения профиля пользователя.
     *
     * @var bool Если true, ресурс изображения повреждён.
     */
    public function isBroken()
    {
        if ($this->source === null) {
            return $this->getSource()['broken'];
        }
        return $this->source['broken'];
    }

    /**
     * Проверяет, является ли ресурс изображения профиля пользователя ресурсом 
     * по умолчанию.
     *
     * @return bool Если true, ресурс изображения является ресурсом по умолчанию.
     */
    public function isDefault()
    {
        if ($this->source === null) {
            return $this->getSource()['default'];
        }
        return $this->source['default'];
    }

    /**
     * Возвращает ресурс изображения профиля пользователя.
     * 
     * @param bool $fullSize Если значение `true`, то ресурс содержит исходный размер 
     *     изображения (по умолчанию `true`).
     * 
     * @return array{
     *     name: string|null,
     *     path: string,
     *     url: string,
     *     default: bool,
     *     broken: bool
     * } Параметры ресурса изображения:
     *     - 'name' (string|null) Имя файла без расширения;
     *     - 'path' (string) Файл с указанием пути;
     *     - 'url' (string) URL-адрес изображения;
     *     - 'default' (bool) Изображение по умолчанию;
     *     - 'broken' (bool) Изображение повреждёно.
     */
    public function getSource(bool $fullSize = true): array
    {
        if ($this->source === null) {
            $this->source = $this->defineSource($this->_profile->{$this->name}, (int) $this->_profile->gender, $fullSize);
        }
        return $this->source;
    }

    /**
     * Определяет ресурс изображения профиля пользователя из указанных параметров.
     * 
     * @param string $filename Имя файла без пути.
     * @param int $gender Пол.
     * @param bool $fullSize Если значение `true`, то ресурс содержит исходный размер 
     *     изображения.
     * 
     * Ресурс изображения:
     * 
     * @return array array<int, array{name: string, type: string, active: bool}> 
     */
    public function defineSource($filename, int $gender = 0, bool $fullSize = true): array
    {
        // если файл изображения указан
        if ($filename) {
            $path = $this->basePath . '/' . $filename;
            // если файл изображения существует
            if (file_exists($path)) {
                return [
                    'filename' => $filename,
                    'name'     => null,
                    'path'     => $path,
                    'url'      => $this->baseUrl . '/' . $filename,
                    'default'  => false,
                    'broken'   => false
                ];
            // если файл изображения не существует
            } else {
            if ($gender !== 0 && $gender !== 1) $gender = 0;
                $name     = $this->default[$gender][1][(int) $fullSize] ?? $this->default[0][0][1];
                $filename = $name . '.' . $this->defaultExtension;
                return [
                    'filename' => $filename,
                    'name'     => $name,
                    'path'     => $this->defaultPath . DS . $filename,
                    'url'      => $this->defaultUrl . '/' . $filename,
                    'default'  => true,
                    'broken'   => true
                ];
            }
        // если файл изображения не указан
        } else {
            if ($gender !== 0 && $gender !== 1) $gender = 0;
            $name = $this->default[$gender][0][(int) $fullSize] ?? $this->default[0][0][1];
            $filename = $name . '.' . $this->defaultExtension;
            return [
                'filename' => $filename,
                'name'     => $name,
                'path'     => $this->defaultPath . DS . $filename,
                'url'      => $this->defaultUrl . '/' . $filename,
                'default'  => true,
                'broken'   => false
            ];
        }
    }

    /**
     * Возвращает имя файла изображения (включая путь к файлу).
     * 
     * @param bool $fullpath Если значение `true`, добавляет путь к файлу.
     * @param bool $fullSize Если значение `true`, учитывает исходный размер 
     *     изображения.
     * 
     * @return string Имя файла изображения.
     */
    public function getFilename(bool $fullpath = true, bool $fullSize = true): string
    {
        $src = $this->getSource($fullSize);
        return $fullpath ? $src['path'] : $src['filename'];
    }

    /**
     * Возвращает URL-адрес изображения.
     * 
     * @param bool $fullSize Если значение `true`, то учитывает исходный размер 
     *     изображения.
     * 
     * @return string URL-адрес изображения.
     */
    public function getUrl(bool $fullSize = true): string
    {
        $src = $this->getSource($fullSize);
        return $src['url'];
    }
}
