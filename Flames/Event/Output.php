<?php

namespace Flames\Event;

use Flames\RequestData;

abstract class Output
{
    public function onOutput(RequestData $requestData, string $buffer) : string
    {
        return $buffer;
    }
}