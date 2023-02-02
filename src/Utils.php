<?php
    namespace Keenan\Logger;

    use Keenan\Logger\DbLog;
    use Keenan\Logger\Logs;

    class Utils
    {
        public static function chkArrayString($array, $key): ?string
        {
            if(isset($key))
            {
                if(array_key_exists($key, $array))
                {
                    if(is_string($array[$key]))
                    {
                        return (string) $array[$key];
                    }
                    return null;
                }
                return null;
            }
            return null;
        }
        
        public static function chkArrayInt($array, $key): ?int
        {
            if(isset($key))
            {
                if(array_key_exists($key, $array))
                {
                    if(is_int($array[$key]))
                    {
                        return $array[$key];
                    }
                    if(is_numeric($array[$key]))
                    {
                        return (int) $array[$key];
                    }
                    return null;
                }
                return null;
            }
            return null;
        }

        public static function chkArrayBool($array, $key): ?bool
        {
            if(isset($key))
            {
                if(array_key_exists($key, $array))
                {
                    if(is_bool($array[$key]))
                    {
                        return $array[$key];
                    }
                    if(is_string($array[$key]))
                    {
                        $value = strtolower($array[$key]);
                        if($value == 'false')
                        {
                            return false;
                        }
                        if($value == 'true')
                        {
                            return true;
                        }
                        if($value == "")
                        {
                            return false;
                        }
                    }
                    return null;
                }
                return null;
            }
            return null;
        }

        public static function chkLogLevel(array $data): bool
        {
            if(!in_array(strtoupper(Utils::chkArrayString($data, 'level')), Logs::LEVELS))
            {
                return false;
            }
            return true;
        }

        public static function removeFile(Logs $log): void
        {
            $dir = '/src/logs';
            $filter = scandir(getcwd() . $dir);
            $location = getcwd() . $dir;
            $fileName = Utils::getLogFile($log);
            if(in_array($fileName, $filter))
            {
                $file = $location . '/' . $fileName;
                unlink($file);
            }
            else
            {
                Logs::LogConsole('info', "File '" . $fileName . "' does not exist at dir: " . $location);
            }
        }

        public static function getLogFile(Logs $log): string
        {
            $date = date('Y-m-d');
            $fileName = strtolower($log->level) . 'Log-' . $date . '.log';

            return $fileName;
        }

        public static function readFile($log): DbLog
        {
            try
            {
                $dir = '/src/logs';
                $location = getcwd() . $dir;
                $fileName = Utils::getLogFile($log);
                $myFile = fopen($location . '/' . $fileName, "r") or throw new \Exception("File does not exist");
                while(!feof($myFile)) 
                {
                    $data = json_decode(fgets($myFile), true);
                    $contextData = Utils::addContext($data);
                    $dbLog = new DbLog($contextData);
                    return $dbLog;
                }
                fclose($myFile);
            }
            catch(\Exception $error)
            {
                Logs::LogConsole('warning', $error->getMessage());
            }
        }

        public static function getContext(): array
        {
            if(isset($_SESSION['config']))
            {
                return $_SESSION['config']['context'];
            }
            else
            {
                 return [];
            }
        }

        public static function init_config(): void
        {
            $_config = include(getcwd() . '/config/config.php');
            try
            {
                $_SESSION['config'] = $_config;
            }
            catch(\Exception $error)
            {
                Logs::LogConsole("error", $error->getMessage());
            }
        }
        public static function addContext($array): array
        {
            foreach($array as $key => $value)
            {
                if(is_array($array[$key]))
                {
                    foreach($value as $subKey => $subValue)
                    {
                        if(is_array($value[$subKey]))
                        {
                            $array[$key] = null;
                        }
                        else
                        {
                            $array += [$subKey => $subValue];
                            unset($array[$key]);
                        }
                    }
                }
            }
            return $array;
        }
    }
?>