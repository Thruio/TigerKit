<?php
namespace TigerKit\Test\Core;

use Slim\Environment;
use Slim\Http\Response;
use TigerKit\Test\TigerBaseTest;
use TigerKit\TigerApp;
use TigerKit\TigerSlim;
use TigerKit\TigerView;

class TigerSlimTest extends TigerBaseTest
{


    public function testTigerSlimInvoke()
    {
        $body = implode("\n\n", $this->faker->paragraphs(5));
        TigerApp::run();

        $tigerApp = new TigerApp(__DIR__);
        $tigerSlim = $tigerApp->getSlimApp();
        $tigerSlim->get(
            "/tigerslimtest", function () use ($tigerSlim, $body) {
                $tigerSlim->response()->body($body);
            }
        );

        $requestParams = array(
          'PATH_INFO' => "/tigerslimtest",
          'REQUEST_METHOD' => "GET",
        );

        Environment::mock($requestParams);

        $response = $tigerSlim->invoke();

        $this->assertTrue($response instanceof Response);
        $this->assertEquals(200, $response->getStatus());
        $this->assertEquals($body, $response->getBody());
        $this->assertEquals(strlen($body), $response->getLength());

    }


}
