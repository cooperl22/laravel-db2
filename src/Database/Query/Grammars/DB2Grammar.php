<?php

namespace Cooperl\DB2\Database\Query\Grammars;

use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Builder;

/**
 * Class DB2Grammar
 *
 * @package Cooperl\DB2\Database\Query\Grammars
 */
class DB2Grammar extends Grammar
{
    /**
     * The format for database stored dates.
     *
     * @var string
     */
    protected $dateFormat;

    /**
     * Wrap a single string in keyword identifiers.
     *
     * @param string $value
     *
     * @return string
     */
    protected function wrapValue($value)
    {
        if ($value === '*') {
            return $value;
        }

        return str_replace('"', '""', $value);
    }

    /**
     * Compile the "limit" portions of the query.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param int                                $limit
     *
     * @return string
     */
    protected function compileLimit(Builder $query, $limit)
    {
        return "FETCH FIRST $limit ROWS ONLY";
    }

    /**
     * Compile a select query into SQL.
     *
     * @param \Illuminate\Database\Query\Builder $query
     *
     * @return string
     */
    public function compileSelect(Builder $query)
    {
        if (is_null($query->columns)) {
            $query->columns = ['*'];
        }

        $components = $this->compileComponents($query);

        // If an offset is present on the query, we will need to wrap the query in
        // a big "ANSI" offset syntax block. This is very nasty compared to the
        // other database systems but is necessary for implementing features.
        if ($query->offset > 0) {
            return $this->compileAnsiOffset($query, $components);
        }

        return $this->concatenate($components);
    }

    /**
     * Create a full ANSI offset clause for the query.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param array                              $components
     *
     * @return string
     */
    protected function compileAnsiOffset(Builder $query, $components)
    {
        // An ORDER BY clause is required to make this offset query work, so if one does
        // not exist we'll just create a dummy clause to trick the database and so it
        // does not complain about the queries for not having an "order by" clause.
        if (!isset($components['orders'])) {
            $components['orders'] = 'order by 1';
        }

        unset($components['limit']);

        // We need to add the row number to the query so we can compare it to the offset
        // and limit values given for the statements. So we will add an expression to
        // the "select" that will give back the row numbers on each of the records.
        $orderings = $components['orders'];

        $columns = (!empty($components['columns']) ? $components['columns'] . ', ' : 'select');

        if ($columns == 'select *, ' && $query->from) {
            $columns = 'select ' . $this->tablePrefix . $query->from . '.*, ';
        }

        $components['columns'] = $this->compileOver($orderings, $columns);

        // if there are bindings in the order, we need to move them to the select since we are moving the parameter
        // markers there with the OVER statement
        if(isset($query->getRawBindings()['order'])){
            $query->addBinding($query->getRawBindings()['order'], 'select');
            $query->setBindings([], 'order');
        }

        unset($components['orders']);

        // Next we need to calculate the constraints that should be placed on the query
        // to get the right offset and limit from our query but if there is no limit
        // set we will just handle the offset only since that is all that matters.
        $start = $query->offset + 1;

        $constraint = $this->compileRowConstraint($query);

        $sql = $this->concatenate($components);

        // We are now ready to build the final SQL query so we'll create a common table
        // expression from the query and get the records with row numbers within our
        // given limit and offset value that we just put on as a query constraint.
        return $this->compileTableExpression($sql, $constraint);
    }

    /**
     * Compile the over statement for a table expression.
     *
     * @param string $orderings
     * @param        $columns
     *
     * @return string
     */
    protected function compileOver($orderings, $columns)
    {
        return "{$columns} row_number() over ({$orderings}) as row_num";
    }

    /**
     * @param $query
     *
     * @return string
     */
    protected function compileRowConstraint($query)
    {
        $start = $query->offset + 1;

        if ($query->limit > 0) {
            $finish = $query->offset + $query->limit;

            return "between {$start} and {$finish}";
        }

        return ">= {$start}";
    }

    /**
     * Compile a common table expression for a query.
     *
     * @param string $sql
     * @param string $constraint
     *
     * @return string
     */
    protected function compileTableExpression($sql, $constraint)
    {
        return "select * from ({$sql}) as temp_table where row_num {$constraint}";
    }

    /**
     * Compile the "offset" portions of the query.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param int                                $offset
     *
     * @return string
     */
    protected function compileOffset(Builder $query, $offset)
    {
        return '';
    }

    /**
     * Compile an exists statement into SQL.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return string
     */
    public function compileExists(Builder $query)
    {
        $existsQuery = clone $query;

        $existsQuery->columns = [];

        return $this->compileSelect($existsQuery->selectRaw('1 exists')->limit(1));
    }

    /**
     * Get the format for database stored dates.
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat ?? parent::getDateFormat();
    }

    /**
     * Set the format for database stored dates.
     *
     * @param $dateFormat
     */
    public function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    /**
     * Compile the SQL statement to define a savepoint.
     *
     * @param  string  $name
     * @return string
     */
    public function compileSavepoint($name)
    {
        return 'SAVEPOINT '.$name.' ON ROLLBACK RETAIN CURSORS';
    }

    /**
     * Compile an "upsert" statement into SQL.
     *
     * Based on and modified from :
     *  - https://github.com/laravel/framework/blob/338ffa625e4b16ccd3d3481371c9312a245fdc30/src/Illuminate/Database/Query/Grammars/SqlServerGrammar.php
     *  - https://nandaibmi.com/index.php/2019/04/16/db2-upsert-using-merge-into/
     *
     * @param  \Illuminate\Database\Query\Builder $query
     * @param  array $values
     * @param  array $uniqueBy
     * @param  array $update
     *
     * @return  string
     */
    public function compileUpsert(Builder $query, array $values, array $uniqueBy, array $update)
    {
        $columns = $this->columnize(array_keys(reset($values)));
        
        $sql = 'merge into ' . $query->from . ' as temp ';
        
        $parameters  =  collect($values)->map(function ($record) {
            return '(' . $this->parameterizeUpsert($record) . ')';
        })->implode(', ');
        
        $sql .= 'using (values ' . $parameters . ') as merge (' . $columns . ') ';

        $on = collect($uniqueBy)->map(function ($column) {
            return 'temp.' . $column . ' = merge.' . $column;
        })->implode(' and ');

        $sql .= 'on ' . $on . ' ';

        if ($update) {
            $update = collect($update)->map(function ($value) {
                return 'temp.' . $value . ' = ' . 'merge.' . $value;
            })->implode(', ');

            $sql .= 'when matched then update set ' . $update . ' ';
        }

        $mergeValues = collect(array_keys(reset($values)))->map(function ($column) {
            return 'merge.' . $column;
        })->implode(', ');

        $sql .= 'when not matched then insert (' . $columns . ') values (' . $mergeValues . ')';

        return $sql;
    }

    /**
     * Parameterize Upsert. Values need to be casted
     * From VARCHAR it is almost always posible to cast back to another type. See table (Table 1. Supported Casts between Built-in Data Types) https://www.ibm.com/support/producthub/db2/docs/content/SSEPGG_11.5.0/com.ibm.db2.luw.sql.ref.doc/doc/r0008478.html
     *
     * @param   array $values
     *
     * @return  string
     */
    private function parameterizeUpsert($record)
    {
        return collect($record)->map(function ($value, $key) {
            return 'CAST(' . $this->parameter($value) . ' as VARCHAR(' . mb_strlen((string) $value) . '))';
        })->implode(', ');
    }
}
