<?php

namespace Flames;

/**
 * Class Process
 *
 * The Process class represents a running process on the system.
 */
class Process
{
    protected int $pid;

    /**
     * Constructor method for the class.
     *
     * @param string $command The command to be executed.
     */
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

    /**
     * Get the process ID.
     *
     * @return int|null The process ID if available, otherwise null.
     */
    public function getPid() : int|null
    {
        return $this->pid;
    }

    /**
     * Method to destroy the running process.
     *
     * @return void
     */
    public function destroy() : void
    {
        if (OS::isUnix() === true) {
            exec('kill -9 ' . $this->pid);
            return;
        }

        exec('taskkill /pid ' . $this->pid . ' /F');
    }
}