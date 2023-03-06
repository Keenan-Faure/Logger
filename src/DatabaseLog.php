<?php

declare(strict_types=1);

namespace Keenan\Logger;

use Keenan\Logger\includes\Logs;

class DatabaseLog extends Logs
{
    public static function databaseLog(string $level, string $message): void
    {
        $logContent = 
        [
            "name" => "Log",
            "useJSONFormatter" => true,
            "fileHandler" => true,
            "useStreamHandle" => false,
            "level" => $level
        ];
        $databaseLog = new Logs($logContent);
        $databaseLog->LogDb($message);
    }
}

?>