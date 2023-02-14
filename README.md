# Logger
Logger library for Integration-web-app

Main features
-

* Uses the composer library `Monolog/Monolog` to write logs to a file stored in `logs`.
* Has the ability to log messages in the format 
```
%level_name% | %datetime% > %message% | %context% %extra%\n
```
onto the console or into a file or database table Logger

Installation
-
Via Composer:
* If you wish to install this library with composer then simply run the command `composer init` inside your current project or libary working directory and when prompted for required packages enter `keenan\logger` to install

Documentation
-

* The libary comes with a `config.php` which can be found in `./config/config.php` where the database details will need to be entered

* To enter contextual information, inside the config.php file there is a array_key called `context`. Currently only an app_name can be used as an additional field.
    - Future update will allow for more context fields.

How to use
-

* If you wish to run the application simply run the functions 
`
FileLog::fileLog
` 
`
ConsoleLog::consoleLog
`
`
DatabaseLog::dbLog
` 
to add log the logs to a file, console and database table respectively.

PHPUnit Tests
-
```
./vendor/bin/phpunit ./test/includes/
```

Note that all tests needs to include the function `Utils::getInit()` at the top of the script as seen below:
```
use Keenan\Logger\includes\Utils;
include(Utils::getInit());
```
