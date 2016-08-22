# laravel-db2

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
    "cooperl/laravel-db2": "~2.0"
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
    'Cooperl\Database\DB2\DB2ServiceProvider'
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

    'ibmi' => [
        'driver'               => 'odbc' / 'ibm' / 'odbczos',
        'driverName'           => '{IBM i Access ODBC Driver}' / '{iSeries Access ODBC Driver}',
         // General settings
        'host'                 => 'server',
        'username'             => '',
        'password'             => '',
        //Server settings
        'database'             => 'WRKRDBDIRE entry',
        'prefix'               => '',
        'schema'               => 'default schema',
	'port'                 => 50000,
        'signon'               => 3,
        'ssl'                  => 0,
        'commitMode'           => 2,
        'connectionType'       => 0,
        'defaultLibraries'     => '',
        'naming'               => 0,
        'unicodeSql'           => 0,
        // Format settings
        'dateFormat'           => 5,
        'dateSeperator'        => 0,
        'decimal'              => 0,
        'timeFormat'           => 0,
        'timeSeparator'        => 0,
        // Performances settings
        'blockFetch'           => 1,
        'blockSizeKB'          => 32,
        'allowDataCompression' => 1,
        'concurrency'          => 0,
        'lazyClose'            => 0,
        'maxFieldLength'       => 15360,
        'prefetch'             => 0,
        'queryTimeout'         => 1,
        // Modules settings
        'defaultPkgLibrary'    => 'QGPL',
        'defaultPackage'       => 'A/DEFAULT(IBM),2,0,1,0',
        'extendedDynamic'      => 1,
        // Diagnostic settings
        'QAQQINILibrary'       => '',
        'sqDiagCode'           => '',
        // Sort settings
        'languageId'           => 'ENU',
        'sortTable'            => '',
        'sortSequence'         => 0,
        'sortWeight'           => 0,
        'jobSort'              => 0,
        // Conversion settings
        'allowUnsupportedChar' => 0,
        'ccsid'                => 1208,
        'graphic'              => 0,
        'forceTranslation'     => 0,
        // Other settings
        'allowProcCalls'       => 0,
        'DB2SqlStates'         => 0,
        'debug'                => 0,
        'trueAutoCommit'       => 0,
        'catalogOptions'       => 3,
        'libraryView'          => 0,
        'ODBCRemarks'          => 0,
        'searchPattern'        => 1,
        'translationDLL'       => '',
        'translationOption'    => 0,
        'maxTraceSize'         => 0,
        'multipleTraceFiles'   => 1,
        'trace'                => 0,
        'traceFilename'        => '',
        'extendedColInfo'      => 0,
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

Set your laravel-db2 credentials in ``app/config/packages/cooperl/laravel-db2/config.php``
the same way as above


## Usage

Consult the [Laravel framework documentation](http://laravel.com/docs).
