<?php

namespace Flames;

use Exception;

class Process
{
    protected int $pid;

    public function __construct(string $command)
    {
        if (OS::isUnix() === true) {
            $command = ($command . ' > /dev/null 2>&1 & echo $!');
            exec($command, $output);

            if (is_array($output) === true) {
                $this->pid = (int)$output[0];
            }

            return;
        }

        if ( $procSocket = proc_open("start /b " . $command, [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
        ], $pipes)) {
            $procStatus = proc_get_status($procSocket);
            $this->pid = $procStatus['pid'];
        }
    }

    public function getPid() : int|null
    {
        return $this->pid;
    }

    public function destroy()
    {
        if (OS::isUnix() === true) {
            exec('kill -9 ' . $this->pid);
            return;
        }

        exec('taskkill /pid ' . $this->pid . ' /F');
    }
}