<?php
namespace Cooperl\Database\DB2\Query\Processors;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\Processor;
use Cooperl\Database\DB2\Query\Grammars\DB2Grammar;

class DB2Processor extends Processor {

    /**
     * Process the results of a "select" query.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $results
     * @return array
     */
    /*public function processSelect(Builder $query, $results)
    {
        $results = array_map(function($result) {
            foreach (get_object_vars($result) as $field => $value) {
                if (is_string($value))
                {
                    $result->$field = trim(preg_split('/[^\r\n\t\x20-\x7E\xA0-\xFF]/', $value)[0]);
                }
            }

            return $result;
        }, $results);

        return $results;
    }*/

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
