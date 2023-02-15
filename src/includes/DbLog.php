<?php

namespace Keenan\Logger\includes;
use Keenan\Logger\ConsoleLog;
use Keenan\Logger\DatabaseLog;
use Keenan\Logger\FileLog;

class DbLog
{
    public ?string $message;
    public ?int $level;
    public ?string $level_name;
    public ?string $channel;
    public ?string $datetime;
    public ?string $appName;

    public function __construct(array $data)
    {
        $this->message      = Utils::chkArrayString($data, 'message');
        $this->level        = Utils::chkArrayInt($data, 'level');
        $this->level_name   = strtoupper(Utils::chkArrayString($data, 'level_name'));
        $this->channel      = Utils::chkArrayString($data, 'channel');
        $this->datetime     = Utils::chkArrayString($data, 'datetime');
        $this->appName      = Utils::chkArrayString($data, 'appName');
    }

    public static function createFromJson(string $json): DbLog
    {
        $array = json_decode($json, true);

        return new DbLog($array);
    }

    public static function createFromArray(array $data): array
    {
        $array = [];
        foreach($data as $value)
        {
            array_push($array, new DbLog($value));
        }
        return $array;
    }

    public static function dbLog(DbLog $dbLog): void
    {
        try
        {
            try
            {
                $rawConn = DbLog::init__conn();
                //may need to reconsider...
                if($rawConn == null)
                {
                    exit();
                }
                $exists = DbLog::tableExist($rawConn);
                if($exists)
                {
                    $query = 'INSERT INTO Logger
                    (
                        message,
                        appName,
                        level,
                        level_name,
                        channel,
                        datetime
                    ) 
                    values 
                    (
                        "' . $dbLog->message . '", 
                        "' . $dbLog->appName . '",
                        "' . $dbLog->level . '",
                        "' . $dbLog->level_name . '",
                        "' . $dbLog->channel . '",
                        "' . $dbLog->datetime . '"
                    )';

                    DbLog::queryDb($query, $rawConn);
                }
                else
                {
                    $query = 'CREATE Table Logger
                    (
                        message varchar(255),
                        appName varchar(255),
                        level varchar(255),
                        level_name varchar(255),
                        channel varchar(255),
                        datetime varchar(255)
                    )';

                    DbLog::queryDb($query, $rawConn);

                    $query = 'INSERT INTO Logger
                    (
                        message,
                        appName,
                        level,
                        level_name,
                        channel,
                        datetime
                    ) 
                    values 
                    (
                        "' . $dbLog->message . '", 
                        "' . $dbLog->appName . '",
                        "' . $dbLog->level . '",
                        "' . $dbLog->level_name . '",
                        "' . $dbLog->channel . '",
                        "' . $dbLog->datetime . '"
                    )';
                    DbLog::queryDb($query, $rawConn);
                }
            }
            catch(\Exception $error)
            {
                ConsoleLog::consoleLog('warning', $error->getMessage());
                FileLog::fileLog('warning', $error->getMessage());
            }
        }
        catch(\Exception $error)
        {
            ConsoleLog::consoleLog('warning', $error->getMessage());
            FileLog::fileLog('warning', $error->getMessage());
        }
    }

    public static function tableExist(\mysqli $rawConn): bool
    {
        $query = "SHOW TABLES";
        $tables = DbLog::queryDbTable($query, $rawConn);
        if(in_array("Logger", $tables))
        {
            return true;
        }
        else
        {
            return false;
        }
        return false;
    }

    public static function queryDbTable(string $query, \mysqli $rawConn): ?array
    {
        try
        {
            if($query != "")
            {
                $output = array();
                if($result = mysqli_query($rawConn, $query))
                {
                    while($row = $result->fetch_object())
                    {
                        $row = json_decode(json_encode($row), true);
                        $row = array_shift($row);
                        array_push($output, $row);    
                    }
                }
                return $output;
            }
            else
            {
                ConsoleLog::consoleLog('warning', "query cannot be null");
                FileLog::fileLog('warning', 'query cannot be null');
                return null;
            }
        }
        catch(\Exception $error)
        {
            ConsoleLog::consoleLog('warning', $error->getMessage());
            FileLog::fileLog('warning', $error->getMessage());
            return null;
        }
    }

    /**
     * @return DbLog[]
     */
    public static function queryDb(string $query, \mysqli $rawConn): array
    {
        try
        {
            $output = array();
            if($result = mysqli_query($rawConn, $query))
            {
                if(is_bool($result))
                {
                    if($result)
                    {
                        array_push($output, $result);
                        return $output;
                    }
                }
                else
                {
                    while($row = $result->fetch_object())
                    {
                        $dbLog = DbLog::createFromJson(json_encode($row));
                        array_push($output, $dbLog);
                    } 
                    return $output;
                }
            }
            else
            {
                array_push($output, $result);
                return $output;
            }
        }
        catch(\Exception $error)
        {
            ConsoleLog::consoleLog("warning", $error->getMessage());
            FileLog::fileLog('warning', $error->getMessage());
        }
    }

    public static function queryDbOther(string $query, \mysqli $rawConn): ?array
    {
        try
        {
            $output = array();
            $resultArray = array();
            if($result = mysqli_query($rawConn, $query))
            {
                $array = array();
                if(is_bool($result))
                {
                    if($result)
                    {
                        array_push($output, $result);
                        return $output;
                    }
                }
                else
                {
                    while($row = $result->fetch_object())
                    {
                        $array = $row;
                        array_push($resultArray, $array);
                    }
                    for($i = 0; $i < sizeof($resultArray); ++$i)
                    {
                        array_push($output, $resultArray[$i]);
                    }    
                    return $output;
                }
            }
            else
            {
                array_push($output, $result);
                return $output;
            }
        }
        catch(\Exception $error)
        {
            ConsoleLog::consoleLog("warning", $error->getMessage());
            FileLog::fileLog('warning', $error->getMessage());
            exit();
        }
    }

    public static function init__conn(): ?\mysqli
    {
        if(isset($_SESSION))
        {
            $username = $_SESSION['config']['database_credentials']['username'];
            $password = $_SESSION['config']['database_credentials']['password'];
            $dbName = $_SESSION['config']['database_credentials']['database'];
            try
            {
                return new \mysqli('localhost', $username, $password, $dbName);
            }
            catch(\Exception $error)
            {
                DatabaseLog::databaseLog('error', $error->getMessage());
            }
        }
        else
        {
            FileLog::fileLog('warning', 'No session found');
            return null;
        }
    }
}

?>