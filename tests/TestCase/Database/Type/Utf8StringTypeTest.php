<?php
// phpcs:ignore
namespace Eggheads\CakephpCommon\Test\TestCase\Database\Type;

use Eggheads\CakephpCommon\Database\Type\Utf8StringType;
use Eggheads\CakephpCommon\ORM\Table;
use Eggheads\CakephpCommon\TestSuite\AppTestCase;
use Cake\Database\Type;
use TestApp\Model\Entity\TestTableOne;

// phpcs:ignore
Type::map('text', Utf8StringType::class);
// phpcs:ignore
Type::map('string', Utf8StringType::class);

/**
 * @property Table $TestTableOne
 */
class Utf8StringTypeTest extends AppTestCase
{
    /** @inheritdoc */
    public function tearDown(): void
    {
        parent::tearDown();
        Type::set('text', new Type\StringType('text'));
        Type::set('string', new Type\StringType('string'));
    }

    /**
     * Сохраняем эмодзи
     */
    public function test(): void
    {
        $this->_setTestNow('2017-11-21 09:00:00');

        $svData = $this->TestTableOne->saveArr([
            'col_enum' => 'val1',
            'col_text' => "test text Бла бла бла\nППП ggg",
        ]);
        self::assertNotEmpty($svData->id);
        /** @var TestTableOne $dbData */
        $dbData = $this->TestTableOne->get($svData->id);
        $this->assertEntityEqualsEntity($svData, $dbData);

        $badText = 'd𡃁d';
        $resultText = 'dd';
        $svData = $this->TestTableOne->saveArr([
            'col_enum' => 'val1',
            'col_text' => $badText,
        ]);
        self::assertEquals($resultText, $this->TestTableOne->get($svData->id)->col_text); // @phpstan-ignore-line
    }
}
