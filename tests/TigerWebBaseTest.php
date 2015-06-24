<?php

namespace TigerKit\Test;

use Slim\Slim;
use Slim\Environment;
use Slim\Http\Response;
use TigerKit\TigerApp;
use Faker;

abstract class TigerWebBaseTest extends \PHPUnit_Framework_TestCase {
  /** @var TigerApp */
  private $tiger;
  /** @var Slim */
  protected $slim;
  /** @var Faker\Factory */
  protected $faker;

  public function setUp()
  {
    $this->tiger = TigerApp::run();
    parent::setUp();
    $_SESSION = array();
    $this->faker = Faker\Factory::create();
    $this->faker->addProvider(new Faker\Provider\en_US\Person($this->faker));
    $this->faker->addProvider(new Faker\Provider\en_US\Address($this->faker));
    $this->faker->addProvider(new Faker\Provider\en_US\PhoneNumber($this->faker));
    $this->faker->addProvider(new Faker\Provider\en_US\Company($this->faker));
    $this->faker->addProvider(new Faker\Provider\Lorem($this->faker));
    $this->faker->addProvider(new Faker\Provider\Internet($this->faker));
  }

  /**
   * @param string $path
   * @return Response
   */
  protected function doRequest($method = "GET", $path = "/", $params = []){
    $requestParams = array(
      'PATH_INFO' => $path,
      'REQUEST_METHOD' => $method,
    );

    if($method == "POST"){
      $requestParams['QUERY_STRING'] = http_build_query($params);
      $requestParams['slim.input'] = http_build_query($params);
    }

    Environment::mock($requestParams);
    $this->tiger = TigerApp::run();

    $this->slim = $this->tiger->getSlimApp();
    $response = $this->tiger->invoke();

    return $response;
  }
}
