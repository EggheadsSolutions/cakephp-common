<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\Factory;

use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class TestTableTwoFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'TestTableTwo';
    }

    protected function setDefaultTemplate(): void
    {
        $this
            ->setDefaultData(function (Generator $faker) {
                return [
                    'col_text' => $faker->text(100),
                ];
            })
            ->withTestTableOne();
    }

    public function withTestTableOne($parameter = null, int $n = 1): self
    {
        return $this->with('TestTableOne', TestTableOneFactory::make($parameter, $n));
    }
}
