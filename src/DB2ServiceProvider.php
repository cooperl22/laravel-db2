<?php
namespace MichaelB\Database\DB2;

use MichaelB\Database\DB2\Connectors\ODBCConnector;
use MichaelB\Database\DB2\Connectors\IBMConnector;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Config;

class DB2ServiceProvider extends ServiceProvider {

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
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        // get the configs
        $conns = is_array(Config::get('laravel-db2::database.connections')) ? Config::get('laravel-db2::database.connections') : [];

        // Add my database configurations to the default set of configurations
        $this->app['config']['database.connections'] = array_merge($conns, $this->app['config']['database.connections']);

        //Extend the connections with pdo_odbc and pdo_ibm drivers
        foreach(Config::get('database.connections') as $conn => $config)
        {

            //Only use configurations that feature a "odbc" or "ibm" driver
            if(!isset($config['driver']) || !in_array($config['driver'], ['odbc', 'ibm']) )
            {
                continue;
            }

            //Create a connector
            $this->app['db']->extend($conn, function($config)
            {        
                switch ($config['driver']) {
                    case 'odbc':
                        $connector = new ODBCConnector();
                        break;
                    case 'ibm':
                        $connector = new IBMConnector();
                        break;
                    default:
                        break;
                }
                $db2Connection = $connector->connect($config);
                return new DB2Connection($db2Connection, $config["database"], $config["prefix"], $config);
            });

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
