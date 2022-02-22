<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\Factory;

use Cake\Datasource\EntityInterface;
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
     * @param callable|int|array|EntityInterface|null $parameter
     * @param int $n
     * @return $this
     * @phpstan-ignore-next-line
     */
    public function withTestTableOne(callable|int|array|null|EntityInterface $parameter = null, int $n = 1): self
    {
        return $this->with('TestTableOne', TestTableOneFactory::make($parameter, $n));
    }
}
