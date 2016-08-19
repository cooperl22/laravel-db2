<?php

namespace Cooperl\Database\DB2\Connectors;

use Illuminate\Database\Connectors\Connector;
use Illuminate\Database\Connectors\ConnectorInterface;

/**
 * Class ODBCConnector
 *
 * @package Cooperl\Database\DB2\Connectors
 */
class ODBCConnector extends Connector implements ConnectorInterface
{

    /**
     * @param array $config
     *
     * @return \PDO
     */
    public function connect(array $config)
    {
        $dsn = $this->getDsn($config);
        $options = $this->getOptions($config);
        $connection = $this->createConnection($dsn, $config, $options);

        if (isset($config['schema'])) {
            $schema = $config['schema'];

            $connection->prepare('set schema '.$schema)->execute();
        }

        return $connection;
    }

    /**
     * @param array $config
     *
     * @return string
     */
    protected function getDsn(array $config)
    {
        $dsnParts = [
            'odbc:DRIVER=%s', 'SYSTEM=%s', 'UserID=%s', 'Password=%s', 'DATABASE=%s', 'SIGNON=%s', 'SSL=%s',
            'CommitMode=%s', 'ConnectionType=%s', 'DefaultLibraries=%s', 'Naming=%s', 'UNICODESQL=%s', 'DateFormat=%s',
            'DateSeperator=%s', 'Decimal=%s', 'TimeFormat=%s', 'TimeSeparator=%s', 'BLOCKFETCH=%s', 'BlockSizeKB=%s',
            'AllowDataCompression=%s', 'CONCURRENCY=%s', 'LAZYCLOSE=%s', 'MaxFieldLength=%s', 'PREFETCH=%s',
            'QUERYTIMEOUT=%s', 'DefaultPkgLibrary=%s', 'DefaultPackage=%s', 'ExtendedDynamic=%s', 'QAQQINILibrary=%s',
            'SQDIAGCODE=%s', 'LANGUAGEID=%s', 'SORTTABLE=%s', 'SortSequence=%s', 'SORTWEIGHT=%s',
            'AllowUnsupportedChar=%s', 'CCSID=%s', 'GRAPHIC=%s', 'ForceTranslation=%s', 'ALLOWPROCCALLS=%s',
            'DB2SQLSTATES=%s', 'DEBUG=%s', 'TRUEAUTOCOMMIT=%s', 'CATALOGOPTIONS=%s', 'LibraryView=%s', 'ODBCRemarks=%s',
            'SEARCHPATTERN=%s', 'TranslationDLL=%s', 'TranslationOption=%s', 'MAXTRACESIZE=%s', 'MultipleTraceFiles=%s',
            'TRACE=%s', 'TRACEFILENAME=%s', 'ExtendedColInfo=%s',
            '', // Just to add a semicolon to the end of string
        ];

        $dsnConfig = [
            // General settings
            $config['driverName'], $config['host'], $config['username'], $config['password'],
            //Server settings
            $config['database'], $config['signon'], $config['ssl'], $config['commitMode'], $config['connectionType'],
            $config['defaultLibraries'], $config['naming'], $config['unicodeSql'],
            // Format settings
            $config['dateFormat'], $config['dateSeperator'], $config['decimal'], $config['timeFormat'],
            $config['timeSeparator'],
            // Performances settings
            $config['blockFetch'], $config['blockSizeKB'], $config['allowDataCompression'], $config['concurrency'],
            $config['lazyClose'], $config['maxFieldLength'], $config['prefetch'], $config['queryTimeout'],
            // Modules settings
            $config['defaultPkgLibrary'], $config['defaultPackage'], $config['extendedDynamic'],
            // Diagnostic settings
            $config['QAQQINILibrary'], $config['sqDiagCode'],
            // Sort settings
            $config['languageId'], $config['sortTable'], $config['sortSequence'], $config['sortWeight'],
            // Conversion settings
            $config['allowUnsupportedChar'], $config['ccsid'], $config['graphic'], $config['forceTranslation'],
            // Other settings
            $config['allowProcCalls'], $config['DB2SqlStates'], $config['debug'], $config['trueAutoCommit'],
            $config['catalogOptions'], $config['libraryView'], $config['ODBCRemarks'], $config['searchPattern'],
            $config['translationDLL'], $config['translationOption'], $config['maxTraceSize'],
            $config['multipleTraceFiles'], $config['trace'], $config['traceFilename'], $config['extendedColInfo'],
        ];

        return sprintf(implode(';', $dsnParts), ...$dsnConfig);
    }
}
