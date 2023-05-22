<?php

namespace Easi\DB2\Exceptions;

use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Throwable;

class TranslatedQueryException extends QueryException
{
    protected array $config;
    public function __construct($connectionName, $sql, array $bindings, Throwable $previous, array $config = [])
    {
        $this->config = $config;
        parent::__construct($connectionName, $sql, $bindings, $previous);
    }

    protected function formatMessage($connectionName, $sql, $bindings, Throwable $previous)
    {
        if(isset($this->config['from_encoding']) && $this->config['from_encoding'] && !is_null($previous->getMessage())) {
            $previousMessage = iconv($this->config['from_encoding'], 'utf-8', $previous->getMessage());
        } else {
            $previousMessage = $previous->getMessage();
        }
        return $previousMessage.' (Connection: '.$connectionName.', SQL: '.Str::replaceArray('?', $bindings, $sql).')';
    }
}
