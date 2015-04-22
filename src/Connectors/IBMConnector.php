<?php
namespace Cooperl\Database\DB2\Connectors;

use Illuminate\Database\Connectors\Connector;
use Illuminate\Database\Connectors\ConnectorInterface;

class IBMConnector extends Connector implements ConnectorInterface
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
        $dsn = "ibm:$database";
        return $dsn;
    }

}
