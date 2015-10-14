# laravel-db2

## This is a fork of [cooperl22's laravel-db2](https://www.github.com/cooperl22/laravel-db2)

[![Latest Stable Version](https://poser.pugx.org/cooperl/laravel-db2/v/stable)](https://packagist.org/packages/cooperl/laravel-db2)
[![Total Downloads](https://poser.pugx.org/cooperl/laravel-db2/downloads)](https://packagist.org/packages/cooperl/laravel-db2)
[![Latest Unstable Version](https://poser.pugx.org/cooperl/laravel-db2/v/unstable)](https://packagist.org/packages/cooperl/laravel-db2)
[![License](https://poser.pugx.org/cooperl/laravel-db2/license)](https://packagist.org/packages/cooperl/laravel-db2)

laravel-db2 is a simple DB2 service provider for Laravel.
It provides DB2 Connection by extending the Illuminate Database component of the laravel framework.

---

- [Installation](#installation)
- [Registering the Package](#registering-the-package)
- [Configuration](#configuration)
- [Usage](#usage)

## Installation

Add laravel-db2 to your composer.json file:

```
"require": {
    "michaelb/laravel-db2": "~1.0"
}
```

Use [composer](http://getcomposer.org) to install this package.

```
$ composer update
```

### Registering the Package

Add the laravel-db2 Service Provider to your config in ``app/config/app.php``:

```php
'providers' => [
    MichaelB\Database\DB2\DB2ServiceProvider::class
],
```

### Configuration

There are two ways to configure laravel-db2. You can choose the most convenient way for you. You can put your DB2 credentials into ``app/config/database.php`` (option 1) file or use package config file which you can be generated through command line by artisan (option 2).

#### Option 1: Configure DB2 using ``app/config/database.php`` file 

Simply add this code at the end of your ``app/config/database.php`` file:

```php
    /*
    |--------------------------------------------------------------------------
    | DB2 Databases
    |--------------------------------------------------------------------------
    */

    'odbc' => [
        'driver'         => 'odbc',
        'host'           => '',
        'database'       => '',
        'username'       => '',
        'password'       => '',
        'charset'        => 'utf8',
        'ccsid'          => 1208,
        'prefix'         => '',
        'schema'         => '',
        'i5_libl'        => '',
        'i5_lib'         => '',
        'i5_commit'      => 0,
        'i5_naming'      => 0,
        'i5_date_fmt'    => 5,
        'i5_date_sep'    => 0,
        'i5_decimal_sep' => 0,
        'i5_time_fmt'    => 0,
        'i5_time_sep'    => 0,
        'options'  => [
            PDO::ATTR_CASE => PDO::CASE_LOWER,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false
            ]
    ],

    'ibm' => [
        'driver'         => 'ibm',
        'host'           => '',
        'database'       => '',
        'username'       => '',
        'password'       => '',
        'charset'        => 'utf8',
        'ccsid'          => 1208,
        'prefix'         => '',
        'schema'         => '',
        'i5_libl'        => '',
        'i5_lib'         => '',
        'i5_commit'      => 0,
        'i5_naming'      => 0,
        'i5_date_fmt'    => 5,
        'i5_date_sep'    => 0,
        'i5_decimal_sep' => 0,
        'i5_time_fmt'    => 0,
        'i5_time_sep'    => 0,
        'options'  => [
            PDO::ATTR_CASE => PDO::CASE_LOWER,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false
        ]
    ],

```
driver setting is either 'odbc' for ODBC connection or 'ibm' for pdo_ibm connection
Then if driver is 'odbc', database must be set to ODBC connection name.
if driver is 'ibm', database must be set to IBMi database name (WRKRDBDIRE).

#### Option 2: Configure DB2 using package config file

Run on the command line from the root of your project:

```
$ php artisan config:publish cooperl/laravel-db2
```

Set your laravel-db2 credentials in ``app/config/packages/michaelb/laravel-db2/config.php``
the same way as above


## Usage

Consult the [Laravel framework documentation](http://laravel.com/docs).
