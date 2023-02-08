<?php

declare(strict_types=1);

namespace Keenan\Logger;

use Keenan\Logger\includes\Logs;

class ConsoleLog extends Logs
{
    public static function consoleLog(string $level, string $message): void
    {
        $logContent = 
        [
            "name" => "Log",
            "useJSONFormatter" => false,
            "fileHandler" => false,
            "useStreamHandle" => true,
            "level" => $level
        ];
        $consoleLog = new Logs($logContent);
        $consoleLog->LogOut($message);
    }
}

?>