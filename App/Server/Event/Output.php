<?php

namespace App\Server\Event;

use Flames\RequestData;

class Output extends \Flames\Event\Output
{
    public function onOutput(RequestData $requestData, string $buffer) : string
    {
        return parent::onOutput($requestData, $buffer);
    }
}