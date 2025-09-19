
# `sindyko/modular-modify`

`sindyko/modular-modify`  это модульная система для приложений Laravel. Она использует
[Composer path repositories](https://getcomposer.org/doc/05-repositories.md#path) для автозагрузки 
и [Laravel package discovery](https://laravel.com/docs/11.x/packages#package-discovery) для инициализации модулей, 
а затем предоставляет минимальный набор инструментов для заполнения любых пробелов.

Этот проект представляет собой как набор соглашений, так и пакет. 
Основная идея заключается в том, что вы можете создавать «модули» в отдельной директории `app-modules/`, 
что позволяет лучше организовать крупные проекты. Эти модули используют существующую
[Laravel package system](https://laravel.com/docs/11.x/packages), и следуют существующим соглашениям Laravel.

- [Установка](#Установка)
- [Использование](#Использование)
- [Сравнение с `internachi/modular`](#Сравнение)

## Установка

Для начала работы выполните:

```shell script
composer require sindyko/modular-modify
```

Laravel автоматически обнаружит пакет, и всё будет настроено автоматически.

### Публикация конфигурации
Хотя это и не обязательно, настоятельно рекомендуется настроить пространство имён по умолчанию для модулей. 
По умолчанию оно установлено как `Modules\`, что работает нормально, но усложняет извлечение модуля в отдельный пакет, 
если вы когда-либо решите это сделать.

Мы рекомендуем настроить организационное пространство имён (например, `"MyCompany"`). 
Для этого нужно опубликовать конфигурацию пакета:

```shell script
php artisan vendor:publish --tag=modular-config
```

### Создание модуля

Далее создадим модуль:

```shell script
php artisan make:module my-module
```

Modular создаст новый модуль для вас:

```
app-modules/
  my-module/
    composer.json
    src/
    tests/
    routes/
    resources/
    database/
```

Он также добавит две новые записи в файл `composer.json` вашего приложения. Первая запись регистрирует
`./app-modules/my-module/` как [путь репозитория](https://getcomposer.org/doc/05-repositories.md#path),
а вторая требует `modules/my-module:*` (как любую другую зависимость Composer).

Modular напомнит вам выполнить обновление Composer, поэтому сделаем это сейчас:

```shell script
composer update modules/my-module
```

### Опционально: Синхронизация конфигурации

Вы можете запустить команду синхронизации, чтобы убедиться, 
что ваш проект настроен для поддержки модулей:

```shell script
php artisan modules:sync
```

Это добавит набор тестов `Modules` в ваш файл `phpunit.xml` (если он существует) 
и обновит конфигурацию плагина [PhpStorm Laravel plugin](https://plugins.jetbrains.com/plugin/7532-laravel)
для правильного поиска представлений вашего модуля.

Безопасно запускать эту команду в любое время, так как она добавит только отсутствующие конфигурации. 
Вы даже можете добавить её в скрипты  `post-autoload-dump` в файле
`composer.json` вашего приложения.

## Использование

Все модули следуют существующим соглашениям Laravel, 
и автоматическое обнаружение должно работать как ожидается в большинстве случаев:

- Команды автоматически регистрируются с помощью Artisan
- Миграции будут выполняться с помощью Migrator
- Фабрики автоматически загружаются для `factory()`
- Политики автоматически обнаруживаются для ваших моделей
- Компоненты Blade будут автоматически обнаружены
- Слушатели событий будут автоматически обнаружены

### Команды

#### Команды пакета

Мы предоставляем несколько вспомогательных команд:

- `php artisan make:module`  — создание нового модуля
- `php artisan modules:cache` — кэширование загруженных модулей для немного более быстрого автообнаружения
- `php artisan modules:clear` — очистка кэша модулей
- `php artisan modules:sync`  — обновление конфигураций проекта (например `phpunit.xml`) с помощью настроек модуля
- `php artisan modules:list`  — список всех модулей

#### Laravel “`make:`” команды

Мы также добавляем опцию `--module=` к большинству команд Laravel `make:`, чтобы вы могли использовать 
все существующие инструменты, которые вы знаете. Сами команды остаются такими же, 
что означает, что вы можете использовать свои [пользовательские заготовки](https://laravel.com/docs/11.x/artisan#stub-customization) и всё остальное, 
что предоставляет Laravel:

- `php artisan make:cast MyModuleCast --module=[название модуля]`
- `php artisan make:controller MyModuleController --module=[название модуля]`
- `php artisan make:command MyModuleCommand --module=[название модуля]`
- `php artisan make:component MyModuleComponent --module=[название модуля]`
- `php artisan make:channel MyModuleChannel --module=[название модуля]`
- `php artisan make:event MyModuleEvent --module=[название модуля]`
- `php artisan make:exception MyModuleException --module=[название модуля]`
- `php artisan make:factory MyModuleFactory --module=[название модуля]`
- `php artisan make:job MyModuleJob --module=[название модуля]`
- `php artisan make:listener MyModuleListener --module=[название модуля]`
- `php artisan make:mail MyModuleMail --module=[название модуля]`
- `php artisan make:middleware MyModuleMiddleware --module=[название модуля]`
- `php artisan make:model MyModule --module=[название модуля]`
- `php artisan make:notification MyModuleNotification --module=[название модуля]`
- `php artisan make:observer MyModuleObserver --module=[название модуля]`
- `php artisan make:policy MyModulePolicy --module=[название модуля]`
- `php artisan make:provider MyModuleProvider --module=[название модуля]`
- `php artisan make:request MyModuleRequest --module=[название модуля]`
- `php artisan make:resource MyModule --module=[название модуля]`
- `php artisan make:rule MyModuleRule --module=[название модуля]`
- `php artisan make:seeder MyModuleSeeder --module=[название модуля]`
- `php artisan make:test MyModuleTest --module=[название модуля]`

#### Другие Laravel-команды

В дополнение к добавлению опции `--module` к большинству команд `make:`, 
мы также добавили ту же опцию к команде `db:seed`. Если вы передадите опцию `--module` команде `db:seed`, 
она будет искать ваш сидер в пространстве имён модуля:

- `php artisan db:seed --module=[название модуля]` попытается вызвать `Modules\MyModule\Database\Seeders\DatabaseSeeder`
- `php artisan db:seed --class=MySeeder --module=[название модуля]` попытается вызвать `Modules\MyModule\Database\Seeders\MySeeder`

#### Команды сторонних разработчиков

Мы также можем добавить опцию `--module` к командам в сторонних пакетах. Первый пакет, который мы поддерживаем, 
— это Livewire. Если у вас установлен Livewire, вы можете запустить:

- `php artisan make:livewire counter --module=[название модуля]`

### Компоненты Blade

Ваши [компоненты Laravel Blade](https://laravel.com/docs/blade#components) будут автоматически зарегистрированы для вас под 
[пространством имён](https://laravel.com/docs/9.x/blade#manually-registering-package-components) компонента. 
Несколько примеров:


| File                                                               | Component                      |
|--------------------------------------------------------------------|--------------------------------|
| `app-modules/demo/src/View/Components/Basic.php`                   | `<x-demo::basic />`            |
| `app-modules/demo/src/View/Components/Nested/One.php`              | `<x-demo::nested.one />`       |
| `app-modules/demo/src/View/Components/Nested/Two.php`              | `<x-demo::nested.two />`       |
| `app-modules/demo/resources/components/anonymous.blade.php`        | `<x-demo::anonymous />`        |
| `app-modules/demo/resources/components/anonymous/index.blade.php`  | `<x-demo::anonymous />`        |
| `app-modules/demo/resources/components/anonymous/nested.blade.php` | `<x-demo::anonymous.nested />` |

### Локализация

Ваши [переводы Laravel](https://laravel.com/docs/11.x/localization#defining-translation-strings) также будут автоматически зарегистрированы под пространством имён компонента. 
Например, если у вас есть файл перевода по адресу:

`app-modules/demo/resources/lang/en/messages.php`

Вы можете получить доступ к этим переводам с помощью: `__('demo::messages.welcome');`

### Настройка структуры модуля по умолчанию

Когда вы вызываете `make:module`, Modular создаёт базовую структуру для вас. Если вы хотите настроить это поведение, 
вы можете опубликовать конфигурационный файл `app-modules.php`
и добавить свои собственные заготовки.

Имена файлов и содержимое файлов поддерживают ряд заполнителей. Среди них:

 - `StubBasePath`
 - `StubModuleNamespace`
 - `StubComposerNamespace`
 - `StubModuleNameSingular`
 - `StubModuleNamePlural`
 - `StubModuleName`
 - `StubClassNamePrefix`
 - `StubComposerName`
 - `StubMigrationPrefix`
 - `StubFullyQualifiedTestCaseBase`
 - `StubTestCaseBase`

## Сравнение

[InterNACHI/modular](https://github.com/InterNACHI/modular) - Это замечательный пакет для старта, но для решения задач, 
которые стояли перед нами, его функционала оказалось недостаточно. Было принято решение создать форк и модифицировать 
его под наши потребности.
