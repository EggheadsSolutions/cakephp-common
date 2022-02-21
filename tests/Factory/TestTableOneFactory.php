<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\Factory;

use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class TestTableOneFactory extends BaseFactory
{

    protected function getRootTableRegistryName(): string
    {
        return 'TestTableOne';
    }

    protected function setDefaultTemplate(): void
    {
        $this->setDefaultData(function (Generator $faker) {
            return [
                'col_enum' => $faker->randomElement(['val1', 'val2', 'val3']),
                'col_text' => $faker->text(100),
                'col_time' => $faker->dateTime->format('Y-m-d H:i:s'),
            ];
        });
    }
}
