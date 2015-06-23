<?php
namespace TigerKit;

use Flynsarmy\SlimMonolog\Log\MonologWriter;
use Slim\Log;
use Slim\Slim;
use Monolog\Logger;
use Monolog\Handler as LogHandler;
use Monolog\Formatter as LogFormatter;
use Symfony\Component\Yaml\Yaml;

class TigerApp
{
  /** @var TigerApp */
  static private $tigerApp;
  /** @var Slim */
  private $slimApp;
  /** @var MonologWriter */
  private $logger;

  // Store where the application was run() from
  private $appRoot;
  private $config;

  static private $defaultConfig = [
    "Application Name" => "Tiger Starter App",
    "Debug Mode" => "On",
  ];

  static public function run()
  {
    if (!self::$tigerApp) {
      $trace = debug_backtrace();
      $appRoot = dirname($trace[0]['file']);
      self::$tigerApp = new TigerApp($appRoot);
    }

    $instance = self::$tigerApp->begin();

    $instance->execute();
  }

  static public function log($message, $level = Log::INFO)
  {
    self::$tigerApp->getLogger()->write($message, $level);
  }

  public function __construct($appRoot)
  {
    $this->appRoot = $appRoot;
  }

  static public function AppRoot()
  {
    return self::$tigerApp->appRoot;
  }

  static public function TemplatesRoot()
  {
    return self::AppRoot() . "/../templates/";
  }

  /**
   * @return MonologWriter
   */
  public function getLogger()
  {
    return $this->logger;
  }

  /**
   * @return Slim
   */
  static public function getSlimApp(){
    return self::$tigerApp->slimApp;
  }

  private function parseConfig(){
    $configFile = "Default.yaml";
    $configPath = "{$this->appRoot}/../config/{$configFile}";

    if(!file_exists($configPath)){

      if(!file_exists(dirname($configPath))){
        if(!mkdir(dirname($configPath))){
          throw new TigerException("Cannot write to " . dirname($configPath));
        }
      }
      $success = file_put_contents($configPath, Yaml::dump(self::$defaultConfig));
      if(!$success){
        throw new TigerException("Cannot write to {$configPath}");
      }
    }
    $this->config = Yaml::parse($configPath);
  }

  /**
   * @return MonologWriter
   */
  private function setupLogger()
  {
    $loggerHandlers = [];

    // Set up file logger.
    $fileLoggerHandler = new LogHandler\StreamHandler(TigerApp::AppRoot() . '/../logs/' . date('Y-m-d') . '.log');
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

  private function parseRoutes(){
    $routesPath = "{$this->appRoot}/../config/Routes.php";
    if(!file_exists($routesPath)){
      throw new TigerException("Routes file {$routesPath} is missing.");
    }
    $app = $this->slimApp;
    require_once($routesPath);
  }

  /**
   * @return Slim
   */
  public function begin()
  {
    $this->parseConfig();

    $this->logger = $this->setupLogger();

    if($this->config['Debug Mode'] == "On"){
      error_reporting(E_ALL);
      ini_set("display_errors", 1);
    }

    // Initialise slim app.
    $this->slimApp = new Slim(array(
      'templates.path' => self::TemplatesRoot(),
      'log.writer' => $this->logger,
      'log.enabled' => true,
    ));

    $this->parseRoutes();

    return $this;
  }

  public function execute(){
    $this->slimApp->run();
  }

}