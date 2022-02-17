# php-mongo-odm

Библиотека для простых CRUD методов. Требует PHP 8 с расширением mongodb

### Запуск тестов

### Сборка контейнера с PHP 8

```./build-php8.0-ubuntu.sh```

Зайти в контейнер можно скриптом
```shell
./run-in-container.sh
```

### Установка пакетов Composer
```
./run-in-container.sh 
cd /app && composer install
```

### Запуск тестов
```shell
./run-in-container.sh
XDEBUG_MODE=coverage ./vendor/bin/phpunit
```

Перед запуском тестов нужно отредактировать значения переменных **mongo.server** и **mongo.dbname**
в файле **phpunit.xml**

Также должен быть запущен сервер mongodb с БД для тестов

Для версии mongodb 3.4
```shell
docker-composer up
```

Для версии mongodb 5.0.5
```shell
docker-compose -f ./docker-compose-mongodb-5.0.yml up
```

