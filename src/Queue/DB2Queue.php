<?php

namespace Cooperl\DB2\Queue;

use Illuminate\Queue\DatabaseQueue;

class DB2Queue extends DatabaseQueue
{

    /**
     * {@inheritdoc}
     */
    public function getLockForPopping()
    {
        return true;
    }

}
