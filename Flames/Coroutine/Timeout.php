<?php

namespace Flames\Coroutine;

use Exception;
use Flames\Collection\Arr;
use Flames\Coroutine\Timeout\Event;
use Flames\Js;
use Flames\Kernel;

class Timeout
{
    protected array $uids = [];

    public function add($delegate, array|Arr $args = null, ?int $miliseconds = 1)
    {
        if (Kernel::MODULE === 'SERVER') {
            throw new Exception('Method only works on client.');
        }

        $uid = (count(Event::$delegatesData) + 1);

        if ($miliseconds === null) {
            $miliseconds = 1;
        }

        if ($args instanceof Arr) {
            $args = $args->toArray();
        } elseif ($args === null) {
            $args = [];
        }

        Event::$delegatesData[$uid] = Arr([
            'delegate' => $delegate,
            'args' => $args,
            'miliseconds' => $miliseconds
        ]);
        $this->uids[] = $uid;
    }

    public function run()
    {
        foreach ($this->uids as $uid) {
            $delegateData = Event::$delegatesData[$uid];

            Js::eval("
                (function() {
                    window.setTimeout(function() {
                        window.PHP.eval('<?php \\\\Flames\\\\Coroutine\\\\Timeout\\\\Event::onDispatch(" . $uid . "); ?>');
                    }, " . $delegateData->miliseconds . ");
                })();
            ");
        }
    }
}