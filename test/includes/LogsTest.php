<?php

declare(strict_types=1);

namespace Keenan\Tests\Logger\includes;
use Keenan\Logger\includes\Utils;

include(Utils::getInit());

use Keenan\Logger\includes\Logs;
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

        $this->assertInstanceOf("Keenan\Logger\includes\Logs", $log);
    }
    public function testCreateFromJson(): void
    {
        $json = $this->setUpJson();
        $log = Logs::createFromJSON($json);

        $this->assertInstanceOf("Keenan\Logger\includes\Logs", $log);
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