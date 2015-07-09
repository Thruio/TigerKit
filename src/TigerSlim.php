<?php
namespace TigerKit;

use Slim\Slim;

class TigerSlim extends Slim
{

    public function invoke()
    {
        $this->middleware[0]->call();
        $this->response()->finalize();
        return $this->response();
    }
}
