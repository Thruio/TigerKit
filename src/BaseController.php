<?php
namespace TigerKit;

use Slim\Slim;

class BaseController
{

    /**
     * @var Slim
*/
    protected $slim;

    public function __construct()
    {
        $this->slim = TigerApp::getSlimApp();
    }
}
