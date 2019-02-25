# laravel-db2

laravel-db2 is a simple DB2 service provider for Laravel.
It provides DB2 Connection by extending the Illuminate Database component of the laravel framework.

## This is a fork of [cooperl22's laravel-db2](https://www.github.com/cooperl22/laravel-db2)

## Fork Detail
DSN has been modified to connect using a connection string, learn more in [PHP/PDO](http://php.net/manual/es/ref.pdo-ibm.connection.php)

---

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)

## Installation
Add laravel-db2 to your composer.json file:
```
"require": {
    "manuelmousett/laravel-db2": "~1.0"
}
```
Use [composer](http://getcomposer.org) to install this package.
```
$ composer update
```

### Configuration
There are two ways to configure laravel-db2. You can choose the most convenient way for you. You can put your DB2 credentials into ``app/config/database.php`` (option 1) file or use package config file which you can generate through command line by artisan (option 2).

Please check appropriate specific DSN parameters for your connection.
For instance here are the ODBC keywords for IBMi
https://www.ibm.com/support/knowledgecenter/fr/ssw_ibm_i_73/rzaik/connectkeywords.htm

If you encounter issues with char fields containing characters outside the invariant character set (for example: "ü") please see : https://www.ibm.com/developerworks/community/forums/html/topic?id=77777777-0000-0000-0000-000014094907
For PHP applications using the UTF8 locale the workaround to prevent the extra garbage data is to set the following connection string keyword:
DEBUG = 65536

#### Option 1: Configure DB2 using ``app/config/database.php`` file
Simply add this code at the end of your ``app/config/database.php`` file:

```php
    /*
    |--------------------------------------------------------------------------
    | DB2 Databases
    |--------------------------------------------------------------------------
    */

    'ibmi' => [
        'driver' => 'db2_ibmi_odbc',
        // or 'db2_ibmi_ibm' / 'db2_zos_odbc' / 'db2_expressc_odbc
        'driverName' => '{IBM i Access ODBC Driver}',
        // or '{iSeries Access ODBC Driver}' / '{IBM i Access ODBC Driver 64-bit}'
        'host' => 'server',
        'username' => '',
        'password' => '',
        'database' => 'WRKRDBDIRE entry',
        'prefix' => '',
        'schema' => 'default schema',
        'port' => 50000,
        'date_format' => 'Y-m-d H:i:s',
        // or 'Y-m-d H:i:s.u' / 'Y-m-d-H.i.s.u'...
        'odbc_keywords' => [
            'SIGNON' => 3,
            'SSL' => 0,
            'CommitMode' => 2,
            'ConnectionType' => 0,
            'DefaultLibraries' => '',
            'Naming' => 0,
            'UNICODESQL' => 0,
            'DateFormat' => 5,
            'DateSeperator' => 0,
            'Decimal' => 0,
            'TimeFormat' => 0,
            'TimeSeparator' => 0,
            'TimestampFormat' => 0,
            'ConvertDateTimeToChar' => 0,
            'BLOCKFETCH' => 1,
            'BlockSizeKB' => 32,
            'AllowDataCompression' => 1,
            'CONCURRENCY' => 0,
            'LAZYCLOSE' => 0,
            'MaxFieldLength' => 15360,
            'PREFETCH' => 0,
            'QUERYTIMEOUT' => 1,
            'DefaultPkgLibrary' => 'QGPL',
            'DefaultPackage' => 'A /DEFAULT(IBM),2,0,1,0',
            'ExtendedDynamic' => 0,
            'QAQQINILibrary' => '',
            'SQDIAGCODE' => '',
            'LANGUAGEID' => 'ENU',
            'SORTTABLE' => '',
            'SortSequence' => 0,
            'SORTWEIGHT' => 0,
            'AllowUnsupportedChar' => 0,
            'CCSID' => 819,
            'GRAPHIC' => 0,
            'ForceTranslation' => 0,
            'ALLOWPROCCALLS' => 0,
            'DB2SQLSTATES' => 0,
            'DEBUG' => 0,
            'TRUEAUTOCOMMIT' => 0,
            'CATALOGOPTIONS' => 3,
            'LibraryView' => 0,
            'ODBCRemarks' => 0,
            'SEARCHPATTERN' => 1,
            'TranslationDLL' => '',
            'TranslationOption' => 0,
            'MAXTRACESIZE' => 0,
            'MultipleTraceFiles' => 1,
            'TRACE' => 0,
            'TRACEFILENAME' => '',
            'ExtendedColInfo' => 0,
        ],
        'options' => [
            PDO::ATTR_CASE => PDO::CASE_LOWER,
            PDO::ATTR_PERSISTENT => false,
            PDO::I5_ATTR_DBC_SYS_NAMING => false,
            PDO::I5_ATTR_COMMIT => PDO::I5_TXN_NO_COMMIT,
            PDO::I5_ATTR_JOB_SORT => false,
            PDO::I5_ATTR_DBC_LIBL => '',
            PDO::I5_ATTR_DBC_CURLIB => '',
        ]
    ],

```
driver setting can be:
- 'db2_ibmi_odbc' for IBMi ODBC connection
- 'db2_ibmi_ibm' for IBMi PDO_IBM connection
- 'db2_zos_odbc' for zOS ODBC connection
- 'db2_expressc_odbc for Express-C ODBC connection

Then if driver is 'db2_*_odbc', database must be set to ODBC connection name.
if driver is 'db2_ibmi_ibm', database must be set to IBMi database name (WRKRDBDIRE).

#### Option 2: Configure DB2 using package config file

Run on the command line from the root of your project:

```
$ php artisan vendor:publish
```

Set your laravel-db2 credentials in ``app/config/db2.php``
the same way as above

## Usage

Consult the [Laravel framework documentation](http://laravel.com/docs).
