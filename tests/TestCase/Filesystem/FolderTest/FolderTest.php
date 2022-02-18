<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\TestCase\Filesystem\FolderTest;

use Eggheads\CakephpCommon\Filesystem\Folder;
use Eggheads\CakephpCommon\TestSuite\AppTestCase;

class FolderTest extends AppTestCase
{

    /**
     * Проверка чистилки файлов
     */
    public function testCleanupDirByLifetime(): void
    {
        $csvFile = __DIR__ . '/temp.csv';
        $pdfFile = __DIR__ . '/temp.pdf';
        $pdfNewFile = __DIR__ . '/tempNew.pdf';
        $pdfInFolderFile = __DIR__ . '/nonDelete/tempNew.pdf';

        file_put_contents($csvFile, 'This is csv file');
        file_put_contents($pdfFile, 'This is PDF file');
        Folder::createIfNotExists(__DIR__ . '/nonDelete');
        file_put_contents($pdfInFolderFile, 'This is PDF file');
        sleep(4);
        file_put_contents($pdfNewFile, 'This is PDF file');

        Folder::cleanupDirByLifetime(__DIR__, ['.*\.pdf'], 3, ['nonDelete/']);

        self::assertFileExists($csvFile, 'Файл temp.csv был удалён, но такого не должно было случится');
        self::assertFileExists($pdfNewFile, 'Файл tempNew.pdf был удалён, но такого не должно было случится');
        self::assertFileExists($pdfInFolderFile, 'Файл /nonDelete/tempNew.pdf был удалён, хотя стоит запрет на удаление из директории');
        self::assertFileDoesNotExist($pdfFile, 'Файл temp.pdf должен был исчезнуть');
        self::assertFileExists(__FILE__, 'Тест удалил себя!!!');

        if (file_exists($pdfInFolderFile)) {
            unlink($pdfInFolderFile);
            rmdir(__DIR__ . '/nonDelete');
        }
        unlink($csvFile);
        unlink($pdfNewFile);
    }
}
