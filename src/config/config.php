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

* naming:
0 = "sql" (as in schema.table)
1 = "system" (as in schema/table)

* dateFormat:
0 = yy/dd (*JUL)
1 = mm/dd/yy (*MDY)
2 = dd/mm/yy (*DMY)
3 = yy/mm/dd (*YMD)
4 = mm/dd/yyyy (*USA)
5 = yyyy-mm-dd (*ISO)
6 = dd.mm.yyyy (*EUR)
7 = yyyy-mm-dd (*JIS)

* dateSeperator:
0 = "/" (forward slash)
1 = "-" (dash)
2 = "." (period)
3 = "," (comma)
4 = " " (blank)

* decimal:
0 = "." (period)
1 = "," (comma)

* timeFormat:
0 = hh:mm:ss (*HMS)
1 = hh:mm AM/PM (*USA)
2 = hh.mm.ss (*ISO)
3 = hh.mm.ss (*EUR)
4 = hh:mm:ss (*JIS)

* timeSeparator:
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

        'ibmi' => [
            'driver'               => 'odbc' / 'ibm',
             // General settings
            'host'                 => 'gigc',
            'username'             => '',
            'password'             => '',
            //Server settings
            'database'             => 'WRKRDBDIRE entry',
            'prefix'               => '',
            'schema'               => 'default schema',
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

    ]

];
