<?php

/*
|--------------------------------------------------------------------------
| DB2 Config
|--------------------------------------------------------------------------
Valeurs définies pour le niveau d'isolation, pour PDO (mot clé "CMT" ou "CommitMode" dans le DSN) :
0 = Commit immediate (*NONE)
1 = Read committed (*CS)
2 = Read uncommitted (*CHG)
3 = Repeatable read (*ALL)
4 = Serializable (*RR)

* i5_naming:
0 = "sql" (as in schema.table)
1 = "system" (as in schema/table)

* i5_date_fmt:
0 = yy/dd (*JUL)
1 = mm/dd/yy (*MDY)
2 = dd/mm/yy (*DMY)
3 = yy/mm/dd (*YMD)
4 = mm/dd/yyyy (*USA)
5 = yyyy-mm-dd (*ISO)
6 = dd.mm.yyyy (*EUR)
7 = yyyy-mm-dd (*JIS)

* i5_date_sep:
0 = "/" (forward slash)
1 = "-" (dash)
2 = "." (period)
3 = "," (comma)
4 = " " (blank)

* i5_decimal_sep:
0 = "." (period)
1 = "," (comma)

* i5_time_fmt:
0 = hh:mm:ss (*HMS)
1 = hh:mm AM/PM (*USA)
2 = hh.mm.ss (*ISO)
3 = hh.mm.ss (*EUR)
4 = hh:mm:ss (*JIS)

* i5_time_sep:
0 = ":" (colon)
1 = "." (period)
2 = "," (comma)
3 = " " (blank)

* PDO::ATTR_CASE:
PDO::CASE_LOWER
PDO::CASE_UPPER
PDO::CASE_NATURAL

*/


return [
    
    'connections' => [

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

    ]

];
