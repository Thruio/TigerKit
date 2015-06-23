<?php
namespace TigerKit;

use Flynsarmy\SlimMonolog\Log\MonologWriter;
use Slim\Log;
use Slim\Slim;
use Monolog\Logger;
use Monolog\Handler as LogHandler;
use Monolog\Formatter as LogFormatter;

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

  static public function run()
  {
    if (self::$tigerApp) {
      $trace = debug_backtrace();
      $appRoot = dirname($trace[0]['file']);
      self::$tigerApp = new TigerApp($appRoot);
    }
    self::$tigerApp->begin();
  }

  static public function log($message, $level = Log::INFO)
  {
    self::$tigerApp->getLogger()->write($message, $level);
  }

  public function __construct($appRoot)
  {
    $this->appRoot = $appRoot;

    //$this->config =
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
   * @return MonologWriter
   */
  private function setupLogger()
  {
    $loggerHandlers = [];

    // Set up file logger.
    $fileLoggerHandler = new LogHandler\StreamHandler(TigerApp::AppRoot() . '/logs/' . date('Y-m-d') . '.log');
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

  /**
   * @return Slim
   */
  public function begin()
  {
    $this->logger = $this->setupLogger();

    // Initialise slim app.
    $this->slimApp = new Slim(array(
      'templates.path' => self::TemplatesRoot(),
      'log.writer' => $this->logger,
      'log.enabled' => true,
    ));

    return $this;
  }

}