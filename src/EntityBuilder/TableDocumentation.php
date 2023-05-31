<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\EntityBuilder;

use Eggheads\CakephpCommon\Error\InternalException;
use Eggheads\CakephpCommon\Filesystem\File;
use Eggheads\CakephpCommon\Filesystem\Folder;
use Eggheads\CakephpCommon\I18n\FrozenDate;
use Eggheads\CakephpCommon\I18n\FrozenTime;
use Eggheads\CakephpCommon\Lib\Misc;
use DocBlockReader\Reader;
use Eggheads\CakephpCommon\Traits\LibraryTrait;
use Exception;
use ReflectionClass;
use ReflectionException;

/**
 * Формируем README.md файл с данными по таблицам
 */
class TableDocumentation
{
    use LibraryTrait;

    private const DEPENDENCY_ONE_TO_ONE = '/([A-Z][^\W_]+)\s\$([A-Z][^\W_]+)\s`([\w-]+)`\s=>\s`([\w-]+)`/';
    private const DEPENDENCY_ONE_TO_MANY = '/([A-Z][^\W_]+)\[\]\s\$([A-Z][^\W_]+)\s`([\w-]+)`\s=>\s`([\w-]+)`/';

    // регекспу нужно 2 слеша для экранирования в регекспе, и ещё 2 слеша для экранирования строки
    private const FIELD_INFO = '/([\\\\a-zA-Z0-9|]+)\s\$([a-z]\w+)\s?(.*)/';

    private const JS_TYPES = [
        '\\' . FrozenTime::class => 'string',
        '\\' . FrozenDate::class => 'string',
        'array' => 'Array',
    ];

    /**
     * Конфиг
     *
     * @var ?EntityBuilderConfig
     */
    private static ?EntityBuilderConfig $_config = null;

    /**
     * Кеш PHPDoc описаний сущностей
     *
     * @var array<string, array<string, string>>
     */
    private static array $_entityAnnotationsCache = [];

    /**
     * Задать конфиг
     *
     * @param ?EntityBuilderConfig $config
     * @return void
     */
    public static function setConfig(?EntityBuilderConfig $config): void
    {
        self::$_config = $config;
    }

    /**
     * Формируем доку
     *
     * @return bool true в случае необходимости перегрузить доку
     * @throws InternalException
     * @throws Exception
     */
    public static function build(): bool
    {
        if (empty(self::$_config)) {
            throw new InternalException('Не задан конфиг');
        }
        self::$_config->checkValid();
        $entityList = self::_getEntityList();
        $mdResult = self::_buildMarkDownDoc($entityList);
        $jsResult = self::_buildJsDoc($entityList);

        return $mdResult || $jsResult;
    }

    /**
     * Формируем список таблиц
     *
     * @return string[]
     * @throws ReflectionException
     */
    private static function _getEntityList(): array
    {
        $folder = new Folder(self::$_config->modelFolder . '/Entity');
        $files = $folder->find('.*\.php', true);
        $baseClassFile = Misc::namespaceSplit(self::$_config->baseEntityClass, true) . '.php';
        $files = array_diff($files, [$baseClassFile]);

        $result = [];
        foreach ($files as $tblFile) {
            include_once $folder->pwd() . DS . $tblFile; // дабы файл может создаться раньше, а autoload не вкурсе

            $entityName = str_replace('.php', '', $tblFile);
            $refClass = new ReflectionClass(self::$_config->modelNamespace . '\Entity\\' . $entityName);
            if ($refClass->isAbstract()) {
                continue;
            }

            $result[] = str_replace('.php', '', $tblFile);
        }
        return $result;
    }

    /**
     * Формируем md файл с описанием
     *
     * @param string[] $entityList
     * @return bool
     */
    private static function _buildMarkDownDoc(array $entityList): bool
    {
        $mdFile = new File(self::$_config->modelFolder . '/' . self::$_config->descriptionFile);
        if ($mdFile->exists()) {
            $curContents = $mdFile->read();
        } else {
            $curContents = '';
            $mdFile->create();
        }

        $newContents = '';

        foreach ($entityList as $entity) {
            $newContents .= self::_buildTableArticle($entity);
        }

        if ($newContents !== $curContents) {
            $mdFile->write($newContents);
            $mdFile->close();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Формируем описание таблицы
     *
     * @param string $className
     * @return string
     */
    private static function _buildTableArticle(string $className): string
    {
        $article = "## $className\n";
        $comment = self::_getTableComment($className);
        if (!empty($comment)) {
            $article .= $comment . "\n";
        }

        $fields = self::_getTableFields($className);
        if (!empty($fields)) {
            $article .= "### Поля:\n";
            foreach ($fields as $field) {
                $article .= "* " . $field . "\n";
            }
        }

        $deps = self::_getTableDependencies($className);
        if (!empty($deps)) {
            $article .= "### Связи:\n";
            foreach ($deps as $dependency) {
                $article .= "* " . $dependency . "\n";
            }
        }
        $article .= "\n";
        return $article;
    }

    /**
     * Комментарий к таблице
     *
     * @param string $className
     * @return ?string
     * @throws Exception
     */
    private static function _getTableComment(string $className): ?string
    {
        $annotations = self::_getEntityAnnotations($className);
        if (!empty($annotations['tableComment'])) {
            return $annotations['tableComment'];
        } else {
            return null;
        }
    }

    /**
     * PHPDoc комментарии к классу
     *
     * @param string $className полный путь к классу
     * @return array<string, string>
     * @throws Exception
     */
    private static function _getEntityAnnotations(string $className): array
    {
        if (empty(self::$_entityAnnotationsCache[$className])) {
            $reader = new Reader(self::$_config->modelNamespace . '\Entity\\' . $className);
            self::$_entityAnnotationsCache[$className] = $reader->getParameters();
        }
        return self::$_entityAnnotationsCache[$className];
    }

    /**
     * Поля теблицы
     *
     * @param string $className
     * @return ?string[]
     * @throws Exception
     */
    private static function _getTableFields(string $className): ?array
    {
        $annotations = self::_getEntityAnnotations($className);
        if (empty($annotations['property'])) {
            return null;
        }
        $annotations['property'] = (array)$annotations['property'];

        $fields = [];
        foreach ($annotations['property'] as $prop) {
            if (preg_match(self::FIELD_INFO, $prop, $matches)) {
                $fields[$matches[2]] = $matches[1] . ' `' . $matches[2] . '`' . (!empty($matches[3]) ? ' ' . $matches[3] : '');
            }
        }
        ksort($fields);
        return array_values($fields);
    }

    /**
     * Список зависимостей с другими таблицами
     *
     * @param string $className
     * @return ?string[]
     * @throws Exception
     */
    private static function _getTableDependencies(string $className): ?array
    {
        $annotations = self::_getEntityAnnotations($className);
        if (empty($annotations['property'])) {
            return null;
        }
        $annotations['property'] = (array)$annotations['property'];

        $dependencies = [];
        foreach ($annotations['property'] as $prop) {
            if (preg_match(self::DEPENDENCY_ONE_TO_ONE, $prop, $matches)) {
                $dependencies[$matches[2]] = $matches[1] . ' `$' . $matches[2] . '` ' . $matches[1] . "." . $matches[3] . ' => ' . $className . '.' . $matches[4];
            }
        }

        foreach ($annotations['property'] as $prop) {
            if (preg_match(self::DEPENDENCY_ONE_TO_MANY, $prop, $matches)) {
                $dependencies[$matches[2]] = $matches[1] . '[] `$' . $matches[2] . '` ' . $matches[1] . "." . $matches[3] . ' => ' . $className . '.' . $matches[4];
            }
        }
        ksort($dependencies);
        return array_values($dependencies);
    }

    /**
     * JS файл с описанием типов
     *
     * @param string[] $entityList
     * @return bool
     * @throws Exception
     */
    private static function _buildJsDoc(array $entityList): bool
    {
        $jsFile = new File(self::$_config->modelFolder . '/' . self::$_config->jsTypesFile);
        if ($jsFile->exists()) {
            $curContents = $jsFile->read();
        } else {
            $curContents = '';
            $jsFile->create();
        }

        $newContents = '';

        foreach ($entityList as $entity) {
            $newContents .= self::_buildJsTableDescription($entity);
        }

        if ($newContents !== $curContents) {
            $jsFile->write($newContents);
            $jsFile->close();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Формируем описание таблицы в формате JS
     *
     * @param string $className
     * @return string
     * @throws Exception
     */
    private static function _buildJsTableDescription(string $className): string
    {
        $article = "/**\n * @typedef {Object} " . $className . 'Entity';
        $comment = self::_getTableComment($className);
        if (!empty($comment)) {
            $article .= ' ' . $comment;
        }
        $article .= "\n";

        $entityAnnotations = self::_getEntityAnnotations($className);
        if (empty($entityAnnotations['property'])) {
            return '';
        }

        $entityAnnotations['property'] = (array)$entityAnnotations['property'];

        $fields = [];
        $oneToOne = [];
        $oneToMany = [];

        foreach ($entityAnnotations['property'] as $fieldDescription) {
            if (preg_match(self::FIELD_INFO, $fieldDescription, $matches)) {
                $fieldType = array_key_exists($matches[1], self::JS_TYPES) ? self::JS_TYPES[$matches[1]] : $matches[1];

                $fields[$matches[2]] = " * @property {" . $fieldType . "} " . $matches[2] . (!empty($matches[3])
                        ? ' ' . $matches[3] : '');
            } elseif (preg_match(self::DEPENDENCY_ONE_TO_ONE, $fieldDescription, $matches)) {
                $oneToOne[$matches[2]] = " * @property {" . $matches[1] . "Entity} " . $matches[2] . ' ' . $matches[3] . ' => ' . $matches[4];
            } elseif (preg_match(self::DEPENDENCY_ONE_TO_MANY, $fieldDescription, $matches)) {
                $oneToMany[$matches[2]] = " * @property {" . $matches[1] . "Entity[]} " . $matches[2] . ' ' . $matches[3] . ' => ' . $matches[4];
            }
        }

        ksort($fields);
        ksort($oneToOne);
        ksort($oneToMany);

        $result = $fields + $oneToOne + $oneToMany;

        if (!empty($result)) {
            $article .= implode("\n", $result) . "\n";
        }

        $article .= " */\n\n";
        return $article;
    }
}
