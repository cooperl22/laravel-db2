<?php
namespace Cooperl\Database\DB2\Query\Processors;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\Processor;
use Cooperl\Database\DB2\Query\Grammars\DB2Grammar;

class DB2Processor extends Processor {

    /**
     * Process an "insert get ID" query.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string  $sql
     * @param  array   $values
     * @param  string  $sequence
     * @return int/array
     */
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        $sequenceStr = $sequence ?: 'id';
        if (is_array($sequence))
        {
            $grammar = new DB2Grammar;
            $sequenceStr = $grammar->columnize($sequence);
        }
        $sql = 'select ' . $sequenceStr . ' from new table (' . $sql;
        $sql .= ')';    
        $results = $query->getConnection()->select($sql, $values);
        if (is_array($sequence))
        {
            return array_values((array) $results[0]);
        }
        else
        {
            $result = (array) $results[0];
            $id = $result[$sequenceStr];
            return is_numeric($id) ? (int) $id : $id;
        }
        
    }

}
