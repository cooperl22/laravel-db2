<?php

namespace Cooperl\DB2\Database\Connectors;

/**
 * Class ODBCZOSConnector
 *
 * @package Cooperl\DB2\Database\Connectors
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
            'odbc:DRIVER=%s',
            'Database=%s',
            'Hostname=%s',
            'Port=%s',
            'Protocol=TCPIP',
            'Uid=%s',
            'Pwd=%s',
            '', // Just to add a semicolon to the end of string
        ];

        $dsnConfig = [
            $config['driverName'],
            $config['database'],
            $config['host'],
            $config['port'],
            $config['username'],
            $config['password'],
        ];

        return sprintf(implode(';', $dsnParts), ...$dsnConfig);
    }
}
