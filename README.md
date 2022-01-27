# php-mongo-odm

Библиотека для простых CRUD методов. Требует php7. 

## Установка и запуск

Для запуска тестов и разработки требуется php7 с расширением mongo

### Сборка docker-контейнера с php7

https://github.com/solo-framework/docker-php7

Если используете PhpStorm, то настроить интерпретатор php, указав собранный контейнер:

![Alt text](interpreter.png?raw=true "Title")

Для выполнения команд в контейнере, нужно использовать скрипт *./run-in-container.sh*


### Установка пакетов Composer
```
./run-in-container.sh 'cd /app && composer install'
```

### Запуск тестов

Перед запуском тестов нужно отредактировать значения переменных **mongo.server** и **mongo.dbname**
в файле **phpunit.xml**

У вас должен быть запущен сервер mongodb с БД для тестов

Пример создания пользователя:

```
use odmtest
db.createUser({user: "odmtest", pwd: "odmtest", roles:["dbOwner"]})
```

Запуск юнит-тестов:

```
./run-in-container.sh 'cd /app && vendor/bin/phpunit'
```