<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Command;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Eggheads\CakephpCommon\EntityBuilder\EntityBuilder;
use Eggheads\CakephpCommon\EntityBuilder\EntityBuilderConfig;
use Eggheads\CakephpCommon\EntityBuilder\TableDocumentation;
use Eggheads\CakephpCommon\Error\InternalException;
use Eggheads\CakephpCommon\ORM\Entity;
use Eggheads\CakephpCommon\ORM\Table;
use Cake\Command\Command;
use ReflectionException;

class EntityBuilderCommand extends Command
{
    /**
     * Формируем/обновляем сущности
     *
     * @param Arguments $args
     * @param ConsoleIo $io
     * @return int|null
     * @throws InternalException
     * @throws ReflectionException
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        if ($this->_buildEntityAndDoc()) {
            $io->out('Has changes, update Model folder');
        }

        return self::CODE_SUCCESS;
    }

    /**
     * Генерим сущности и документацию
     *
     * @return bool
     * @throws InternalException|ReflectionException
     */
    private function _buildEntityAndDoc(): bool
    {
        $this->_setConfig();
        $hasEntityChanges = EntityBuilder::build();
        $hasDocChanges = TableDocumentation::build();
        return $hasEntityChanges || $hasDocChanges;
    }

    /**
     * Инициализация конфига
     *
     * @return void
     * @throws InternalException
     */
    private function _setConfig(): void
    {
        $config = EntityBuilderConfig::create()
            ->setModelFolder(APP . 'Model')
            ->setBaseTableClass(Table::class)
            ->setBaseEntityClass(Entity::class);
        EntityBuilder::setConfig($config);
        TableDocumentation::setConfig($config);
    }
}
