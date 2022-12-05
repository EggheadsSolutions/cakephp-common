## ProxyConfig

## TestTableFive
description 5
### Поля:
* array `col_json` = NULL Описание JSON
* int `id`

## TestTableOne
description govno
### Поля:
* int `col_enum`
* \Cake\I18n\Time `col_time` = 'CURRENT_TIMESTAMP' asdasd
* int `id` comment1
* string `notExists`
* string `oldField`

## TestTableSix
Table description 6
### Поля:
* \Eggheads\CakephpCommon\I18n\FrozenTime `created` = 'CURRENT_TIMESTAMP' Создано
* int `id`

## TestTableTwo
description qweqwe
### Поля:
* string `col_text` = NULL
* int `id`
* int `table_one_fk` blabla
* string `virtualField`
* string|null `virtualFieldOrNull`
### Связи:
* TestTableOne `$TestTableOne` TestTableOne.table_one_fk => TestTableTwo.id

