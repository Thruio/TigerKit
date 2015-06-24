<?php

namespace TigerKit\Test;

use Slim\Slim;
use Slim\Environment;
use Slim\Http\Response;
use TigerKit\TigerApp;

class TigerWebBaseTest extends \PHPUnit_Framework_TestCase {
  /** @var TigerApp */
  private $tiger;
  /** @var Slim */
  protected $slim;

  public function setUp()
  {
    $_SESSION = array();
  }

  /**
   * @param string $path
   * @return Response
   */
  protected function doRequest($method = "GET", $path = "/"){
    Environment::mock(array(
      'PATH_INFO' => $path,
      'REQUEST_METHOD' => $method
     ));
    $this->tiger = TigerApp::run();

    $this->slim = $this->tiger->getSlimApp();
    $response = $this->tiger->invoke();

    return $response;
  }
}
