<?php

namespace Keenan\Logger;

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
            $username = $_SESSION['config']['database_credentials']['username'];
            $password = $_SESSION['config']['database_credentials']['password'];
            $dbName = $_SESSION['config']['database_credentials']['database'];
            if(!is_null($dbName))
            {
                try
                {
                    $rawConn = new \mysqli('localhost', $username, $password, $dbName);
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
                    Logs::LogConsole('error', $error->getMessage());
                }
            }
            else if($dbName == "")
            {
                throw new \Exception("Cannot use an empty string as a database name");
            }
            else
            {
                throw new \Exception("Invalid Database Name");
            }
        }
        catch(\Exception $error)
        {
            Logs::LogConsole('warning', $error->getMessage());
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
                Logs::LogConsole('warning', "query cannot be null");
                return null;
            }
        }
        catch(\Exception $error)
        {
            Logs::LogConsole('error', $error->getMessage());
            return null;
        }
    }

    public static function queryDb(string $query, \mysqli $rawConn): ?array
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
            Logs::LogConsole("warning", $error->getMessage());
            exit();
        }
    }

}

?>