# php-mongo-odm

Библиотека для простых CRUD методов. Требует PHP 8 с расширением mongodb
Пока протестировано только для php8.0, php8.1 и mongodb 3.6 

### Сборка контейнера с PHP 8.1

Для версии 8.0 выполнять аналогичные команды 

```shell
./build-php8.1-bullseye.sh
```

Зайти в контейнер можно скриптом
```shell
./run-in-container.sh php8.1-cli-bullseye bash
```

### Установка пакетов Composer
```
./run-in-container.sh php8.1-cli-bullseye bash -c "cd /app && composer install"
```

### Запуск тестов
```shell
./run-in-container.sh php8.1-cli-bullseye bash -c "cd /app && XDEBUG_MODE=coverage ./vendor/bin/phpunit"
```

Перед запуском тестов нужно отредактировать значения переменных **mongo.server** и **mongo.dbname**
в файле **phpunit.xml**

Также должен быть запущен сервер mongodb с БД для тестов

Например, для версии mongodb 3.6
```shell
docker compose -f ./docker-compose-mongodb-3.6.yml up
```

Для других версий аналогично