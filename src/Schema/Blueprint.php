<?php

namespace Cooperl\Database\DB2\Schema;

/**
 * Class Blueprint
 *
 * @package Cooperl\Database\DB2\Schema
 */
class Blueprint extends \Illuminate\Database\Schema\Blueprint
{
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
    protected function indexCommand($type, $columns, $index)
    {
        $columns = (array) $columns;

        switch ($type) {
            case 'index':
                $indexSystem = false;

                if (!is_null($index)) {
                    $indexSystem = $index;
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
}
