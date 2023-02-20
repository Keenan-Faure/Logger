<?php

declare(strict_types=1);

namespace Keenan\Tests\Logger\includes;

use Keenan\Logger\includes\Utils;
use Keenan\Logger\includes\DbLog;
use Keenan\Logger\includes\Logs;
use Keenan\Logger\ConsoleLog;
use Keenan\Logger\FileLog;

use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    private \mysqli $rawConn;

    private function setUpLog(): Logs
    {
        $log = new Logs([
            "name" => "Log",
            "useJSONFormatter" => true,
            "fileHandler" => true,
            "useStreamHandle" => false,
            "level" => "alert"
        ]);
        $log->alert("I am an alert Message");
        return $log;
    }

    protected function setUp(): void
    {
        try
        {
            Utils::init_config();
            $_config = $_SESSION['config']['database_credentials'];
            $this->rawConn = new \mysqli(
                'localhost', 
                Utils::chkArrayString($_config, 'username'),
                Utils::chkArrayString($_config, 'password'),
                Utils::chkArrayString($_config, 'database')
            );
        }
        catch(\Exception $error)
        {
            ConsoleLog::consoleLog("error", $error->getMessage());
            FileLog::fileLog('warning', $error->getMessage());
            exit();
        }
    }
    public function testChkArrayString(): void
    {
        $array = 
        [
            "name" => "Keenan"
        ];
        $this->assertSame("Keenan",Utils::chkArrayString($array, "name"));
    }

    public function testChkArrayInt(): void
    {
        $array = 
        [
            "id" => "90"
        ];
        $this->assertSame(90,Utils::chkArrayInt($array, "id"));
    }
    
    public function testChkArrayBool(): void
    {
        $array = 
        [
            "active" => "true"
        ];
        $this->assertSame(true,Utils::chkArrayBool($array, "active"));
    }

    public function testQueryDbTable(): void
    {
        $query = "SHOW TABLES";
        $tables = DbLog::queryDbTable($query, $this->rawConn);
        if(is_null($tables))
        {
            exit();
        }
        $actual = in_array("Logger", $tables);

        $this->assertIsBool($actual);
    }

    public function testTableExist(): void
    {
        $actual = DbLog::tableExist($this->rawConn);

        $this->assertIsBool($actual);
    }

    public function testQueryDb(): void
    {
        $exists = DbLog::tableExist($this->rawConn);
        if($exists)
        {
            $query = "SELECT * FROM Logger";
            $actual = DbLog::queryDb($query, $this->rawConn);
            $this->assertIsArray($actual);
        }
        //cannot perform any assertions because the table dne
        //if table was create - run tests again
    }
    public function testRemoveFile(): void
    {
        $log = $this->setUpLog();

        Utils::removeFile($log);

        $dir = '/logs';
        $filter = scandir(getcwd() . $dir);
        $fileName = Utils::getLogFile($log);

        if(in_array($fileName, $filter))
        {
            $actual = true;
        }
        else
        {
            $actual = false;
        }

        $expectedResult = false;
        $this->assertSame($expectedResult, $actual);
    }
    public function testReadFile(): void
    {
        $log = $this->setUpLog();

        $actual = Utils::readFile($log);

        $this->assertSame("I am an alert Message", $actual->message);
        $this->assertSame("Log", $actual->channel);
        $this->assertSame("ALERT", $actual->level_name);

    }
    public function testGetLogFile(): void
    {
        $log = $this->setUpLog();

        $expectedResult = "alertLog-" . date("Y-m-d") . ".log";
        $actual = Utils::getLogFile($log);

        $this->assertSame($expectedResult, $actual);
        Utils::removeFile($log);
    }

    public function testDbLog(): void
    {
        $exists = DbLog::tableExist($this->rawConn);
        if($exists)
        {
            $query = "INSERT INTO Logger
            (
                message,
                appName,
                level, 
                level_name, 
                channel, 
                datetime
            ) 
            VALUES
            (
                'message',
                'appName',
                '999',
                'level_name',
                'channel',
                'dateTimeString'
            )";
            DbLog::queryDb($query, $this->rawConn);

            $data = json_encode(
                new DbLog(
                    json_decode(
                        json_encode(DbLog::queryDb(
                            "SELECT * FROM Logger WHERE level = '999' LIMIT 1", 
                            $this->rawConn
                        )[0]), true
                    )
                )
            );
            $expectedResult = '{
                "message": "message",
                "appName": "appName",
                "level": 999,
                "level_name": "LEVEL_NAME",
                "channel": "channel",
                "datetime": "dateTimeString"
            }';

            $this->assertJsonStringEqualsJsonString($expectedResult, $data);

            $query = "DELETE FROM Logger WHERE level = '999'";
            DbLog::queryDb($query, $this->rawConn);
        }
        else
        {
            $query = 'CREATE Table Logger
            (
                ID int AUTO_INCREMENT primary key NOT NULL,
                appName varchar(255),
                message varchar(255), 
                level varchar(255),
                level_name varchar(255),
                channel varchar(255),
                datetime varchar(255)
            )';
            DbLog::queryDb($query, $this->rawConn);

            $query = "INSERT INTO Logger
            (
                appName,
                message, 
                level, 
                level_name, 
                channel, 
                datetime
            ) 
            VALUES
            (
                'appName',
                'message',
                '999',
                'level_name',
                'channel',
                'dateTimeString'
            )";
            DbLog::queryDb($query, $this->rawConn);

            $data = json_encode(
                new DbLog(
                    json_decode(
                        json_encode(DbLog::queryDb(
                            "SELECT * FROM Logger WHERE level = '999' LIMIT 1", 
                            $this->rawConn
                        )[0]), true
                    )
                )
            );       
            $expectedResult = '{
                "appName": "appName",
                "message": "message",
                "level": 999,
                "level_name": "LEVEL_NAME",
                "channel": "channel",
                "datetime": "dateTimeString"
            }';

            $this->assertJsonStringEqualsJsonString($expectedResult, $data);

            $query = "DELETE FROM Logger WHERE level = '999'";
            DbLog::queryDb($query, $this->rawConn);
        }
    }

    public function testAddContext(): void
    {
        $data = 
        [
            "value" => "subValue",
            "value_2" => 
            [
                "value_3" => "subValue_2"
            ],
            "value_4" => 
            [
                "value_5" =>
                [
                    "value_6" => "subValue_3"
                ]
            ],
            "value_7" => 
            [
                "value_8" => "subValue_4"
            ]
        ];

        $array = Utils::addContext($data);

        $expectedJson = '
        {
            "value": "subValue",
            "value_3": "subValue_2",
            "value_4": null,
            "value_8": "subValue_4"
        }';

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($array));
    }
}