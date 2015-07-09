<?php

namespace TigerKit\Test;

use Slim\Slim;
use Slim\Environment;
use Slim\Http\Response;
use TigerKit\TigerApp;
use Faker;

abstract class TigerWebBaseTest extends TigerBaseTest
{

  /** @var Slim */
    protected $slim;

    public function setUp()
    {
        parent::setUp();
    }

  /**
   * @param string $path
   * @return Response
   */
    protected function doRequest($method = "GET", $path = "/", $params = [])
    {
        $requestParams = array(
        'PATH_INFO' => $path,
        'REQUEST_METHOD' => $method,
        );

        if ($method == "POST") {
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
