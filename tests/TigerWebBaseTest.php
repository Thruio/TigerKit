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
    $this->tiger = new TigerApp(APP_ROOT);
    $this->slim = $this->tiger->getSlimApp();
  }

  /**
   * @param string $path
   * @return Response
   */
  protected function doRequest($path = "/"){
    Environment::mock(array(
      'PATH_INFO' => $path
    ));
    $response = $this->slim->invoke();

    return $response;
  }
}
