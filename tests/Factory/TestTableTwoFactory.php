<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\Factory;

use Eggheads\CakephpCommon\Plugins\CakephpFixtureFactories\AbstractFixtureFactory;
use Faker\Generator;

class TestTableTwoFactory extends AbstractFixtureFactory
{
    /** @inerhitDoc */
    // phpcs:ignore
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

    /**
     * Создадим зависимую запись
     *
     * @param $parameter
     * @param int $n
     * @return $this
     */
    public function withTestTableOne($parameter = null, int $n = 1): self
    {
        return $this->with('TestTableOne', TestTableOneFactory::make($parameter, $n));
    }
}
