<?php

namespace Cooperl\DB2;

use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;
use Cooperl\DB2\Database\DB2Connection;
use Cooperl\DB2\Database\Connectors\ODBCConnector;
use Cooperl\DB2\Database\Connectors\IBMConnector;
use Cooperl\DB2\Database\Connectors\ODBCZOSConnector;
use Cooperl\DB2\Queue\DB2Connector;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;

/**
 * Class DB2ServiceProvider
 *
 * @package Cooperl\DB2\Database
 */
class DB2ServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/config/db2.php';
        $this->publishes([$configPath => $this->getConfigPath()], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // get the configs
        $conns = is_array(config('db2.connections')) ? config('db2.connections') : [];

        // Add my database configurations to the default set of configurations
        config(['database.connections' => array_merge($conns, config('database.connections'))]);

        // Extend the connections with pdo_odbc and pdo_ibm drivers
        foreach (config('database.connections') as $conn => $config) {
            // Only use configurations that feature a "odbc", "ibm" or "odbczos" driver
            if (!isset($config['driver']) || !in_array($config['driver'], [
                    'db2_ibmi_odbc',
                    'db2_ibmi_ibm',
                    'db2_zos_odbc',
                    'db2_expressc_odbc',
                ])
            ) {
                continue;
            }

            // Create a connector
            $this->app['db']->extend($conn, function($config, $name) {
                $config['name'] = $name;
                switch ($config['driver']) {
                    case 'db2_expressc_odbc':
                    case 'db2_ibmi_odbc':
                        $connector = new ODBCConnector();
                        break;

                    case 'db2_zos_odbc':
                        $connector = new ODBCZOSConnector();
                        break;

                    case 'db2_ibmi_ibm':
                    default:
                        $connector = new IBMConnector();
                        break;
                }

                $db2Connection = $connector->connect($config);

                return new DB2Connection($db2Connection, $config["database"], $config["prefix"], $config);
            });
        }

        $this->app->extend(
            'queue',
            function (QueueManager $queueManager) {
                $queueManager->addConnector('db2_odbc', function () {
                    return new DB2Connector($this->app['db']);
                });

                return $queueManager;
            }
        );
    }

    /**
     * Get the config path
     *
     * @return string
     */
    protected function getConfigPath()
    {
        if ($this->app instanceof LaravelApplication) {
            return config_path('db2.php');
        } elseif ($this->app instanceof LumenApplication) {
            return base_path('config/db2.php');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
