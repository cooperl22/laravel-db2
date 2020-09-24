<?php

namespace Cooperl\DB2\Database\Schema;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Grammars\Grammar;

/**
 * Class Blueprint
 *
 * @package Cooperl\DB2\Database\Schema
 */
class Blueprint extends \Illuminate\Database\Schema\Blueprint
{

   /**
     * The sequence number of reply list entries.
     *
     * @var int
     */
    private $replyListSequenceNumber;

    /**
     * Get the sequence number of reply list entries.
     *
     * @return int
     */
    public function getReplyListSequenceNumber()
    {
        return $this->replyListSequenceNumber;
    }

    /**
     * Set the sequence number of reply list entries.
     *
     * @param int $replyListSequenceNumber
     * @return void
     */
    public function setReplyListSequenceNumber(int $replyListSequenceNumber)
    {
        return $this->replyListSequenceNumber = $replyListSequenceNumber;
    }

    /**
     * Get the raw SQL statements for the blueprint.
     *
     * @param  \Illuminate\Database\Connection  $connection
     * @param  \Illuminate\Database\Schema\Grammars\Grammar  $grammar
     * @return array
     */
    public function toSql(Connection $connection, Grammar $grammar)
    {
        $this->addReplyListEntryCommands($connection);

        return parent::toSql($connection, $grammar);
    }

    /**
     * Add the commands that are necessary to DROP and Rename statements on IBMi.
     *
     * @param  \Illuminate\Database\Connection  $connection
     * @return void
     */
    protected function addReplyListEntryCommands(Connection $connection)
    {
        if ($this->commandsNamed(['dropColumn', 'renameColumn'])->count() > 0) {
            array_unshift($this->commands, $this->createCommand('addReplyListEntry'), $this->createCommand('changeJob'));
            array_push($this->commands, $this->createCommand('removeReplyListEntry'));
        }
    }

    /**
     * Specify a system name for the table.
     *
     * @param  string $systemName
     */
    public function forSystemName($systemName)
    {
        $this->systemName = $systemName;
    }

    /**
     * Specify a label for the table.
     *
     * @param  string $label
     *
     * @return \Illuminate\Support\Fluent
     */
    public function label($label)
    {
        return $this->addCommand('label', compact('label'));
    }

    /**
     * Add a new index command to the blueprint.
     *
     * @param  string $type
     * @param  string|array $columns
     * @param  string $index
     *
     * @return \Illuminate\Support\Fluent
     */
    protected function indexCommand($type, $columns, $index, $algorithm = null)
    {
        $columns = (array) $columns;

        switch ($type) {
            case 'index':
                $indexSystem = false;

                if (!is_null($index)) {
                    //$indexSystem = $index;
                }

                $index = $this->createIndexName($type, $columns);

                return $this->addCommand($type, compact('index', 'indexSystem', 'columns'));
            default:
                break;
        }

        // If no name was specified for this index, we will create one using a basic
        // convention of the table name, followed by the columns, followed by an
        // index type, such as primary or index, which makes the index unique.
        if (is_null($index)) {
            $index = $this->createIndexName($type, $columns);
        }

        return $this->addCommand($type, compact('index', 'columns'));
    }

    /**
     * Create a new boolean column on the table.
     *
     * @param  string $column
     *
     * @return \Illuminate\Support\Fluent
     */
    public function boolean($column)
    {
        $prefix = $this->table;
        // Aucune utilité d'avoir le nom du schéma dans le préfixe de la contrainte check pour le type booléen
        $schemaTable = explode(".", $this->table);

        if (count($schemaTable) > 1) {
            $prefix = $schemaTable[1];
        }

        return $this->addColumn('boolean', $column, ['prefix' => $prefix]);
    }

    /**
     * Create a new numeric column on the table.
     *
     * @param  string $column
     * @param  int $total
     * @param  int $places
     *
     * @return \Illuminate\Support\Fluent
     */
    public function numeric($column, $total = 8, $places = 2)
    {
        return $this->addColumn('numeric', $column, compact('total', 'places'));
    }

    public function synchro($index, $masterizable = false)
    {

        $this->string('id_sync', 20)
             ->index($index);
        $this->string('hashcode', 32);

        if (true === $masterizable) {
            $this->boolean('data_master')
                 ->default(true);
        }
    }

    /**
     * @param string $index
     */
    public function dropSynchro($index)
    {
        $this->dropColumn('id_sync', 'hashcode');
        $this->dropIndex($index);
    }
}
