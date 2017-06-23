<?php

namespace Cooperl\Database\DB2\Connectors;

/**
 * Class IBMConnector
 *
 * @package Cooperl\Database\DB2\Connectors
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
        $dsn = "ibm:{$config['database']}";

        return $dsn;
    }
}
