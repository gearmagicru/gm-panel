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
use Gm\Helper\Str;
use Gm\Theme\Theme;
use Gm\Data\Model\RecordModel;
use Gm\Filesystem\Filesystem as Fs;

/**
 * Модель данных настроек разметки виджета.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Data\Model
 * @since 1.0
 */
class WidgetMarkupSettingsModel extends RecordModel
{
    /** 
     * Уникальный идентификатор виджета в шаблоне.
     * 
     * @var string
     */
    protected string $uniqueId = '';

    /**
     * Файл последнего шаблона из которого был вызван виджет.
     * 
     * @see \Gm\View\Widget::$calledFromViewFile
     * 
     * @var string
     */
    public string $calledFromViewFile = '';

    /**
     * {@inheritdoc}
     */
    public function isNewRecord(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function updateProcess(array $attributes = null): false|int
    {
        if (!$this->beforeSave(false)) {
            return false;
        }

        // возвращает атрибуты без псевдонимов (если они были указаны)
        $attributes = $this->unmaskedAttributes($this->attributes);

        $this->beforeUpdate($attributes);

        // сохранение настроек модуля
        $result = $this->saveSettings($attributes);
        $this->afterSave(false, $attributes, $result);
        return 1;
    }

    /**
     * Сохраняет настройки разметки виджета в шаблон.
     * 
     * @param array $attributes Параметры (атрибуты) настроек виджета.
     * 
     * @return bool
     * 
     * @throws \Gm\Filesystem\Exception\FileNotFoundException
     */
    public function saveSettings(array $attributes): bool
    {
        $filename = $this->getViewFile();
        $content = Fs::get($filename);

        // получение скрипта из файла шаблона
        $script = $this->getScriptPhpFromView($this->uniqueId, $content);
        if ($script === null) {
            $this->addError('Error getting script from template file');
            return false;
        }

        // замена скрипта на новый
        $content = str_replace(
            $script['content'], 
            $this->makeScriptPhp($this->uniqueId, $attributes), 
            $content
        );

        // запись нового скрипта в файл
        if (Fs::put($filename, $content) === false) {
            $this->addError(sprintf('Failed to write file "%s"', $filename));
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function afterValidate(bool $isValid): bool
    {
        if ($isValid) {
            //
            $this->uniqueId = $this->getUnsafeAttribute('id');
            if (empty($this->uniqueId)) {
                $this->addError(Gm::t('app', 'Parameter passed incorrectly "{0}"', ['id']));
                return false;
            }

            //
            $this->calledFromViewFile = $this->getUnsafeAttribute('calledFrom');
            if (empty($this->calledFromViewFile)) {
                $this->addError(Gm::t('app', 'Parameter passed incorrectly "{0}"', ['calledFrom']));
                return false;
            }

            //
            $viewFile = $this->getViewFile();
            if (!file_exists($viewFile)) {
                $this->addError(Gm::t('app', 'File "{0}" not found', [$viewFile]));
                return false;
            }
        }
        return $isValid;
    }

    /**
     * {@inheritdoc}
     */
    public function get(mixed $identifier = null): ?static
    {
        return $this;
    }

    /**
     * @param string $id
     * @param string $content
     * 
     * @return array|null
     */
    public function getScriptPhpFromView(string $id, string $content): ?array
    {
        $posId = mb_strpos($content, "'" . $id . "'");
        if ($posId === false) return null;
    
        $posEnd = mb_strpos($content, '?>', $posId);
        if ($posEnd === false) return null;
    
        $content = mb_substr($content, 0, $posEnd + 2);
    
        $posBegin = mb_strrpos($content, '<?');
        if ($posBegin === false) return null;
    
        return [
            'begin'   => $posBegin,
            'end'     => $posEnd,
            'content' => mb_substr($content, $posBegin)
        ];
    }

    /**
     * @param string $id
     * @param array $params
     * 
     * @return string
     */
    public function makeScriptPhp(string $id, array $params = []): string
    {
        return '<?= $this->widget(\'' . $id . '\', ' . Str::varExport($params, true) . ') ?>';
    }

    /**
     * @return string
     */
    protected function getViewFile(): string
    {
        return $this->getTheme()->path . $this->calledFromViewFile;
    }

    /**
     * @see WidgetMarkupSettingsModel::getTheme()
     * 
     * @var Theme
     */
    protected Theme $theme;

    /**
     * @return Theme
     */
    protected function getTheme(): Theme
    {
        if (!isset($this->theme)) {
            $this->theme = Gm::$app->createFrontendTheme();
            $this->theme->set();
        }
        return $this->theme;
    }
}
