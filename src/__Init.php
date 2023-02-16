<?php   
    
    use Keenan\Logger\FileLog;
    use Keenan\Logger\includes\Utils;

    include_once(Utils::getInit());
    if(!isset($_SESSION) || !isset($_SESSION['config']))
    {
        try
        {
            $url = Utils::createFolderMap();
            $filePath = $url . '/vendor/keenan/logger/config/config.php';
            if(file_exists($filePath))
            {
                $_config = include($filePath);
            }
            else
            {
                include($url . '/config/config.php');
            }
        }
        catch(\Exception $error)
        {
            FileLog::fileLog('warning', $error->getMessage());
        }
    }
?>