<?php

namespace Cooperl\DB2\Database\Schema;

use Closure;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class Builder
 *
 * @package Cooperl\DB2\Database\Schema
 */
class Builder extends \Illuminate\Database\Schema\Builder
{

    /**
     * Determine if the given table exists.
     *
     * @param  string $table
     *
     * @return bool
     */
    public function hasTable($table)
    {
        $sql = $this->grammar->compileTableExists();
        $schemaTable = explode(".", $table);

        if (count($schemaTable) > 1) {
            $schema = $schemaTable[0];
            $table = $this->connection->getTablePrefix().$schemaTable[1];
        } else {
            $schema = $this->connection->getDefaultSchema();
            $table = $this->connection->getTablePrefix().$table;
        }

        return count($this->connection->select($sql, [$schema, $table])) > 0;
    }

    /**
     * Get the column listing for a given table.
     *
     * @param  string $table
     *
     * @return array
     */
    public function getColumnListing($table)
    {
        $sql = $this->grammar->compileColumnExists();
        $database = $this->connection->getDatabaseName();
        $table = $this->connection->getTablePrefix().$table;
        $results = $this->connection->select($sql, [$database, $table]);

        return $this->connection->getPostProcessor()->processColumnListing($results);
    }

    /**
     * Execute the blueprint to build / modify the table.
     *
     * @param Blueprint $blueprint
     */
    protected function build(Blueprint $blueprint)
    {
        $schemaTable = explode(".", $blueprint->getTable());

        if (count($schemaTable) > 1) {
            $this->connection->setCurrentSchema($schemaTable[0]);
        }

        $blueprint->build($this->connection, $this->grammar);
        $this->connection->resetCurrentSchema();
    }

    /**
     * Create a new command set with a Closure.
     *
     * @param  string $table
     * @param  \Closure $callback
     *
     * @return \Cooperl\DB2\Database\Schema\Blueprint
     */
    protected function createBlueprint($table, Closure $callback = null)
    {
        if (isset($this->resolver)) {
            return call_user_func($this->resolver, $table, $callback);
        }

        return new \Cooperl\DB2\Database\Schema\Blueprint($table, $callback);
    }
}
