# EntityBuilder

Данный функционал предназначен для автоописания свойств и зависимостей Entity сущностей. Также автоматом прописываются
для соответствующих Table результаты вызова методов get(), find() и т.п.

Для автоматического формирования описаний необходимо отнаследовать
класс [EntityBuilderShell](../Shell/EntityBuilderShell.php)
и вызывать после выполнения миграций.

## Для чего нужна папка Model/Table/Query

Все классы в данной папке нужны только для автоподсказки IDE, самим CakePHP они не используются.