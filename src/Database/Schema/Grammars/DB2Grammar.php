<?php

namespace Cooperl\DB2\Database\Schema\Grammars;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Fluent;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Database\Schema\Blueprint;

class DB2Grammar extends Grammar
{
    /**
     * The possible column modifiers.
     *
     * @var array
     */
    protected $preModifiers = ['ForColumn'];
    protected $modifiers = [
        'Nullable',
        'Default',
        'Generated',
        'Increment',
        'StartWith',
        'Before',
        'ImplicitlyHidden',
    ];
    /**
     * The possible column serials
     *
     * @var array
     */
    protected $serials = [
        'smallInteger',
        'integer',
        'bigInteger',
    ];

    /**
     * Wrap a single string in keyword identifiers.
     *
     * @param  string $value
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
     * Compile the query to determine the list of tables.
     *
     * @return string
     */
    public function compileTableExists()
    {
        return 'select * from information_schema.tables where table_schema = upper(?) and table_name = upper(?)';
    }

    /**
     * Compile the query to determine the list of columns.
     *
     * @return string
     */
    public function compileColumnExists()
    {
        return 'select column_name from information_schema.columns where table_schema = upper(?) and table_name = upper(?)';
    }

    /**
     * Compile a create table command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $command
     * @param  \Illuminate\Database\Connection       $connection
     *
     * @return string
     */
    public function compileCreate(Blueprint $blueprint, Fluent $command, Connection $connection)
    {
        $columns = implode(', ', $this->getColumns($blueprint));
        $sql = 'create table ' . $this->wrapTable($blueprint);

        if (isset($blueprint->systemName)) {
            $sql .= ' for system name ' . $blueprint->systemName;
        }

        $sql .= " ($columns)";

        return $sql;
    }

    /**
     * Compile a label command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $command
     * @param  \Illuminate\Database\Connection       $connection
     *
     * @return string
     */
    public function compileLabel(Blueprint $blueprint, Fluent $command, Connection $connection)
    {
        return 'label on table ' . $this->wrapTable($blueprint) . ' is \'' . $command->label . '\'';
    }

    /**
     * Compile the blueprint's column definitions.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     *
     * @return array
     */
    protected function getColumns(Blueprint $blueprint)
    {
        $columns = [];

        foreach ($blueprint->getColumns() as $column) {
            // Each of the column types have their own compiler functions which are tasked
            // with turning the column definition into its SQL format for this platform
            // used by the connection. The column's modifiers are compiled and added.
            //$sql = $this->wrap($column).' '.$this->getType($column);
            $sql = $this->addPreModifiers($this->wrap($column), $blueprint, $column);
            $sql .= ' ' . $this->getType($column);

            $columns[] = $this->addModifiers($sql, $blueprint, $column);
        }

        return $columns;
    }

    /**
     * Add the column modifiers to the definition.
     *
     * @param  string                                $sql
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $column
     *
     * @return string
     */
    protected function addPreModifiers($sql, Blueprint $blueprint, Fluent $column)
    {
        foreach ($this->preModifiers as $preModifier) {
            if (method_exists($this, $method = "modify{$preModifier}")) {
                $sql .= $this->{$method}($blueprint, $column);
            }
        }

        return $sql;
    }

    /**
     * Compile a create table command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $command
     *
     * @return string
     */
    public function compileAdd(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);
        $columns = $this->prefixArray('add', $this->getColumns($blueprint));
        $statements = [];

        foreach ($columns as $column) {
            $statements[] = 'alter table ' . $table . ' ' . $column;
        }

        return $statements;
    }

    /**
     * Compile a primary key command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $command
     *
     * @return string
     */
    public function compilePrimary(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);
        $columns = $this->columnize($command->columns);

        // Aucune utilité d'avoir le nom du schéma dans le nom de la contrainte Primary
        $schemaTable = explode(".", $table);

        if (count($schemaTable) > 1) {
            $command->index = str_replace($schemaTable[0] . "_", "", $command->index);
        }

        return "alter table {$table} add constraint {$command->index} primary key ({$columns})";
    }

    /**
     * Compile a foreign key command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $command
     *
     * @return string
     */
    public function compileForeign(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);
        $on = $this->wrapTable($command->on);

        // We need to prepare several of the elements of the foreign key definition
        // before we can create the SQL, such as wrapping the tables and convert
        // an array of columns to comma-delimited strings for the SQL queries.
        $columns = $this->columnize($command->columns);
        $onColumns = $this->columnize((array) $command->references);

        // Aucune utilité d'avoir le nom du schéma dans le nom de la contrainte Foreign
        $schemaTable = explode(".", $table);

        if (count($schemaTable) > 1) {
            $command->index = str_replace($schemaTable[0] . "_", "", $command->index);
        }

        $sql = "alter table {$table} add constraint {$command->index} ";
        $sql .= "foreign key ({$columns}) references {$on} ({$onColumns})";

        // Once we have the basic foreign key creation statement constructed we can
        // build out the syntax for what should happen on an update or delete of
        // the affected columns, which will get something like "cascade", etc.
        if (!is_null($command->onDelete)) {
            $sql .= " on delete {$command->onDelete}";
        }

        if (!is_null($command->onUpdate)) {
            $sql .= " on update {$command->onUpdate}";
        }

        return $sql;
    }

    /**
     * Compile a unique key command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $command
     *
     * @return string
     */
    public function compileUnique(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);
        $columns = $this->columnize($command->columns);

        // Aucune utilité d'avoir le nom du schéma dans le nom de la contrainte Unique
        $schemaTable = explode(".", $table);

        if (count($schemaTable) > 1) {
            $command->index = str_replace($schemaTable[0] . "_", "", $command->index);
        }

        return "alter table {$table} add constraint {$command->index} unique({$columns})";
    }

    /**
     * Compile a plain index key command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $command
     *
     * @return string
     */
    public function compileIndex(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);
        $columns = $this->columnize($command->columns);

        // Aucune utilité d'avoir le nom du schéma dans le nom de la contrainte Index
        $schemaTable = explode(".", $table);

        if (count($schemaTable) > 1) {
            $command->index = str_replace($schemaTable[0] . "_", "", $command->index);
        }

        $sql = "create index {$command->index}";

        if ($command->indexSystem) {
            $sql .= " for system name {$command->indexSystem}";
        }

        $sql .= " on {$table}($columns)";

        //return "create index {$command->index} for system name on {$table}($columns)";
        return $sql;
    }

    /**
     * Compile a drop table command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $command
     *
     * @return string
     */
    public function compileDrop(Blueprint $blueprint, Fluent $command)
    {
        return 'drop table ' . $this->wrapTable($blueprint);
    }

    /**
     * Compile a drop table (if exists) command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $command
     *
     * @return string
     */
    public function compileDropIfExists(Blueprint $blueprint, Fluent $command)
    {
        return 'drop table if exists ' . $this->wrapTable($blueprint);
    }

    /**
     * Compile a drop column command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $command
     *
     * @return string
     */
    public function compileDropColumn(Blueprint $blueprint, Fluent $command)
    {
        $columns = $this->prefixArray('drop', $this->wrapArray($command->columns));
        $table = $this->wrapTable($blueprint);

        return 'alter table ' . $table . ' ' . implode(', ', $columns);
    }

    /**
     * Compile a drop primary key command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $command
     *
     * @return string
     */
    public function compileDropPrimary(Blueprint $blueprint, Fluent $command)
    {
        return 'alter table ' . $this->wrapTable($blueprint) . ' drop primary key';
    }

    /**
     * Compile a drop unique key command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $command
     *
     * @return string
     */
    public function compileDropUnique(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);

        // Aucune utilité d'avoir le nom du schéma dans le nom de la contrainte Unique
        $schemaTable = explode(".", $table);

        if (count($schemaTable) > 1) {
            $command->index = str_replace($schemaTable[0] . "_", "", $command->index);
        }

        return "alter table {$table} drop index {$command->index}";
    }

    /**
     * Compile a drop index command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $command
     *
     * @return string
     */
    public function compileDropIndex(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);

        // Aucune utilité d'avoir le nom du schéma dans le nom de la contrainte Index
        $schemaTable = explode(".", $table);

        if (count($schemaTable) > 1) {
            $command->index = str_replace($schemaTable[0] . "_", "", $command->index);
        }

        return "alter table {$table} drop index {$command->index}";
    }

    /**
     * Compile a drop foreign key command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $command
     *
     * @return string
     */
    public function compileDropForeign(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);

        // Aucune utilité d'avoir le nom du schéma dans le nom de la contrainte Foreign
        $schemaTable = explode(".", $table);

        if (count($schemaTable) > 1) {
            $command->index = str_replace($schemaTable[0] . "_", "", $command->index);
        }

        return "alter table {$table} drop foreign key {$command->index}";
    }

    /**
     * Compile a rename table command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $command
     *
     * @return string
     */
    public function compileRename(Blueprint $blueprint, Fluent $command)
    {
        $from = $this->wrapTable($blueprint);

        return "rename table {$from} to " . $this->wrapTable($command->to);
    }

    /**
     * Create the column definition for a char type.
     *
     * @param  \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeChar(Fluent $column)
    {
        return "char({$column->length})";
    }

    /**
     * Create the column definition for a string type.
     *
     * @param  \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeString(Fluent $column)
    {
        return "varchar({$column->length})";
    }

    /**
     * Create the column definition for a text type.
     *
     * @param  \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeText(Fluent $column)
    {
        $colLength = ($column->length ? $column->length : 16369);

        return "varchar($colLength)";
    }

    /**
     * Create the column definition for a medium text type.
     *
     * @param  \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeMediumText(Fluent $column)
    {
        $colLength = ($column->length ? $column->length : 16000);

        return "varchar($colLength)";
    }

    /**
     * Create the column definition for a long text type.
     *
     * @param  \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeLongText(Fluent $column)
    {
        $colLength = ($column->length ? $column->length : 16000);

        return "varchar($colLength)";
    }

    /**
     * Create the column definition for a big integer type.
     *
     * @param  \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeBigInteger(Fluent $column)
    {
        return 'bigint';
    }

    /**
     * Create the column definition for a integer type.
     *
     * @param  \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeInteger(Fluent $column)
    {
        return 'int';
    }

    /**
     * Create the column definition for a small integer type.
     *
     * @param  \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeSmallInteger(Fluent $column)
    {
        return 'smallint';
    }

    /**
     * Create the column definition for a numeric type.
     *
     * @param  \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeNumeric(Fluent $column)
    {
        return "numeric({$column->total}, {$column->places})";
    }

    /**
     * Create the column definition for a float type.
     *
     * @param  \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeFloat(Fluent $column)
    {
        return "decimal({$column->total}, {$column->places})";
    }

    /**
     * Create the column definition for a double type.
     *
     * @param  \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeDouble(Fluent $column)
    {
        if ($column->total && $column->places) {
            return "double({$column->total}, {$column->places})";
        }

        return 'double';
    }

    /**
     * Create the column definition for a decimal type.
     *
     * @param  \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeDecimal(Fluent $column)
    {
        return "decimal({$column->total}, {$column->places})";
    }

    /**
     * Create the column definition for a boolean type.
     *
     * @param  \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeBoolean(Fluent $column)
    {
        $definition = 'smallint constraint %s_%s_%s check(%s in(0, 1)) %s';

        return sprintf($definition, $column->type, $column->prefix, $column->name, $column->name, is_null($column->default) ? ' default 0' : '');
    }

    /**
     * Create the column definition for an enum type.
     *
     * @param  \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeEnum(Fluent $column)
    {
        return "enum('" . implode("', '", $column->allowed) . "')";
    }

    /**
     * Create the column definition for a date type.
     *
     * @param  \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeDate(Fluent $column)
    {
        if (!$column->nullable) {
            return 'date default current_date';
        }

        return 'date';
    }

    /**
     * Create the column definition for a date-time type.
     *
     * @param  \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeDateTime(Fluent $column)
    {
        return $this->typeTimestamp($column);
    }

    /**
     * Create the column definition for a time type.
     *
     * @param  \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeTime(Fluent $column)
    {
        if (!$column->nullable) {
            return 'time default current_time';
        }

        return 'time';
    }

    /**
     * Create the column definition for a timestamp type.
     *
     * @param  \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeTimestamp(Fluent $column)
    {
        if (!$column->nullable) {
            return 'timestamp default current_timestamp';
        }

        return 'timestamp';
    }

    /**
     * Create the column definition for a binary type.
     *
     * @param  \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeBinary(Fluent $column)
    {
        return 'blob';
    }

    /**
     * Get the SQL for a nullable column modifier.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $column
     *
     * @return string|null
     */
    protected function modifyNullable(Blueprint $blueprint, Fluent $column)
    {
        return $column->nullable ? '' : ' not null';
    }

    /**
     * Get the SQL for a default column modifier.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $column
     *
     * @return string|null
     */
    protected function modifyDefault(Blueprint $blueprint, Fluent $column)
    {
        if (!is_null($column->default)) {
            return " default " . $this->getDefaultValue($column->default);
        }

        return null;
    }

    /**
     * Get the SQL for an auto-increment column modifier.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $column
     *
     * @return string|null
     */
    protected function modifyIncrement(Blueprint $blueprint, Fluent $column)
    {
        if (in_array($column->type, $this->serials) && $column->autoIncrement) {
            return ' generated by default as identity constraint ' . $blueprint->getTable() . '_' . $column->name . '_primary primary key';
        }

        return null;
    }

    /**
     * Get the SQL for an "before" column modifier.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $column
     *
     * @return string|null
     */
    protected function modifyBefore(Blueprint $blueprint, Fluent $column)
    {
        if (!is_null($column->before)) {
            return ' before ' . $this->wrap($column->before);
        }

        return null;
    }

    /**
     * Get the SQL for an "for column" column modifier.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $column
     *
     * @return string|null
     */
    protected function modifyForColumn(Blueprint $blueprint, Fluent $column)
    {
        if (!is_null($column->forColumn)) {
            return ' for column ' . $this->wrap($column->forColumn);
        }

        return null;
    }

    /**
     * Get the SQL for a "generated" column modifier.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $column
     *
     * @return string|null
     */
    protected function modifyGenerated(Blueprint $blueprint, Fluent $column)
    {
        if (!is_null($column->generated)) {
            return ' generated ' . ($column->generated === true ? 'always' : $this->wrap($column->generated));
        }

        return null;
    }

    /**
     * Get the SQL for a "startWith" column modifier.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $column
     *
     * @return string|null
     */
    protected function modifyStartWith(Blueprint $blueprint, Fluent $column)
    {
        if (!is_null($column->startWith)) {
            return ' (start with ' . $column->startWith . ')';
        }

        return null;
    }

    /**
     * Get the SQL for an "implicitly hidden" column modifier.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $column
     *
     * @return string|null
     */
    protected function modifyImplicitlyHidden(Blueprint $blueprint, Fluent $column)
    {
        if (!is_null($column->implicitlyHidden)) {
            return ' implicitly hidden';
        }

        return null;
    }

    /**
     * Format a value so that it can be used in "default" clauses.
     *
     * @param  mixed $value
     *
     * @return string
     */
    protected function getDefaultValue($value)
    {
        if ($value instanceof Expression || is_bool($value) || is_numeric($value)) {
            return $value;
        }

        return "'" . strval($value) . "'";
    }

    /**
     * Compile a executeCommand command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $command
     *
     * @return string
     */
    private function compileExecuteCommand(Blueprint $blueprint, Fluent $command)
    {
        return "CALL QSYS2.QCMDEXC('" . $command->command . "')";
    }

    /**
     * Compile an addReplyListEntry command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $command
     * @param  \Illuminate\Database\Connection  $connection
     *
     * @return string
     */
    public function compileAddReplyListEntry(Blueprint $blueprint, Fluent $command, Connection $connection)
    {
        $sequenceNumberQuery = <<<EOT
            with reply_list_info(sequence_number) as (
                values(1)
                union all
                select sequence_number + 1
                from reply_list_info
                where sequence_number + 1 between 2 and 9999
            )
            select min(sequence_number) sequence_number
            from reply_list_info
            where not exists (
                select 1
                from qsys2.reply_list_info rli
                where rli.sequence_number = reply_list_info.sequence_number
            )
EOT;

        $blueprint->setReplyListSequenceNumber($sequenceNumber = $connection->selectOne($sequenceNumberQuery)->sequence_number);
        $command->command = "ADDRPYLE SEQNBR($sequenceNumber) MSGID(CPA32B2) RPY(''I'')";

        return $this->compileExecuteCommand($blueprint, $command);
    }

    /**
     * Compile a removeReplyListEntry command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $command
     *
     * @return string
     */
    public function compileRemoveReplyListEntry(Blueprint $blueprint, Fluent $command)
    {
        $sequenceNumber = $blueprint->getReplyListSequenceNumber();
        $command->command = "RMVRPYLE SEQNBR($sequenceNumber)";

        return $this->compileExecuteCommand($blueprint, $command);
    }

    /**
     * Compile a changeJob command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $blueprint
     * @param  \Illuminate\Support\Fluent            $command
     *
     * @return string
     */
    public function compileChangeJob(Blueprint $blueprint, Fluent $command)
    {
        $command->command = 'CHGJOB INQMSGRPY(*SYSRPYL)';

        return $this->compileExecuteCommand($blueprint, $command);
    }

}
