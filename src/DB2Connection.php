<?php

namespace Cooperl\Database\DB2;

use Cooperl\Database\DB2\Query\Processors\DB2ZOSProcessor;
use PDO;

use Illuminate\Database\Connection;

use Cooperl\Database\DB2\Schema\Builder;
use Cooperl\Database\DB2\Query\Processors\DB2Processor;
use Cooperl\Database\DB2\Query\Grammars\DB2Grammar as QueryGrammar;
use Cooperl\Database\DB2\Schema\Grammars\DB2Grammar as SchemaGrammar;

/**
 * Class DB2Connection
 *
 * @package Cooperl\Database\DB2
 */
class DB2Connection extends Connection
{
    /**
     * The name of the default schema.
     *
     * @var string
     */
    protected $defaultSchema;
    /**
     * The name of the current schema in use.
     *
     * @var string
     */
    protected $currentSchema;

    public function __construct(PDO $pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        parent::__construct($pdo, $database, $tablePrefix, $config);
        $this->currentSchema = $this->defaultSchema = strtoupper($config['schema']);
    }

    /**
     * Get the name of the default schema.
     *
     * @return string
     */
    public function getDefaultSchema()
    {
        return $this->defaultSchema;
    }

    /**
     * Reset to default the current schema.
     *
     * @return string
     */
    public function resetCurrentSchema()
    {
        $this->setCurrentSchema($this->getDefaultSchema());
    }

    /**
     * Set the name of the current schema.
     *
     * @param $schema
     *
     * @return string
     */
    public function setCurrentSchema($schema)
    {
        //$this->currentSchema = $schema;
        $this->statement('SET SCHEMA ?', [strtoupper($schema)]);
    }

    /**
     * Get a schema builder instance for the connection.
     *
     * @return \Illuminate\Database\Schema\MySqlBuilder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new Builder($this);
    }

    /**
     * @return \Illuminate\Database\Grammar
     */
    protected function getDefaultQueryGrammar()
    {
        $defaultGrammar = new QueryGrammar;

        if (array_key_exists('date_format', $this->config)) {
            $defaultGrammar->setDateFormat($this->config['date_format']);
        }

        return $this->withTablePrefix($defaultGrammar);
    }

    /**
     * Default grammar for specified Schema
     *
     * @return \Illuminate\Database\Grammar
     */
    protected function getDefaultSchemaGrammar()
    {
        switch ($this->config['driver']) {
            case 'db2_expressc_odbc':
                $defaultGrammar = $this->withTablePrefix(new DB2ExpressCGrammar);
                break;
            default:
                $defaultGrammar = $this->withTablePrefix(new SchemaGrammar);
                break;
        }

        return $defaultGrammar;
    }

    /**
     * Get the default post processor instance.
     *
     * @return \Illuminate\Database\Query\Processors\PostgresProcessor
     */
    protected function getDefaultPostProcessor()
    {
        switch ($this->config['driver']) {
            case 'db2_zos_odbc':
                $defaultProcessor = new DB2ZOSProcessor;
                break;
            default:
                $defaultProcessor = new DB2Processor;
                break;
        }

        return $defaultProcessor;
    }
}
