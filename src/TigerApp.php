<?php
namespace TigerKit;

use Flynsarmy\SlimMonolog\Log\MonologWriter;
use Slim\Log;
use Monolog\Logger;
use Monolog\Handler as LogHandler;
use Monolog\Formatter as LogFormatter;
use Symfony\Component\Yaml\Yaml;
use Thru\ActiveRecord;
use Thru\Session\Session;

class TigerApp
{
  /** @var TigerApp */
  static private $tigerApp;
  /** @var TigerSlim */
  private $slimApp;
  /** @var MonologWriter */
  private $logger;
  /** @var Session */
  private $session;

  // Store where the application was run() from
  private $appRoot;
  private $appTree;
  private $dbPool;
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
    ]
  ];

  static private $defaultAppTree = [
    "TopNav" => [
      'Left' => [
        ["Label" => "Home", "Url" => "/"],
        ["Label" => "About", "Url" => "/about"],
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
  static public function run()
  {
    if (!defined('APP_ROOT')) {
      die("APP_ROOT not defined. Are you not using bootstrap.php?");
    }

    if (!self::$tigerApp) {
      self::$tigerApp = new TigerApp(APP_ROOT);
    }

    $instance = self::$tigerApp->begin();

    return $instance;
  }

  static public function log($message, $level = Log::INFO)
  {
    self::$tigerApp->getLogger()->write($message, $level);
  }

  /**
   * @param string $appRoot
   */
  public function __construct($appRoot)
  {
    $this->appRoot = $appRoot;
  }

  static public function AppRoot()
  {
    return self::$tigerApp->appRoot;
  }

  static public function WebHost() {
    return self::$tigerApp->slimApp->request()->getHost();
  }

  static public function WebPort() {
    return self::$tigerApp->slimApp->request()->getPort();
  }

  static public function WebIsSSL() {
    return self::WebPort() == 443 ? true : false;
  }

  static public function WebRoot() {
    return(self::WebIsSSL() ? "https" : "http") . "://" . self::WebHost() . (!in_array(self::WebPort(), [443,80])?':'.self::WebPort():'') . rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\") . "/";
  }

  /**
   * @param string $key
   * @return string|array|false
   */
  static public function Config($key) {
    $indexes = explode(".", $key);
    $configData = self::$tigerApp->config;
    foreach ($indexes as $index) {
      if (isset($configData[$index])) {
        $configData = $configData[$index];
      }else {
        TigerApp::log("No such config index: {$key}");
        return false;
      }
    }
    return $configData;
  }

  static public function Tree($key) {
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

  static public function TemplatesRoot()
  {
    return self::AppRoot() . "/templates/";
  }

  static public function PublicRoot() {
    return self::AppRoot() . "/public/";
  }

  static public function PublicCacheRoot() {
    return self::AppRoot() . "/public/cache/";
  }

  static public function LogRoot() {
    return self::AppRoot() . "/logs/";
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
  static public function getSlimApp() {
    return self::$tigerApp->slimApp;
  }

  private function parseConfig() {
    $configFile = "Default.yaml";

    if (getenv('HOST')) {
      $configFile = getenv('HOST') . ".yaml";
    }

    $configPath = "{$this->appRoot}/config/{$configFile}";

    if (!file_exists($configPath)) {

      if (!file_exists(dirname($configPath))) {
        if (!mkdir(dirname($configPath))) {
          throw new TigerException("Cannot write to " . dirname($configPath));
        }
      }
      $success = file_put_contents($configPath, Yaml::dump(self::$defaultConfig));
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
    $loggerHandlers = [];

    // Set up file logger.
    if (!file_exists(TigerApp::LogRoot())) {
      mkdir(TigerApp::LogRoot(), 0777, true);
    }
    $fileLoggerHandler = new LogHandler\StreamHandler(TigerApp::LogRoot() . date('Y-m-d') . '.log', null, null, 0664);
    $loggerHandlers[] = $fileLoggerHandler;

    // Set up Chrome Logger
    $chromeLoggerHandler = new LogHandler\ChromePHPHandler();
    $chromeLoggerHandler->setFormatter(new LogFormatter\ChromePHPFormatter());
    $loggerHandlers[] = $chromeLoggerHandler;

    // Set up Slack Logger
    #$slackLoggerHandler = new LogHandler\SlackHandler(SLACK_TOKEN, SLACK_CHANNEL, SLACK_USER, null, null, Logger::DEBUG);
    #$slackLoggerHandler->setFormatter(new LogFormatter\LineFormatter());
    #$loggerHandlers[] = $slackLoggerHandler;

    $logger = new MonologWriter(array(
      'handlers' => $loggerHandlers,
    ));

    return $logger;
  }

  private function parseRoutes() {
    $app = $this->slimApp;
    $routesFile = APP_ROOT . "/config/Routes.php";
    if (file_exists($routesFile)) {
      require($routesFile);
    }
  }

  /**
   * @return TigerApp
   */
  public function begin()
  {
    $this->parseConfig();

    $this->logger = $this->setupLogger();

    if ($this->config['Debug Mode'] == "On") {
      error_reporting(E_ALL);
      ini_set("display_errors", 1);
    }

    // TODO: Load app tree from yaml
    $this->appTree = self::$defaultAppTree;

    // Initialise databases
    foreach (TigerApp::Config("Databases") as $name => $config) {
      #\Kint::dump($config);exit;
      $this->dbPool[$name] = new ActiveRecord\DatabaseLayer(array(
        'db_type'     => $config['Type'],
        'db_hostname' => $config['Host'],
        'db_port'     => $config['Port'],
        'db_username' => $config['Username'],
        'db_password' => $config['Password'],
        'db_database' => $config['Database']
      ));
    }

    // Initialise Redis Pool
    // TODO: Write this.

    // Initialise Session
    $this->session = new Session();

    // Initialise slim app.
    $this->slimApp = new TigerSlim(array(
      'templates.path' => self::TemplatesRoot(),
      'log.writer' => $this->logger,
      'log.enabled' => true,
    ));

    // Set the View controller.
    // TODO: Make this settable in the config or somewhere in the sample App
    $this->slimApp->view(new TigerView());

    // Add routes to slim
    $this->parseRoutes();

    return $this;
  }

  public function invoke() {
    return $this->slimApp->invoke();
  }

  public function execute() {
    return $this->slimApp->run();
  }
}