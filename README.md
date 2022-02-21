# Eggheads Application Skeleton

Запуск в Docker контейнере (выполнить в корневой папке проекта):

```bash
docker-compose up -d
# подключаемся по ssh к проекту
docker-compose exec -u www-data php bash
# устанавливаем зависимости
composer install
# запускаем все тесты
composer check
```

В папке _dev_ находится описание контейнеров, а также рабочая папка для MySQL.
