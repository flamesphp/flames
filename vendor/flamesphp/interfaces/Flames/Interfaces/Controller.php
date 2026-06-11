<?php
declare(strict_types=1);


namespace Flames\Interfaces;

use Flames\Collection\Arr;
use Flames\Framework\Controller\Response;
use Flames\Framework\Controller\RequestData;

interface Controller
{
    public function onRequest(RequestData $requestData): string|array|Arr|Response;
}
