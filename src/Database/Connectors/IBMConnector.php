<?php

namespace Cooperl\DB2\Database\Connectors;

/**
 * Class IBMConnector
 *
 * @package Cooperl\DB2\Database\Connectors
 */
class IBMConnector extends DB2Connector
{
    /**
     * @param array $config
     *
     * @return string
     */
    protected function getDsn(array $config)
    {
        $dsn = "ibm:DRIVER={$config['driverName']};DATABASE={$config['database']};HOSTNAME={$config['host']};PORT={$config['port']};PROTOCOL=TCPIP;";

        return $dsn;
    }
}
