<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Panel\Data\Model;

use Gm;
use SplFileInfo;
use Gm\Filesystem\Finder;
use Gm\Filesystem\Filesystem as Fs;

/**
 * Модель формирования массива файлов и папок.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Data\Model
 * @since 1.0
 */
class FilesGridModel extends BaseGridModel
{
    /**
     * @var string Тип элемента "папка".
     */
    public const TYPE_FOLDER = 'folder';

    /**
     * @var string Тип элемента "файл".
     */
    public const TYPE_FILE = 'file';

    /**
     * @var string Атрибут, идентификатор строки сетки.
     */
    public const ATTR_ID = 'id';

    /**
     * @var string Атрибут, название файла.
     */
    public const ATTR_NAME = 'name';

    /**
     * @var string Атрибут, относительное название файла (включает локальный путь).
     */
    public const ATTR_RNAME = 'relName';

    /**
     * @var string Атрибут, тип файла.
     */
    public const ATTR_TYPE = 'type';

    /**
     * @var string Атрибут, время последнего доступа к файлу.
     */
    public const ATTR_ACTIME = 'acTime';

    /**
     * @var string Атрибут, время последнего изменения индексного дескриптора файла.
     */
    public const ATTR_CHTIME = 'chTime';

    /**
     * @var string Атрибут, права доступа к файлу.
     */
    public const ATTR_PERMS = 'perms';

    /**
     * @var string Атрибут, размер файла.
     */
    public const ATTR_SIZE = 'size';

    /**
     * @var string Атрибут, тип MIME-файла.
     */
    public const ATTR_MIME = 'mime';

    /**
     * Атрибуты файла.
     * 
     * Каждая строка сетки возвращает значения указанных атрибутов.
     * Где ключ - название ваших атрибутов.
     *
     * @var array
     */
    public array $attributes = [
        self::ATTR_ID      => 'id',
        self::ATTR_NAME    => 'name',
        self::ATTR_RNAME   => 'relName',
        self::ATTR_TYPE    => 'type',
        self::ATTR_ACTIME  => 'acTime',
        self::ATTR_CHTIME  => 'chTime',
        self::ATTR_PERMS   => 'perms',
        self::ATTR_SIZE    => 'size',
        self::ATTR_MIME    => 'mime',
    ];

    /**
     * Применять атрибут "Права доступа".
     * 
     * @var bool
     */
    public bool $usePermsAttr = false;

    /**
     * Применять атрибут "Последний доступ".
     * 
     * @var bool
     */
    public bool $useAccessTimeAttr = false;

    /**
     * Применять атрибут "Последнее обновление".
     * 
     * @var bool
     */
    public bool $useChangeTimeAttr = false;

    /**
     * Применять атрибут "MIME".
     * 
     * @var bool
     */
    public bool $useMimeAttr = true;

    /**
     * Применять атрибут "Размер".
     * 
     * @var bool
     */
    public bool $useSizeAttr = false;

    /**
     * Текущая путь к файлам.
     * 
     * @var string|null
     */
    public ?string $path = null;

    /**
     * Параметр передаваемый HTTP-запросом, указывающий на текущий путь к файлам.
     * 
     * Параметр передаётся с помощью метода POST и определяется {@see BaseGridModel::definePath()}.
     * Если значение параметра `false`, тогда будет применяться значение {@see BaseGridModel::$defaultPath}.
     * 
     * @var string|false
     */
    public string|false $pathParam = 'path';

    /**
     * Определяет, что парамтер $path получен из HTTP-запроса.
     * 
     * @see BaseGridModel::definePath()
     * 
     * @var bool
     */
    protected bool $hasPath = false;

    /**
     * Значение текущего путя к файлам.
     * 
     * Используется в том случаи, если значение параметра {@see BaseGridModel::$pathParam} 
     * отсутствует в HTTP-запросе.
     * 
     * @var string
     */
    public string $defaultPath = '';

    /**
     * Показывать файлы VCS.
     * 
     * @var bool
     */
    public bool $showVCSFiles = true;

    /**
     * Показывать файлы и папки с точкой.
     * 
     * @var bool
     */
    public bool $showDotFiles = true;

    /**
     * Показывать папки без доступа.
     * 
     * @var bool
     */
    public bool $showUnreadableDirs = true;

    /**
     * Показывать только файлы.
     * 
     * @var bool
     */
    public bool $showOnlyFiles = false;

    /**
     * Если фильтр применялся.
     * 
     * @see FilesGridModel::filterRowsQuery
     * 
     * @var bool
     */
    public bool $isFiltered = false;

    /**
     * Форматтер.
     * 
     * @var null|\Gm\I18n\Formatter
     */
    protected $formatter;

    /**
     * Абсолютный путь к файлам из HTTP-запроса.
     * 
     * @var null|string|false
     */
    protected $realPath;

    /**
     * @var string
     */
    protected string $basePath = '@home';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this->path = $this->definePath();
        $this->realPath = $this->defineRealPath();
        $this->basePath = $this->defineBasePath();
        $this->formatter = Gm::$app->formatter;
    }

    /**
     * @return string
     */
    public function defineBasePath(): string
    {
        return Gm::getAlias($this->basePath);
    }

    /**
     * Определяет абсолютный путь из указанного пути в HTTP-запросе.
     * 
     * @see FilesGridMode::definePath()
     * 
     * @return false|string
     */
    public function defineRealPath(): false|string
    {
        return $this->getSafePath($this->definePath());
    }

    /**
     * Определяет текущий путь к файлам.
     * 
     * - если значение указано в параметрах конфигурации, тогда возвратит {@see BaseGridModel::$path};
     * - если не указан параметр запроса или сам параметр отсутствует в запросе, 
     * тогда возвратит {@see BaseGridModel::$defaultPath};
     * - если значение параметра является не допустимым, 
     * тогда возвратит {@see BaseGridModel::$defaultPath}.
     * 
     * @see BaseGridModel::$path
     * 
     * @return string
     */
    protected function definePath(): string
    {
        // если значение указано в параметрах конфиграции
        if ($this->path !== null) {
            return $this->path;
        }

        if ($this->dataManager && isset($this->dataManager->path))
            $defaultPath = $this->dataManager->path;
        else
            $defaultPath = $this->defaultPath;

        // если запрещено получать значение из HTTP-запроса
        if ($this->pathParam === false) {
            return $defaultPath;
        }

        $path = Gm::$app->request->getPost($this->pathParam, null);
        if (empty($path)) {
            return $defaultPath;
        }
        // параметр был получен из запроса
        $this->hasPath = true;
        return $path;
    }

    /**
     * Возвращает абсолютный путь для указанной папки или файла.
     * 
     * Например: 
     *     - 'upload/images' => '/home/www/site/upload/images'; 
     *     - 'upload/images/image.jpg' => '/home/www/site/upload/images/image.jpg'.
     * 
     * @param string $path Папка или файл.
     * 
     * @return false|string Возвращает `false`, если указанная директории или файл 
     *     не существует.
     */
    public function getSafePath(string $path)
    {
        return Gm::getSafePath($path);
    }

    /**
     * {@inheritdoc}
     * 
     * @return Finder|null Искатель файлов и папок.
     */
    public function getRowsBuilder(): Finder
    {
        if ($this->rowsBuilder === null) {
            $this->rowsBuilder = Finder::create();
        }
        return $this->rowsBuilder;
    }

    /**
     * {@inheritdoc}
     *
     * @param Finder $builder Искатель файлов и папок.
     *
     * @return Finder|null
     */
    public function buildQuery($builder)
    {
        if (empty($this->realPath)) return null;

        // показывать папки без доступа
        if (!$this->showUnreadableDirs) {
            $builder->ignoreUnreadableDirs();
        }

        $builder
            ->ignoreVCS(!$this->showVCSFiles)
            ->ignoreVCSIgnored(!$this->showVCSFiles)
            ->ignoreDotFiles(!$this->showDotFiles);

        $this->filterRows($builder);
        $this->sortRows($builder);
        return $builder;
    }

    /**
     * Выполняет фильтрацию строк.
     * 
     * @param Finder|null $builder Поисковик файлов и папок.
     * 
     * @return void
     */
    public function filterRows(?Finder $builder)
    {
        if ($builder) {
            if ($this->directFilter) {
                $filter = [];
                // переводим параметры фильтра в пары 'ключ - значение'
                foreach ($this->directFilter as $params) {
                    $filter[$params['property']] = $params['value'];
                }
                $this->isFiltered = $this->filterRowsQuery($builder, $filter);
            } else {
                // если фильтр не применялся, выводим текущий список файлов и папок
                $builder->in($this->realPath)->depth('== 0');
            }
        }
    }

    /**
     * формирует и выполняет запрос фильтрации строк.
     * 
     * @param Finder $builder Поисковик файлов и папок.
     * @param array $filter Фильтра в виде пар 'ключ - значение'.
     * 
     * @return bool Возвращает значение `true` если фильтрация строк выполнена.
     */
    public function filterRowsQuery(Finder $builder, array $filter): bool
    {
        return false;
    }

    /**
     * Выполняет сортировку атрибутов файла в строках.
     *
     * @param Finder $builder Поисковик файлов и папок.
     * @param null|string $name Имя атрибута {@see FilesGridModel::$attributes} (по умолчанию `null`).
     * @param null|string $direction Порядок сортировки {@see BaseGridModel::SORT_ASC}, 
     *     {@see BaseGridModel::SORT_DESC} (по умолчанию `null`).
     *
     * @return Finder|null
     */
    public function sortRows(Finder $builder, string $name = null, string $direction = null)
    {
        if (!$builder->searched) {
            return null;
        }

        /** @var null|array $order */
        $order = $this->getOneOrder();

        $name = $name ?: ($order ? $order[0] : null);
        $direction = $direction ?: ($order ? $order[1] : null);

        if (empty($name)) return;

        $attribute = null;
        foreach ($this->attributes as $attribute => $nameAttr) {
            if ($nameAttr === $name) {
                $attribute = $nameAttr;
                break;
            }
        }

        switch ($attribute) {
            case self::ATTR_NAME: $builder->sortByName(); break;
            case self::ATTR_TYPE: $builder->sortByType(); break;
            case self::ATTR_ACTIME: $builder->sortByAccessedTime(); break;
            case self::ATTR_CHTIME: $builder->sortByChangedTime(); break;
            default:
                $attribute = null;
        }

        if ($attribute) {
            if ($direction === self::SORT_ASC) {
                $builder->reverseSorting();
                return [$attribute, $direction];
            } else
                return [$attribute, self::SORT_DESC];
        }
        return null;
    }

    /**
     * {@inheritdoc}
     * 
     * @param Finder|null $receiver Искатель файлов и папок.
     * 
     * @return int
     */
    public function getTotalRows($receiver = null): int
    {
        return $receiver && $receiver->searched ? $receiver->count() : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountRows(): int
    {
        $builder = $this->getRowsBuilder();
        return $builder && $builder->searched ? $builder->count() : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchRows($receiver): array
    {
        if (empty($receiver) || !$receiver->searched) return [];

        $rows = [];
        $index = 0;

        /** @var SplFileInfo $file */
        foreach ($receiver as $file) {
            $row = $this->fetchFile($file);

            if ($row === null) continue;

            // показывать только файлы
            if ($this->showOnlyFiles && $file->isDir()) continue;

            $index++;
            if ($index <= $this->rangeBegin) continue;
            if ($index > $this->rangeEnd) break;

            $rows[] = $this->fetchRow($row, $file);
        }
        return $rows;
    }

    /**
     * Получает базовую информацию о файле.
     *
     * @param SplFileInfo $file Информация о текущем файле.
     * 
     * @return array|null
     */
    public function fetchFile(SplFileInfo $file): ?array
    {
        $attr = &$this->attributes;
        // имя файла, например 'file.jpg'
        $name = $file->getFileName();
        // имя файла + относительный путь, например 'img/file.jpg'
        $relName = $file->getRelativePathname();

        // замена абсолютного пути на локальный, т.к. при фильтрации $this->path не
        // устанавливается
        if ($this->isFiltered)
            $id = str_replace($this->basePath . DS, '', $file->getPathname());
        else
            $id = ($this->path ? $this->path . '/' : '') . $relName;
        if (OS_WINDOWS) {
            $id = str_replace(DS, '/', $id);
            $relName = str_replace(DS, '/', $relName);
        }

        return [
            $attr[self::ATTR_ID]     => $id,
            $attr[self::ATTR_NAME]   => $name,
            $attr[self::ATTR_RNAME]  => $relName,
            $attr[self::ATTR_SIZE]   => '',
            $attr[self::ATTR_PERMS]  => '',
            $attr[self::ATTR_ACTIME] => null,
            $attr[self::ATTR_CHTIME] => null,
            $attr[self::ATTR_MIME]   => '',
            $attr[self::ATTR_TYPE]   => $file->isDir() ? self::TYPE_FOLDER : self::TYPE_FILE
        ];
    }

    /**
     * Формирует строку с атрибутами файла.
     *
     * @param array $row Предварительная нформация о файле.
     * @param SplFileInfo $file Информация о текущем файле.
     * 
     * @return array
     */
    public function fetchRow(array $row, SplFileInfo $file): array
    {
        $attr = &$this->attributes;
        $filename = $file->getRealPath();

        // если атрибут "Права доступа" доступен
        if ($this->usePermsAttr) {
            // права доступа к файлу
            $row[$attr[self::ATTR_PERMS]] = Fs::permissions($filename);
        }

        // если атрибут "Последний доступ" доступен
        if ($this->useAccessTimeAttr) {
            /** @var string|false $accessTime Время последнего доступа к файлу */
            $accessTime = @$file->getATime();
            $row[$attr[self::ATTR_ACTIME]] = $accessTime ? $this->formatter->toDateTime($accessTime, 'php:Y-m-d H:i:s') : null;
        }

        // если атрибут "Последнее обновление" доступен
        if ($this->useChangeTimeAttr) {
            /** @var string|false $changeTime Время изменения файла */
            $changeTime = @$file->getCTime();
            $row[$attr[self::ATTR_CHTIME]] = $accessTime ? $this->formatter->toDateTime($changeTime, 'php:Y-m-d H:i:s') : null;
        }

        // если атрибут "MIME-тип" доступен
        if ($this->useMimeAttr) {
            $row[$attr[self::ATTR_MIME]] = mime_content_type($filename) ?: '';
        }

        // если атрибут "Размер" доступен
        if ($this->useSizeAttr && $file->isFile()) {
            $size = Fs::size($filename);
            $row[$attr[self::ATTR_SIZE]] = $size ? $this->formatter->toShortSizeDataUnit($size, 1) : SYMBOL_NONAME;
        }
        return $row;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRows(array $rowsId): false|int
    {
        $counter = 0;
        Fs::$throwException = false;
        foreach ($rowsId as $fileId) {
            $filename = $this->getSafePath($fileId);
            if ($filename) {
                if (Fs::isFile($filename))
                    $result = Fs::deleteFile($filename);
                else
                    $result = Fs::deleteDirectory($filename, false, $counter);
                if (!$result) return false;
                $counter++;
            }
        }
        return $counter;
    }
}