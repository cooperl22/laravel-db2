<?php
namespace Cooperl\Database\DB2\Connectors;

use Illuminate\Database\Connectors\Connector;
use Illuminate\Database\Connectors\ConnectorInterface;

class ODBCConnector extends Connector implements ConnectorInterface
{

    public function connect(array $config)
    {
        $dsn = $this->getDsn($config);

        $options = $this->getOptions($config);

        $connection = $this->createConnection($dsn, $config, $options);

        if (isset($config['schema']))
        {
            $schema = $config['schema'];

          $connection->prepare("set schema $schema")->execute();
        }

        return $connection;
    }

    protected function getDsn(array $config) {
        extract($config);

        $dsn = "odbc:DRIVER={iSeries Access ODBC Driver};"
             . "SYSTEM=$database;"
             . "NAM=$i5_naming;"
             . "DATABASE=$i5_lib;"
             . "DBQ=$i5_libl;"
             . "DFT=$i5_date_fmt;"
             . "DSP=$i5_date_sep;"
             . "DEC=$i5_decimal_sep;"
             . "TFT=$i5_time_fmt;"
             . "TSP=$i5_time_sep;"
             . "CCSID=$ccsid";

        return $dsn;
    }

}
