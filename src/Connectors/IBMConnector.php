<?php
namespace Cooperl\Database\DB2\Connectors;

use Illuminate\Database\Connectors\Connector;
use Illuminate\Database\Connectors\ConnectorInterface;

use PDO;

class IBMConnector extends Connector implements ConnectorInterface
{

    public function connect(array $config)
    {
        $dsn = $this->getDsn($config);

        $options = [
            PDO::I5_ATTR_DBC_SYS_NAMING => false,
            PDO::I5_ATTR_COMMIT => PDO::I5_TXN_NO_COMMIT,
            PDO::I5_ATTR_JOB_SORT => false
        ];

        // Naming mode
        switch ($config['naming']) {
            case 1:
                $options[PDO::I5_ATTR_DBC_SYS_NAMING] = true;
                break;
            case 0:
            default:
                $options[PDO::I5_ATTR_DBC_SYS_NAMING] = false;
                break;
        }

        // Isolation mode
        switch ($config['commitMode']) {
            case 1:
                $options[PDO::I5_ATTR_COMMIT] = PDO::I5_TXN_READ_COMMITTED;
                break;
            case 2:
                $options[PDO::I5_ATTR_COMMIT] = PDO::I5_TXN_READ_UNCOMMITTED;
                break;
            case 3:
                $options[PDO::I5_ATTR_COMMIT] = PDO::I5_TXN_REPEATABLE_READ;
                break;
            case 4:
                $options[PDO::I5_ATTR_COMMIT] = PDO::I5_TXN_SERIALIZABLE;
                break;
            case 0:
            default:
                $options[PDO::I5_ATTR_COMMIT] = PDO::I5_TXN_NO_COMMIT;
                break;
        }

        // Job sort mode
        switch ($config['jobSort']) {
            case 1:
                $options[PDO::I5_ATTR_DBC_SYS_NAMING] = true;
                break;
            case 0:
            default:
                $options[PDO::I5_ATTR_DBC_SYS_NAMING] = false;
                break;
        }

        $options = $this->getOptions($config) + $options;

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
