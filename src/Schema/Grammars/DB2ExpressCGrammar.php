<?php

namespace Cooperl\Database\DB2\Schema\Grammars;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Fluent;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Database\Schema\Blueprint;

class DB2ExpressCGrammar extends DB2Grammar
{
    /**
     * Compile the query to determine the list of tables.
     *
     * @return string
     */
    public function compileTableExists()
    {
        return 'select * from syspublic.all_tables where table_schema = upper(?) and table_name = upper(?)';
    }

    /**
     * Compile the query to determine the list of columns.
     *
     * @return string
     */
    public function compileColumnExists()
    {
        return 'select column_name from syspublic.all_ind_columns where table_schema = upper(?) and table_name = upper(?)';
    }
}
