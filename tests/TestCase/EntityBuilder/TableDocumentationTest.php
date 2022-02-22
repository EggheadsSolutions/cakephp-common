<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\TestCase\EntityBuilder;

use Eggheads\CakephpCommon\Filesystem\Folder;
use Eggheads\CakephpCommon\EntityBuilder\EntityBuilder;
use Eggheads\CakephpCommon\EntityBuilder\EntityBuilderConfig;
use Eggheads\CakephpCommon\EntityBuilder\TableDocumentation;
use Eggheads\CakephpCommon\TestSuite\AppTestCase;

class TableDocumentationTest extends AppTestCase
{
    /** @inheritdoc */
    public function setUp(): void
    {
        parent::setUp();

        EntityBuilderTest::restoreModelFolder(true);
        EntityBuilderConfig::create()
            ->setModelFolder(EntityBuilderTest::MODEL_PATH . DS)
            ->setModelNamespace('TestApp\\' . EntityBuilderTest::MODEL_FOLDER)
            ->register();
    }

    /** @inheritdoc */
    public function tearDown(): void
    {
        parent::tearDown();
        EntityBuilder::setConfig(null);
        TableDocumentation::setConfig(null);
        EntityBuilderTest::restoreModelFolder(false);
    }

    /**
     * без конфига
     */
    public function testNoConfig(): void
    {
        $this->expectExceptionMessage("Не задан конфиг");
        $this->expectException(\Exception::class);
        TableDocumentation::setConfig(null);
        TableDocumentation::build();
    }

    /**
     * плохой конфиг
     */
    public function testBadConfig(): void
    {
        $this->expectExceptionMessage("Empty value for field 'modelFolder'");
        $this->expectException(\Exception::class);
        EntityBuilderConfig::create()->register();
        TableDocumentation::build();
    }

    /**
     * Обновление существующих таблиц и создание для них всего, что нужно
     */
    public function testBuild(): void
    {
        /**
         * Он работает на основе существующих классов. если в них неактуальные комменты, то они и останутся
         * изменяет только 2 файла доков
         */
        $hasChanges = TableDocumentation::build();
        $expectedFolder = new Folder(__DIR__ . '/ExpectedResults/BuildDocs');
        $expectedFiles = $expectedFolder->read()[1];
        foreach ($expectedFiles as $fileName) {
            $expectedFile = __DIR__ . '/ExpectedResults/BuildDocs/' . $fileName;
            $actualFile = APP . EntityBuilderTest::MODEL_FOLDER . '/' . $fileName;
            self::assertFileEquals($expectedFile, $actualFile, 'Неправильно сработал построитель документации');
        }
        self::assertTrue($hasChanges, 'Построитель не сказал, что были изменения');
    }
}
