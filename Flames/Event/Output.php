<?php

namespace Flames\Event;

use Flames\RequestData;

abstract class Output
{
    public function onOutput(RequestData $requestData, string|null $buffer) : string|null
    {
        return $buffer;
    }
}