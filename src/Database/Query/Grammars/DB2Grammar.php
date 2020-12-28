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
        if ($query->unions && $query->aggregate) {
            return $this->compileUnionAggregate($query);
        }

        // If the query does not have any columns set, we'll set the columns to the
        // * character to just get all of the columns from the database. Then we
        // can build the query and concatenate all the pieces together as one.
        $original = $query->columns;

        if (is_null($query->columns)) {
            $query->columns = ['*'];
        }

        $components = $this->compileComponents($query);

        // limit must be handled by wrapWithOffset if an offset is present
        if($query->offset > 0 || $query->unionOffset > 0){
            unset($components['limit']);
        }

        // If an offset is present on the query, we will need to wrap the query in
        // a big "ANSI" offset syntax block. This is very nasty compared to the
        // other database systems but is necessary for implementing features.
        $sql = $this->concatenate($components);

        if ($query->unions) {
            $sql = $this->wrapUnion($sql).' '.$this->compileUnions($query);
        }

        if ($query->offset > 0 || $query->unionOffset > 0){
            $sql = $this->wrapWithOffset($query, $sql, $query->orders, $query->offset);
        }

        $query->columns = $original;

        return $sql;
    }

    /**
     * If an offset is present on the query, we will need to wrap the query in
     * a big "ANSI" offset syntax block. This is very nasty compared to the
     * other database systems but is necessary for implementing features.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $sql
     *
     * @return string
     */
    protected function wrapWithOffset(Builder $query, $sql)
    {
        $orders = $query->unionOrders ?? $query->orders;
        $offset = $query->unionOffset ?? $query->offset;

        // need to gather order details frome the query
        $orderings = $this->compileOrders($query, $orders);

        // if there are bindings in the order, we need to copy them to the select since we are copying the parameter
        // markers there with the OVER statement
        if(isset($query->getRawBindings()['order'])){
            $query->addBinding($query->getRawBindings()['order'], 'select');
        }

        // offset will wrap the query in a temp name 'offset_query', we should select everything from it
        $columns = $this->compileOver($orderings, 'offset_table.*, ');

        // Next we need to calculate the constraints that should be placed on the query
        // to get the right offset and limit from our query but if there is no limit
        // set we will just handle the offset only since that is all that matters.
        $start = ($query->unionOffset ?? $query->offset) + 1;

        $constraint = $this->compileRowConstraint($query);

        $sql = 'select '.$columns.' from ('.$sql.') as offset_table';

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
        $offset = $query->unionOffset ?? $query->offset;
        $limit = $query->unionLimit ?? $query->limit;

        $start = $offset + 1;

        if ($limit > 0) {
            $finish = $offset + $limit;

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
     * Compile the "union" queries attached to the main query.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return string
     */
    protected function compileUnions(Builder $query)
    {
        if(!$query->unionOffset){
            return parent::compileUnions($query);
        }

        $sql = '';

        foreach ($query->unions as $union) {
            $sql .= $this->compileUnion($union);
        }

        // do not compile unionOrders, unionLimit, or unionOffset as they will be covered by wrapWithOffset

        return ltrim($sql);
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
}
