<?php

declare(strict_types=1);

namespace Keenan\Tests\Logger\includes;

use PHPUnit\Framework\TestCase;
use Keenan\Logger\includes\DbLog;

class DbLogTest extends TestCase
{
    private function setUpArray(): array
    {
        $array = [
            "message" => "I am demo",
            "level" => "50",
            "level_name" => "info",
            "channel" => "Logger",
            "datetime" => "dateTimeString",
            "appName" => "appName"
        ];
        return $array;
    }

    private function setUpJson(): string
    {
        $json = '
        {
            "message": "I am demo",
            "level": 55,
            "level_name": "INFO",
            "channel": "Logger",
            "datetime": "dateTimeString",
            "appName": "appName"
        }';
        return $json;
    }

    public function testConstructor(): void
    {
        $dbLog = new DbLog($this->setUpArray());

        $this->assertSame("I am demo", $dbLog->message);
        $this->assertSame(50, $dbLog->level);
        $this->assertSame("INFO", $dbLog->level_name);
        $this->assertSame("Logger", $dbLog->channel);
        $this->assertSame("dateTimeString", $dbLog->datetime);

        $object_attributes = [
            "message",
            "level",
            "level_name",
            "channel",
            "datetime",
            "appName"
        ];

        for($i = 0; $i < sizeof($object_attributes); ++$i)
        {
            $this->assertObjectHasAttribute($object_attributes[$i], $dbLog);
        }

        $this->assertInstanceOf("Keenan\Logger\includes\DbLog", $dbLog);
    }

    public function testJsonConversion(): void
    {
        $json = $this->setUpJson();
        $dbLog = DbLog::createFromJson($json);

        $this->assertJsonStringEqualsJsonString($json, json_encode($dbLog));
    }

    public function testArrayConversion(): void
    {
        $array = [
            [
                "message" => null,
                "level" => 100,
                "level_name" => "ERROR",
                "channel" => "Logger",
                "datetime" => "dateTimeString",
                "appName" => "appName"
            ],
            [
                "message" => "I am demo",
                "level" => 50,
                "level_name" => "INFO",
                "channel" => "Logger",
                "datetime" => "dateTimeString",
                "appName" => "appName"
            ]
        ];

        $json = json_encode(DbLog::createFromArray($array));

        $this->assertJsonStringEqualsJsonString(json_encode($array), $json);
    }
}

?>