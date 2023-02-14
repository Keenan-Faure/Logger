<?php    
    use Keenan\Logger\includes\Utils;
    use Keenan\Logger\FileLog;
    if(!isset($_SESSION) || !isset($_SESSION['config']))
    {
        //starts session
        session_start();

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
                FileLog::fileLog("warning", $error->getMessage());
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
                FileLog::fileLog("warning", $error->getMessage());
            }
        }
    }
?>