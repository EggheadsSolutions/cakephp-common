<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\ORM;

class Entity extends \Cake\ORM\Entity
{
    /**
     * Проверка, что значение поля изменилось
     * потому что dirty() и extractOriginalChanged() могут срабатывать даже когда не изменилось, а при любом присвоении
     *
     * @param string $fieldName
     * @return bool
     */
    public function changed(string $fieldName): bool
    {
        return $this->get($fieldName) != $this->getOriginal($fieldName);
    }

    /**
     * Ошибки без разделения по полям
     *
     * @return string[]
     */
    public function getAllErrors(): array
    {
        $errors = $this->getErrors();
        if (empty($errors)) {
            return [];
        }
        return array_merge(...array_values($errors));
    }
}
