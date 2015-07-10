<?php
namespace TigerKit\Test\Core;

use Slim\Environment;
use TigerKit\TigerApp;

class TigerAppTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        TigerApp::run();
    }

    public function testConfigExists()
    {
        $this->assertEquals("Tiger Starter App", TigerApp::Config('Application Name'));
    }

    public function testConfigDoesNotExist()
    {
        $this->assertFalse(TigerApp::Config('Bogus'));
    }

    public function testTreeExists()
    {
        $this->assertTrue(is_array(TigerApp::Tree("TopNav.Left")));
    }

  /**
   * @expectedException \TigerKit\TigerException
   * @expectedExceptionMessage No such tree node index: TopNav.Whoops
   */
    public function testTreeDoesNotExist()
    {
        $this->assertTrue(is_array(TigerApp::Tree("TopNav.Whoops")));
    }

    public function testPathUtils()
    {
        $requestParams = array(
        'PATH_INFO' => "/",
        'REQUEST_METHOD' => "GET",
        );

        Environment::mock($requestParams);

        $this->assertEquals(APP_ROOT . "/build/logs/", TigerApp::LogRoot());
        $this->assertEquals(APP_ROOT . "/templates/", TigerApp::TemplatesRoot());
      #$this->assertEquals(APP_ROOT . "/templates", TigerApp::WebDiskRoot());
        $this->assertEquals(APP_ROOT . "/public/", TigerApp::PublicRoot());
        $this->assertEquals(APP_ROOT . "/public/cache/", TigerApp::PublicCacheRoot());
        $this->assertEquals("localhost", TigerApp::WebHost());
        $this->assertEquals(80, TigerApp::WebPort());
        $this->assertEquals(false, TigerApp::WebIsSSL());
    }

    public function testExecute()
    {
        ob_start();
        TigerApp::run()
        ->begin()
        ->execute();
        ob_end_clean();
    }

    public function testGetStorage()
    {
        $storage = TigerApp::getStorage();
        $this->assertEquals('League\Flysystem\Filesystem', get_class($storage));
    }

    public function testParseConfig()
    {
        TigerApp::run()->begin()->parseConfig(TigerApp::AppRoot() . "/config/Default.yaml");
    }

    public function testParseConfigDoesntExist()
    {
        TigerApp::run()->begin()->parseConfig("/tmp/nochancebro/DoesntExist.yaml");
        unlink("/tmp/nochancebro/DoesntExist.yaml");
        rmdir("/tmp/nochancebro");
    }

  /**
   * @expectedException \TigerKit\TigerException
   * @expectedExceptionMessage Cannot write to /nochancebro
   */
    public function testParseConfigDirCannotExist()
    {
        TigerApp::run()->begin()->parseConfig("/nochancebro/DoesntExist.yaml");
    }

  /**
   * @expectedException \TigerKit\TigerException
   * @expectedExceptionMessage Cannot write to /DoesntExist.yaml
   */
    public function testParseConfigFileCannotExist()
    {
        TigerApp::run()->begin()->parseConfig("/DoesntExist.yaml");
    }

  /**
   * @expectedException \TigerKit\TigerException
   * @expectedExceptionMessage Unsupported storage type: notatype.
   */
    public function testSetupStorageBogusType()
    {
        TigerApp::run()->begin()->setupStorage(['Type'=> 'notatype']);
    }
}
