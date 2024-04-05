<?php

namespace Flames;

use Exception;

class Process
{
    protected int $pid;

    public function __construct(string $command)
    {
        if (OS::isUnix() === true) {
            throw new Exception('Unix not supported yet.');
        }

        if ( $procSocket = proc_open("start /b " . $command, [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
        ], $pipes)) {
            $procStatus = proc_get_status($procSocket);
            $this->pid = $procStatus['pid'];
        }
    }

    public function destroy()
    {
        if (OS::isUnix() === true) {
            throw new Exception('Unix not supported yet.');
        }

        exec('taskkill /pid ' . $this->pid . ' /F');
    }
}