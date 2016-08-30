<?php

namespace Cooperl\Database\DB2\Connectors;

/**
 * Class ODBCZOSConnector
 *
 * @package Cooperl\Database\DB2\Connectors
 */
class ODBCZOSConnector extends ODBCConnector
{
    /**
     * @param array $config
     *
     * @return string
     */
    protected function getDsn(array $config)
    {
        $dsnParts = [
            'odbc:DRIVER={IBM DB2 ODBC DRIVER}',
            'Database=%s',
            'Hostname=%s',
            'Port=%s',
            'Protocol=TCPIP',
            'Uid=%s',
            'Pwd=%s',
            '', // Just to add a semicolon to the end of string
        ];

        $dsnConfig = [
            $config['database'],
            $config['host'],
            $config['port'],
            $config['username'],
            $config['password'],
        ];

        return sprintf(implode(';', $dsnParts), ...$dsnConfig);
    }
}
