<?php

declare(strict_types=1);

namespace Keenan\Tests\Logger;

session_start();

use Keenan\Logger\Logs;
use PHPUnit\Framework\TestCase;

class LogsTest extends TestCase
{
    private function setUpContent(): array
    {
        $content = [
            "name" => "Log",
            "useJSONFormatter" => true,
            "fileHandler" => true,
            "useStreamHandle" => false,
            "level" => "alert"
        ];
        return $content;
    }

    private function setUpJson(): string
    {
        $json = '
        {
            "name": "Log",
            "useJSONFormatter": true,
            "fileHandler": true,
            "useStreamHandle": false,
            "level": "ALERT"
        }';
        return $json;
    }

    public function testConstructor(): void
    {
        $log = new Logs($this->setUpContent());
        
        $this->assertSame(true, $log->useJSONFormatter);
        $this->assertSame(true, $log->fileHandler);
        $this->assertSame(false, $log->useStreamHandle);
        $this->assertSame('ALERT', $log->level);

        $this->assertInstanceOf("Keenan\Logger\Logs", $log);

        Logs::LogDb('error', "Unknown error occurred");
    }
    public function testCreateFromJson(): void
    {
        $json = $this->setUpJson();
        $log = Logs::createFromJSON($json);

        $this->assertInstanceOf("Keenan\Logger\Logs", $log);
        $expectedJson = '{
            "useJSONFormatter": true,
            "fileHandler": true,
            "useStreamHandle": false,
            "level": "ALERT"
        }';
        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($log));
    }
}

?>