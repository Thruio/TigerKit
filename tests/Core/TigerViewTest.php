<?php
namespace TigerKit\Test\Core;

use Slim\Environment;
use TigerKit\Test\TigerBaseTest;
use TigerKit\TigerApp;
use TigerKit\TigerView;

class TigerViewTest extends TigerBaseTest
{

    /**
 * @var TigerView
*/
    private $tigerView;

    public function setUp()
    {
        TigerApp::run();
        $this->tigerView = new TigerView();
        $this->rrmdir(TigerApp::AppRoot() . "/public/cache");
    }

    private function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") {
                        rrmdir($dir."/".$object);

                    } else {
                        unlink($dir."/".$object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public function testCssAndJS()
    {
        $this->tigerView->addCSS("vendor/twbs/bootstrap/dist/css/bootstrap.min.css");
        $this->tigerView->addCSS("vendor/twbs/bootstrap/dist/css/bootstrap-theme.css");
        $this->tigerView->addJS("vendor/twbs/bootstrap/dist/js/bootstrap.js");
        $this->tigerView->addJS("vendor/twbs/bootstrap/dist/js/bootstrap.min.js");
        $this->assertEquals(2, count($this->tigerView->getCSS()));
        $this->assertEquals(2, count($this->tigerView->getJS()));
    }

    public function testUrl()
    {
        $this->assertEquals("http://facebook.com", $this->tigerView->url("http://facebook.com"));
        $this->assertEquals("/dashboard", $this->tigerView->url("/dashboard"));
    }

    public function testLink()
    {
        $this->assertEquals("<a href=\"/dashboard\">dashboard</a>", $this->tigerView->link("/dashboard", "dashboard"));
        $this->assertEquals("<a href=\"/dashboard\">dashboard</a>", $this->tigerView->l("/dashboard", "dashboard"));
        $this->assertEquals("<a class=\"extra-class another-class\" href=\"/dashboard\">dashboard</a>", $this->tigerView->link("/dashboard", "dashboard", ['classes' => ['extra-class', 'another-class']]));
    }

    public function testPageTitles()
    {
        $this->tigerView->setPageTitle("pagetitle");
        $this->tigerView->setSiteTitle("sitetitle");
        $this->assertEquals("sitetitle - pagetitle", $this->tigerView->getSiteTitle());
    }
}
