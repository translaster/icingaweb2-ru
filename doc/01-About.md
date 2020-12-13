# Об Icinga Web 2 <a id="about"></a>

Icinga Web 2 - это мощный PHP-фреймворк для веб-приложений, который поставляется в чистом и сокращенном дизайне.
Он быстрый, отзывчивый, доступный и легко расширяемый посредством модулей.

## Модуль мониторинга <a id="about-monitoring"></a>

This is the core module for most Icinga Web 2 users.

It provides an intuitive user interface for monitoring with Icinga 2.
Especially there are lots of list and detail views (e.g. for hosts and services)
you can sort and filter depending on what you want to see.

You can also control the monitoring process itself by sending external commands to Icinga.
Most such actions (like rescheduling a check) can be done with just a single click.

More details about this module can be found in [this chapter](../modules/monitoring/doc/01-About.md#monitoring-module-about).

## Установка <a id="about-installation"></a>

Icinga Web 2 can be installed easily from packages from the official package repositories.
Setting it up is also easy with the web based setup wizard.

See [here](02-Installation.md#installation) for more information about the installation.

## Конфигурирование <a id="about-configuration"></a>

Icinga Web 2 can be configured via the user interface and .ini files.

See [here](03-Configuration.md#configuration) for more information about the configuration.

## Аутентификация <a id="about-authentication"></a>

With Icinga Web 2 you can authenticate against relational databases, LDAP and more.
These authentication methods can be easily configured (via the corresponding .ini file).

See [here](05-Authentication.md#authentication) for more information about
the different authentication methods available and how to configure them.

## Авторизация <a id="about-authorization"></a>

In Icinga Web 2 there are permissions and restrictions to allow and deny (respectively)
roles to view or to do certain things.
These roles can be assigned to users and groups.

See [here](06-Security.md#security) for more information about authorization
and how to configure roles.

## Пользовательские предпочтения <a id="about-preferences"></a>

Besides the global configuration each user has individual configuration options
like the interface's language or the current timezone.
They can be stored either in a database or in .ini files.

See [here](07-Preferences.md#preferences) for more information about a user's preferences
and how to configure their storage type.

## Документация <a id="about-documentation"></a>

With the documentation module you can read the documentation of the framework (and any module) directly in the user interface.

The module can also export the documentation to PDF.

More details about this module can be found in [this chapter](../modules/doc/doc/01-About.md#doc-module-about).

## Перевод <a id="about-translation"></a>

Icinga Web 2 and all modules by Icinga utilize gettext to provide translations into other languages from the default
English (en_US). However, the actual language specific files (locales) are not separately included in every project.

Icinga uses a central repository to manage locales: https://github.com/Icinga/L10n

If you want to provide or update a translation for your own language, please head over there where you will find
[instructions](https://github.com/Icinga/L10n/blob/master/CONTRIBUTING.md) on how to contribute.

## Доступность <a id="about-accessibility"></a>

В интерфейсе Icinga Web 2 могут видеть даже слепые - удобная навигация с помощью программы чтения с экрана и специальные темы для различных видов недостатков зрения позволяют каждому контролировать свои системы без каких-либо нарушений.