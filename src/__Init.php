<?php   
    
    use Keenan\Logger\includes\Utils;
    use Keenan\Logger\FileLog;
    if(!isset($_SESSION) || !isset($_SESSION['config']))
    {
        session_start();
        try
        {
            $_config = include(Utils::createFolderMap() . '/vendor/keenan/logger/config/config.php');
        }
        catch(\Exception $error)
        {
            FileLog::fileLog('warning', $error->getMessage());
        }
    }
?>