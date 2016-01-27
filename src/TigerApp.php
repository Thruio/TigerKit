<?php
namespace TigerKit;

use Aws\S3\S3Client;
use Flynsarmy\SlimMonolog\Log\MonologWriter;
use League\Flysystem;
use Monolog\Formatter as LogFormatter;
use Monolog\Handler as LogHandler;
use Monolog\Logger;
use Slim\Log;
use Symfony\Component\Yaml\Yaml;
use Thru\ActiveRecord;
use Thru\Session\Session;
use Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware;

class TigerApp
{
    /**
     * @var TigerApp
     */
    static private $tigerApp;
    /**
     * @var TigerSlim
     */
    private $slimApp;
    /**
     * @var MonologWriter
     */
    private $logger;
    /**
     * @var Session
     */
    private $session;

    // Store where the application was run() from
    private $appRoot;
    private $appTree;
    private $dbPool;
    private $storagePool;
    private $config;

    static private $defaultConfig = [
      "Application Name" => "Tiger Starter App",
      "Copyright" => "Your Name Here",
      "Debug Mode" => "On",
      "Databases" => [
        "Default" => [
          "Type" => "Mysql",
          "Host" => "localhost",
          "Port" => 3306,
          "Username" => "tiger",
          "Password" => "tiger",
          "Database" => "tiger",
        ]
      ],
      "Caches" => [
        "Default" => [
          "Type" => "Redis",
          "Host" => "localhost",
          "Port" => 6379,
          "Database" => 5,
        ]
      ],
      "Storage" => [
        "Default" => [
          "Type" => "Zip",
          "Location" => "datablob.zip"
        ]
      ]
    ];

    static private $defaultAppTree = [
      "TopNav" => [
        'Left' => [
          ["Label" => "Home", "Url" => "/"],
          ["Label" => "About", "Url" => "/about"],
          ["Label" => "Boards", "Url" => "/r/dashboard"],
          ["Label" => "Image Gallery", "Url" => "/gallery"],
          ["Label" => "Github", "Url" => "https://github.com/Thruio/TigerSampleApp"],
        ],
        'Right' => [
          ["Label" => "Login", "Url" => "/login"],
          ["Label" => "Logout", "Url" => "/logout"],
        ]
      ]
    ];

    /**
     * @return TigerApp
     */
    public static function run()
    {
        if (!self::$tigerApp) {
            self::$tigerApp = new TigerApp(APP_ROOT);
        }

        $instance = self::$tigerApp->begin();

        return $instance;
    }

    public static function log($message, $level = Log::INFO)
    {
        error_log($message, $level);
        self::$tigerApp->getLogger()->write($message, $level);
    }

    /**
     * @param string $appRoot
     */
    public function __construct($appRoot)
    {
        $this->appRoot = $appRoot;
    }

    /**
     * @return TigerApp
     */
    public static function Instance()
    {
        return self::$tigerApp;
    }

    public static function AppRoot()
    {
        return self::$tigerApp->appRoot;
    }

    public static function WebHost()
    {
        return self::$tigerApp->slimApp->request()->getHost();
    }

    public static function WebPort()
    {
        return self::$tigerApp->slimApp->request()->getPort();
    }

    public static function WebIsSSL()
    {
        return self::WebPort() == 443 ? true : false;
    }

    public static function WebRoot()
    {
        return (self::WebIsSSL() ? "https" : "http") . "://" . self::WebHost() . (!in_array(
            self::WebPort(),
            [443, 80]
        ) ? ':' . self::WebPort() : '') . rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\") . "/";
    }

    /**
     * @param string $key
     * @return string|array|false
     */
    public static function Config($key)
    {
        $indexes = explode(".", $key);
        $configData = self::$tigerApp->config;
        foreach ($indexes as $index) {
            if (isset($configData[$index])) {
                $configData = $configData[$index];
            } else {
                TigerApp::log("No such config index: {$key}");

                return false;
            }
        }

        return $configData;
    }

    public static function Tree($key)
    {
        $indexes = explode(".", $key);
        $treeData = self::$tigerApp->appTree;
        foreach ($indexes as $index) {
            if (isset($treeData[$index])) {
                $treeData = $treeData[$index];
            } else {
                throw new TigerException("No such tree node index: {$key}");
            }
        }

        return $treeData;
    }

    public static function TemplatesRoot()
    {
        return self::AppRoot() . "/templates/";
    }

    public static function PublicRoot()
    {
        return self::AppRoot() . "/public/";
    }

    public static function PublicCacheRoot()
    {
        return self::AppRoot() . "/public/cache/";
    }

    public static function LogRoot()
    {
        return self::AppRoot() . "/build/logs/";
    }

    /**
     * @return MonologWriter
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return TigerSlim
     */
    public static function getSlimApp()
    {
        return self::$tigerApp->slimApp;
    }

    /**
     * @param string $pool
     * @return Flysystem\Filesystem
     */
    public static function getStorage($pool = 'Default')
    {
        return self::$tigerApp->storagePool[$pool];
    }

    public function parseConfig($configPath)
    {
        if (!file_exists($configPath)) {
            if (!file_exists(dirname($configPath))) {
                if (!@mkdir(dirname($configPath))) {
                    throw new TigerException("Cannot write to " . dirname($configPath));
                }
            }
            $success = @file_put_contents($configPath, Yaml::dump(self::$defaultConfig));
            if (!$success) {
                throw new TigerException("Cannot write to {$configPath}");
            }
        }
        $this->config = Yaml::parse(file_get_contents($configPath));
    }

    /**
     * @return MonologWriter
     */
    private function setupLogger()
    {
        // Set up file logger.
        /*
        $fileLoggerFile = TigerApp::LogRoot() . date('Y-m-d') . '.log';
        if(!file_exists(dirname($fileLoggerFile))){
            mkdir(dirname($fileLoggerFile),0777,true);
        }
        $fileLoggerHandler = new LogHandler\StreamHandler(
            $fileLoggerFile,
            Logger::DEBUG,
            true,
            0664
        );*/

        // Set up Chrome Logger
        $chromeLoggerHandler = new LogHandler\ChromePHPHandler();
        $chromeLoggerHandler->setFormatter(new LogFormatter\ChromePHPFormatter());

        // Set up Slack Logger
        // $slackLoggerHandler = new LogHandler\SlackHandler(SLACK_TOKEN, SLACK_CHANNEL, SLACK_USER, null, null, Logger::DEBUG);
        // $slackLoggerHandler->setFormatter(new LogFormatter\LineFormatter());

        $logger = new MonologWriter(
            array(
            'handlers' => [
              //$fileLoggerHandler,
              $chromeLoggerHandler,
                // $slackLoggerHandler,
            ],
            )
        );

        return $logger;
    }

    private function parseRoutes()
    {
        $app = $this->slimApp;
        $routesFile = APP_ROOT . "/config/Routes.php";
        if (file_exists($routesFile)) {
            include $routesFile;
        }
    }

    /**
     * @return TigerApp
     */
    public function begin()
    {
        if (defined('APP_ENV')) {
            $configFile = APP_ENV . '.yaml';
        } elseif (getenv('HOST')) {
            $configFile = getenv('HOST') . '.yaml';
        } else {
            $configFile = 'Default.yaml';
        }
        $this->parseConfig("{$this->appRoot}/config/{$configFile}");

        $this->logger = $this->setupLogger();

        if ($this->config['Debug Mode'] == "On") {
            error_reporting(E_ALL);
            ini_set("display_errors", 1);
        }

        $environment = array_merge($_ENV, $_SERVER);
        ksort($environment);

        // TODO: Load app tree from yaml
        $this->appTree = self::$defaultAppTree;

        // Initialise databases
        if (count(TigerApp::Config("Databases")) > 0) {
            foreach (TigerApp::Config("Databases") as $name => $settings) {
                $config = array();

                $config['db_type'] = $settings['Type'];
                if (isset($settings['DockerLink'])) {
                    $prefix = strtoupper($settings['DockerLink']);
                    if (isset($environment["{$prefix}_PORT"])) {
                        $host = parse_url($environment["{$prefix}_PORT"]);
                        $config['db_hostname'] = $host['host'];
                        $config['db_port'] = $host['port'];
                        if (isset($environment["{$prefix}_USERNAME"])) {
                            $config['db_username'] = $environment["{$prefix}_USERNAME"];
                        }
                        if (isset($environment["{$prefix}_ENV_MYSQL_USER"])) {
                            $config['db_username'] = $environment["{$prefix}_ENV_MYSQL_USER"];
                        }
                        if (isset($environment["{$prefix}_PASSWORD"])) {
                            $config['db_password'] = $environment["{$prefix}_PASSWORD"];
                        }
                        if (isset($environment["{$prefix}_ENV_MYSQL_PASSWORD"])) {
                            $config['db_password'] = $environment["{$prefix}_ENV_MYSQL_PASSWORD"];
                        }
                        if (isset($environment["{$prefix}_DATABASE"])) {
                            $config['db_database'] = $environment["{$prefix}_DATABASE"];
                        }
                        if (isset($environment["{$prefix}_ENV_MYSQL_DATABASE"])) {
                            $config['db_database'] = $environment["{$prefix}_ENV_MYSQL_DATABASE"];
                        }
                    } else {
                        throw new \Exception("Cannot find \$environment[{$prefix}_PORT] trying to use DockerLink config.");
                    }
                }
                if (isset($settings['Host'])) {
                    $config['db_hostname'] = $settings['Host'];
                }
                if (isset($settings['Port'])) {
                    $config['db_port'] = $settings['Port'];
                }
                if (isset($settings['Username'])) {
                    $config['db_username'] = $settings['Username'];
                }
                if (isset($settings['Password'])) {
                    $config['db_password'] = $settings['Password'];
                }
                if (isset($settings['Database'])) {
                    $config['db_database'] = $settings['Database'];
                }

                // Sqlite-specific
                if (isset($settings['File'])) {
                    $config['db_file'] = $settings['File'];
                }

                $this->dbPool[$name] = new ActiveRecord\DatabaseLayer($config);
            }
        }

        // Initialise Storage Pool

        if (TigerApp::Config("Storage") !== false) {
            foreach (TigerApp::Config("Storage") as $name => $config) {
                $this->storagePool[$name] = $this->setupStorage($config);
            }
        }

        // Initialise Redis Pool
        // TODO: Write this.

        // Initialise Session
        $this->session = new Session();

        // Initialise slim app.
        $this->slimApp = new TigerSlim(
            array(
            'templates.path' => self::TemplatesRoot(),
            'log.writer' => $this->logger,
            'log.enabled' => true,
            )
        );

        // Set up whoops
        //$this->slimApp->config('whoops.editor', 'phpstorm');
        //$this->slimApp->add(new WhoopsMiddleware());

        // Set the View controller.
        // TODO: Make this settable in the config or somewhere in the sample App
        $this->slimApp->view(new TigerView());

        // Add routes to slim
        $this->parseRoutes();

        return $this;
    }

    public function setupStorage($config)
    {
        switch (strtolower($config['Type'])) {
        case 'zip':
            $adaptor = new Flysystem\ZipArchive\ZipArchiveAdapter(APP_ROOT . "/" . $config['Location']);
            break;
        case "s3":
            $clientConfig = [];
            if(isset($config['BaseUrl'])) {
                $clientConfig['base_url'] = $config['BaseUrl'];
            }
            $clientConfig['key'] = $config['Key'];
            $clientConfig['secret'] = $config['Secret'];
            if(isset($config['Region'])) {
                $clientConfig['region'] = $config['Region'];
            }
            // \Kint::dump($clientConfig);exit;
            $client  = S3Client::factory($clientConfig);
            $adaptor = new Flysystem\AwsS3v2\AwsS3Adapter($client, $config['Bucket']);
            break;
        default:
            throw new TigerException("Unsupported storage type: {$config['Type']}.");
        }

        return new Flysystem\Filesystem($adaptor);
    }

    public function invoke()
    {
        return $this->slimApp->invoke();
    }

    public function execute()
    {
        return $this->slimApp->run();
    }
}
