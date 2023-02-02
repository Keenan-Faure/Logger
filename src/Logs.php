<?php
    declare(strict_types=1);

    namespace Keenan\Logger;

    if(isset($_SESSION))
    {
        Utils::init_config();
    }

    use Monolog\Level;
    use Monolog\Logger;
    use Monolog\Handler\RotatingFileHandler;
    use Monolog\Formatter\JsonFormatter;
    use Monolog\Formatter\LineFormatter;
    use Monolog\Handler\StreamHandler;

    use Keenan\Logger\Utils;

    class Logs extends Logger
    {
        const OUTPUT_FORMAT = "%level_name% | %datetime% > %message% | %context% %extra%\n";
        const DATE_FORMAT = "Y-n-j, g:i a";

        const LEVELS = 
        [
            "ERROR",
            "INFO",
            "WARNING",
            "ALERT"
        ];

        /** @param JsonFormatter and @param LineFormatter */
        public ?bool $useJSONFormatter;
        /** @param RotatingFileHandler and @param StreamFileHandler */
        public ?bool $fileHandler;
        /** @param StreamHandler */
        public ?bool $useStreamHandle;
        public string $level;

        public function __construct(array $data)
        {
            if(!Utils::chkLogLevel($data))
            {
                Logs::LogConsole('error', "undefined level");
                exit();
            }

            parent::__construct(Utils::chkArrayString($data, 'name'));

            $this->useJSONFormatter     = Utils::chkArrayBool($data, 'useJSONFormatter');
            $this->fileHandler   = Utils::chkArrayBool($data, 'fileHandler');
            $this->useStreamHandle = Utils::chkArrayBool($data, 'useStreamHandle');
            $this->level          = strtoupper(Utils::chkArrayString($data, 'level'));

            $this->init_logger();
        }

        public static function createFromJSON(string $json): Logs
        {
            if(isset($json))
            {
                $logs = new Logs(json_decode($json, true));
                return $logs;
            }
        }

        public static function LogConsole(string $level, string $message): void
        {
            try
            {
                if(in_array(strtoupper($level), Logs::LEVELS))
                {
                    $context = Utils::getContext();
                    $content = [
                        "name" => "Logger",
                        "useJSONFormatter" => false,
                        "fileHandler" => false,
                        "useStreamHandle" => true,
                        "level" => $level
                    ];
                    $consoleLogger = new Logs($content);
                    switch ($consoleLogger->level)
                    {
                        case 'ERROR': { $consoleLogger->error($message, $context); break; }
                        case 'INFO': { $consoleLogger->info($message, $context); break; }
                        case 'WARNING': { $consoleLogger->warning($message, $context); break; }
                        case 'ALERT': { $consoleLogger->alert($message, $context); break; }
                    }
                }
                else
                {
                    throw new \Exception("Incorrect Log level: " . $level);
                }
            }
            catch(\Exception $error)
            {
                Logs::LogConsole("error", $error->getMessage());
            }
        }

        //missing a message(?)
        public static function LogDb(string $level, string $message): void
        {
            try
            {
                $context = Utils::getContext();
                $content = [
                    "name" => "Logger",
                    "useJSONFormatter" => true,
                    "fileHandler" => true,
                    "useStreamHandle" => false,
                    "level" => $level
                ];
                $dbLogger = new Logs($content);
                switch ($dbLogger->level)
                {
                    case 'ERROR': { $dbLogger->error($message, $context); break; }
                    case 'INFO': { $dbLogger->info($message, $context); break; }
                    case 'WARNING': { $dbLogger->warning($message, $context); break; }
                    case 'ALERT': { $dbLogger->alert($message, $context); break; }
                }
                $dbLog = Utils::readFile($dbLogger);
                DbLog::dbLog($dbLog);
                Utils::removeFile($dbLogger);
            }
            catch(\Exception $error)
            {
                Logs::LogConsole("error", $error->getMessage());
            }
        }

        public function init_logger(): void
        {
            $stream_handler = null;
            if($this->fileHandler !== null)
            {
                if($this->fileHandler == false)
                {
                    $stream_handler = new StreamHandler("php://stdout");
                }
                else if($this->fileHandler == true)
                {
                    switch ($this->level)
                    {
                        case 'ERROR': { $stream_handler = new RotatingFileHandler(__DIR__ . "/logs/errorLog.log", 1, Level::Error); break; }
                        case 'INFO': { $stream_handler = new RotatingFileHandler(__DIR__ . "/logs/infoLog.log", 1, Level::Info); break; }
                        case 'WARNING': { $stream_handler = new RotatingFileHandler(__DIR__ . "/logs/warningLog.log", 1, Level::Warning); break; }
                        case 'ALERT': { $stream_handler = new RotatingFileHandler(__DIR__ . "/logs/alertLog.log", 1, Level::Alert); break; }
                        default:
                    }
                }
            }
            if($this->useJSONFormatter != null)
            {
                if($this->useJSONFormatter == true)
                {
                    $stream_handler->setFormatter(new JsonFormatter());
                }
                else if($this->useJSONFormatter == false)
                {
                    $stream_handler->setFormatter(new LineFormatter
                    (
                        Logs::OUTPUT_FORMAT, Logs::DATE_FORMAT,true, true, true
                    ));                }
            }
            if($this->useStreamHandle == true)
            {
                $stream_handler->setFormatter(new LineFormatter
                (
                    Logs::OUTPUT_FORMAT, Logs::DATE_FORMAT,true, true, true
                ));
            }
            $this->pushHandler($stream_handler);
        }
    }
?>