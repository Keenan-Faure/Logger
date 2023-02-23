<?php

    namespace Keenan\Logger\includes;

    use Keenan\Logger\ConsoleLog;
    use Keenan\Logger\FileLog;
    
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
            $dir = '/logs';
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
                ConsoleLog::consoleLog('warning', "File '" . $fileName . "' does not exist at dir: " . $location);
                FileLog::fileLog('warning', "File '" . $fileName . "' does not exist at dir: " . $location);
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
                $dir = '/logs';
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
                ConsoleLog::consoleLog('warning', $error->getMessage());
                FileLog::fileLog('warning', $error->getMessage());
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
            $cwd = getcwd();
            $lastFolder = Utils::getLastFolder($cwd);
            if($lastFolder != 'Logger')
            {
                $_config = include(getcwd() . '/vendor/keenan/logger/config/config.php');
                try
                {
                    $_SESSION['config'] = $_config;
                }
                catch(\Exception $error)
                {
                    ConsoleLog::consoleLog("warning", $error->getMessage());
                    FileLog::fileLog('warning', $error->getMessage());
                }
            }
            else
            {
                $_config = include(getcwd() . '/config/config.php');
                try
                {
                    $_SESSION['config'] = $_config;
                }
                catch(\Exception $error)
                {
                    ConsoleLog::consoleLog("warning", $error->getMessage());
                    FileLog::fileLog('warning', $error->getMessage());
                }
            }
        }
        
        public static function getLastFolder(string $path): string
        {
            $folderArray = explode('/', $path);
            if(sizeof($folderArray) == 1)
            {
                $folderArray = explode('\\', $path);
            }
            return $folderArray[sizeof($folderArray)-1];
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
        /**
         * returns the directory of the __Init.php file respective to 
         * the cwd
         */
        public static function getInit(): string
        {
            $url = Utils::createFolderMap();
            
            $filePath = $url . '/vendor/keenan/logger/src/__Init.php';
            if(file_exists($filePath))
            {
                return $filePath;
            }
            else
            {
                return $url . '/src/__Init.php';
            }
        }

        public static function createFolderMap(): string
        {
            $lastFolder = Utils::getLastFolder(getcwd());

            $folderArray = explode('/', getcwd());
            if(sizeof($folderArray) == 1)
            {
                $folderArray = explode('\\', getcwd());
            }
            $array_keylast = array_keys($folderArray, $lastFolder)[0];
            $preKey = 0;
            $foundSrc = false;
            for($i = 0; $i < sizeof($folderArray); ++$i)
            {
                if($folderArray[$array_keylast - $i] == 'src')
                {
                    $preKey = $array_keylast - ($i+1);
                    $foundSrc = true;
                    break;
                }
            }
            $url = '';
            //Mac computers first folder is blank {{Users}}
            if($folderArray[0] == "")
            {
                for($j = 0; $j < $preKey; ++$j)
                {
                    $url = $url . '/'. $folderArray[$j+1];
                }
            }
            //If the src was found but the OS is windows
            else if($foundSrc === true && $url === "")
            {
                for($j = -1; $j < $preKey; ++$j)
                {
                    $url = $url . '/'. $folderArray[$j+1];
                }
            }
            if($url === "" && $foundSrc === false)
            {
                for($j = 0; $j < sizeof($folderArray); ++$j)
                {
                    $url = $url . '/'. $folderArray[$j];
                }
                $url = substr($url, 1);
            }
            if(PHP_OS == "WINNT")
            {
                if(substr($url, 0, 1) == '/')
                {
                    $url = substr($url, 1);
                }
            }
        }
    }
?>
