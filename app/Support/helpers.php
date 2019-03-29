<?php

if (! function_exists('lockTables')) {
    /**
     * Execute a Closure within locking tables and "transaction".
     *
     * @param  array $tables [['table1', 'w'], ['table2', 'r'], ...]
     * @param  Closure $callback
     */
    function lockTables(array $tables, Closure $callback)
    {
        $types = [
            'r' => 'READ',
            'w' => 'WRITE',
        ];

        $tables = array_map(function ($v) use ($types) {
            return $v[0] . ' ' . $types[$v[1]];
        }, $tables);

        DB::unprepared('LOCK TABLES ' . implode(', ', $tables));
        DB::statement('SET AUTOCOMMIT = 0');

        $exception = false;

        try {
            $callback();

            DB::statement('COMMIT');
        } catch (Exception $e) {
            DB::statement('ROLLBACK');
            $exception = $e;
        }

        DB::statement('SET AUTOCOMMIT = 1');
        DB::unprepared('UNLOCK TABLES');

        if ($exception) {
            throw $exception;
        }
    }
}
