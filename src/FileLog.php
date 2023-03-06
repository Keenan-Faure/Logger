<?php

declare(strict_types=1);

namespace Keenan\Logger;

use Keenan\Logger\includes\Logs;

class FileLog extends Logs
{
    public static function fileLog(string $level, string $message): void
    {
        $logContent = 
        [
            "name" => "Log",
            "useJSONFormatter" => false,
            "fileHandler" => true,
            "useStreamHandle" => true,
            "level" => $level
        ];
        $fileLog = new Logs($logContent);
        $fileLog->LogOut($message);
    }
}

?>