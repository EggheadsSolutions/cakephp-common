<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Filesystem;

use Eggheads\CakephpCommon\Error\InternalException;
use Eggheads\CakephpCommon\Lib\Env;
use Eggheads\CakephpCommon\Lib\Strings;
use Exception;

class File extends \Cake\Filesystem\File
{
    /** @var string Временная папка внутри TMP для generateTempFilePath() */
    protected const TEMP_FILE_DIR = 'temp-files';

    /**
     * Зипует файлы
     *
     * @param string|string[] $files - файлы, которые нужно зипануть
     * @param string|null $newFile - полное имя нового файла. если не передать, то будет self::DOWNLOAD_PATH . uniqid()
     * @param bool $deleteOld - удалять ли файлы
     * @return string Имя файла
     * @throws InternalException
     */
    public static function zip(array|string $files, ?string $newFile = null, bool $deleteOld = false): string
    {
        $files = (array)$files;

        if (empty($newFile)) {
            $defaultPath = Env::getDownloadPath();
            if (empty($defaultPath)) {
                throw new InternalException('Путь не задан явно и нет пути по-умолчанию');
            }
            Folder::createIfNotExists($defaultPath);
            $newFile = $defaultPath . uniqid();
        }
        if (!preg_match('/\.zip$/i', $newFile)) {
            $newFile .= '.zip';
        }

        $tmpDir = TMP . uniqid();
        mkdir($tmpDir);
        $zipFiles = [];
        $currentDir = getcwd();
        chdir($tmpDir);
        foreach ($files as $sourceFile) {
            $tmpFile = Strings::lastPart(DS, $sourceFile);
            $zipFiles[] = $tmpFile;
            if ($deleteOld) {
                rename($sourceFile, $tmpFile);
            } else {
                if (is_dir($sourceFile)) {
                    exec("cp -r $sourceFile $tmpFile");
                } else {
                    copy($sourceFile, $tmpFile);
                }
            }
        }
        if (file_exists($newFile)) {
            unlink($newFile);
        }

        exec('zip -r "' . $newFile . '" "' . implode('" "', $zipFiles) . '"');
        chdir($currentDir);
        exec("rm -rf $tmpDir");
        return $newFile;
    }

    /**
     * Распаковать архив
     * по умолчанию рядом с архивом
     *
     * @param string $pathToFile
     * @param string|null $unzipFolder
     * @return void
     * @throws Exception
     */
    public static function unZip(string $pathToFile, ?string $unzipFolder = null)
    {
        $extension = strstr(pathinfo($pathToFile)['basename'], '.');
        if (!empty($unzipFolder)) {
            !file_exists($unzipFolder) ? mkdir($unzipFolder) : null;
        }
        $unpackPath = !empty($unzipFolder) ? $unzipFolder : dirname($pathToFile);
        switch ($extension) {
            case '.tar.gz':
                exec('tar -xf ' . $pathToFile . ' -C ' . $unpackPath);
                break;
            default:
                exec('unzip ' . $pathToFile . ' -d ' . $unpackPath);
                break;
        }
    }

    /**
     * Генерируем уникальное имя для файла в специально обученной папке
     *
     * @param string $prefix
     * @return string
     */
    public static function generateTempFilePath(string $prefix): string
    {
        $tempDir = TMP . static::TEMP_FILE_DIR;
        Folder::createIfNotExists($tempDir);
        Folder::cleanupDirByLifetime($tempDir, ['.*'], 3600);
        return tempnam($tempDir, $prefix . '-');
    }
}
