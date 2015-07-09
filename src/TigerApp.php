<?php
namespace TigerKit;

use Flynsarmy\SlimMonolog\Log\MonologWriter;
use Slim\Log;
use Monolog\Logger;
use Monolog\Handler as LogHandler;
use Monolog\Formatter as LogFormatter;
use Symfony\Component\Yaml\Yaml;
use League\Flysystem;
use Thru\ActiveRecord;
use Thru\Session\Session;
use Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware;

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
        ["Label" => "Github", "Url" => "https://github.com/Thruio/TigerSampleApp"],
        ["Label" => "Boards", "Url" => "/r/dashboard"],
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
  static public function getSlimApp() {
    return self::$tigerApp->slimApp;
  }

  /**
   * @param string $pool
   * @return Flysystem\Filesystem
   */
  static public function getStorage($pool = 'Default'){
    return self::$tigerApp->storagePool[$pool];
  }

  public function parseConfig($configPath) {
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
    $fileLoggerHandler = new LogHandler\StreamHandler(TigerApp::LogRoot() . date('Y-m-d') . '.log', null, null, 0664);

    // Set up Chrome Logger
    $chromeLoggerHandler = new LogHandler\ChromePHPHandler();
    $chromeLoggerHandler->setFormatter(new LogFormatter\ChromePHPFormatter());

    // Set up Slack Logger
    #$slackLoggerHandler = new LogHandler\SlackHandler(SLACK_TOKEN, SLACK_CHANNEL, SLACK_USER, null, null, Logger::DEBUG);
    #$slackLoggerHandler->setFormatter(new LogFormatter\LineFormatter());

    $logger = new MonologWriter(array(
      'handlers' => [
        $fileLoggerHandler,
        $chromeLoggerHandler,
        #$slackLoggerHandler,
      ],
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
    $configFile = (getenv('HOST')?getenv('HOST'):'Default') . '.yaml';
    $this->parseConfig("{$this->appRoot}/config/{$configFile}");

    $this->logger = $this->setupLogger();

    if ($this->config['Debug Mode'] == "On") {
      error_reporting(E_ALL);
      ini_set("display_errors", 1);
    }

    // TODO: Load app tree from yaml
    $this->appTree = self::$defaultAppTree;

    // Initialise databases
    if(count(TigerApp::Config("Databases")) > 0) {
      foreach (TigerApp::Config("Databases") as $name => $config) {
        #\Kint::dump($config);exit;
        $this->dbPool[$name] = new ActiveRecord\DatabaseLayer(array(
          'db_type' => $config['Type'],
          'db_hostname' => $config['Host'],
          'db_port' => $config['Port'],
          'db_username' => $config['Username'],
          'db_password' => $config['Password'],
          'db_database' => $config['Database']
        ));
      }
    }

    // Initialise Storage Pool
    if(count(TigerApp::Config("Storage")) > 0) {
      foreach (TigerApp::Config("Storage") as $name => $config) {
        $this->storagePool[$name] = $this->setupStorage($config);
      }
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

    // Set up whoops
    //$this->slimApp->config('whoops.editor', 'phpstorm');
    $this->slimApp->add(new WhoopsMiddleware());

    // Set the View controller.
    // TODO: Make this settable in the config or somewhere in the sample App
    $this->slimApp->view(new TigerView());

    // Add routes to slim
    $this->parseRoutes();

    return $this;
  }

  public function setupStorage($config){
    switch (strtolower($config['Type'])) {
      case 'zip':
        $adaptor = new Flysystem\ZipArchive\ZipArchiveAdapter(APP_ROOT . "/" . $config['Location']);
        break;
      default:
        throw new TigerException("Unsupported storage type: {$config['Type']}.");
    }

    return new Flysystem\Filesystem($adaptor);
  }

  public function invoke() {
    return $this->slimApp->invoke();
  }

  public function execute() {
    return $this->slimApp->run();
  }
}