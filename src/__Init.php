<?php   
    
    use Keenan\Logger\FileLog;
    use Keenan\Logger\includes\Utils;

    include_once(Utils::getInit());

    try
    {
        $url = Utils::createFolderMap();
        $filePath = $url . '/vendor/keenan/logger/config/config.php';
        if(file_exists($filePath))
        {
            $_config = include($filePath);
            $_SESSION['config'] = $_config;
        }
        else
        {
            $_config = include($url . '/config/config.php');
            $_SESSION['config'] = $_config;
        }
    }
    catch(\Exception $error)
    {
        FileLog::fileLog('warning', $error->getMessage());
    }
?>