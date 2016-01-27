<?php

namespace TigerKit;

class TigerException extends \Exception
{

    public function getJsonException()
    {
        return json_encode(
            [
            'status' => 'FAIL',
            'status_message' => $this->getMessage(),
            ]
        );
    }
}
