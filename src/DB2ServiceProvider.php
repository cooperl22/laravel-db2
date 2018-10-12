<?php

namespace Cooperl\Database\DB2;

use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;
use Cooperl\Database\DB2\Connectors\ODBCConnector;
use Cooperl\Database\DB2\Connectors\IBMConnector;
use Cooperl\Database\DB2\Connectors\ODBCZOSConnector;
use Illuminate\Support\ServiceProvider;

/**
 * Class DB2ServiceProvider
 *
 * @package Cooperl\Database\DB2
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
