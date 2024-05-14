<?php

namespace Flames\Process;

use Flames\OS;

/**
 * Class WSL
 *
 * The WSL class represents a running process on the system using Windows Subsystem Linux.
 */
class WSL extends \Flames\Process
{
    protected int $pid;

    /**
     * Constructor method for the class.
     *
     * @param string $command The command to be executed.
     */
    public function __construct(string $command)
    {
        if (OS::isUnix() === false || \Flames\WSL::isWSL() === false) {
            parent::__construct($command);
            return;
        }

        $split = explode(' ', $command);
        $split[0] = str_replace('\\', '\\\\', $split[0]);
        $command = '';
        foreach ($split as $part) {
            $command .= ($part . ' ');
        }
        $command = substr($command, 0, -1);

        chdir('/mnt/c/Windows/');
        $command = ('cmd.exe /C ' . $command . ' > /dev/null 2>&1 & echo $!');
        exec($command, $output);
        chdir(ROOT_PATH);

        if (is_array($output) === true) {
            $this->pid = (int)$output[0];
        }
    }
}