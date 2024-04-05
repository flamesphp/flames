<?php

namespace Flames;

use Flames\Collection\Arr;
use Flames\Cryptography\Hash;
use Flames\Exception\Coroutine\Timeout;

class Coroutine
{
    protected const BASE_FOLDER = '.cache/coroutine/';

    protected int $timeout;
    protected string $caller;
    protected array $coroutines = [];

    public function __construct(int $timeout = 0)
    {
        $this->caller = self::__getCaller();
        $this->timeout = $timeout;
    }

    public function add(string $method, mixed $args = null)
    {
        $args = func_get_args();
        array_splice($args, 0, 1);

        $serializedArgs = [];
        foreach ($args as $arg) {
            $serializedArgs[] = serialize($arg);
        }

        $this->coroutines[] = [
            'method' => $method,
            'args'   => $serializedArgs,
            'hash'   => Hash::getRandom()
        ];
    }

    public function run(bool $echoOutput = false) : Arr
    {
        $coroutinePath = (ROOT_PATH . self::BASE_FOLDER);
        if (is_dir($coroutinePath) === false) {
            mkdir($coroutinePath, 0777, true);
        }

        $results = Arr();
        $coroutinesWaiting = [];

        foreach ($this->coroutines as $coroutine) {
            $coroutine['caller'] = $this->caller;
            file_put_contents($coroutinePath . $coroutine['hash'], serialize($coroutine));

            $coroutinesWaiting[] = [
                'coroutine' => $coroutine,
                'process'   => new Process('php bin internal:coroutine ' . $coroutine['hash'])
            ];
        }

        $coroutineWaitingTotal = count($coroutinesWaiting);
        $coroutinesFinished = [];

        $startTime = microtime(true) * 1000;
        while (true) {
            foreach ($coroutinesWaiting as $coroutineWaiting) {
                if (in_array($coroutineWaiting['coroutine']['hash'], $coroutinesFinished) === false && file_exists($coroutinePath . sha1($coroutineWaiting['coroutine']['hash'])) === true) {
                    $coroutinesFinished[] = $coroutineWaiting['coroutine']['hash'];
                }
            }

            if (count($coroutinesFinished) === $coroutineWaitingTotal) {
                break;
            }

            time_nanosleep(0, 1000000);

            if ($this->timeout > 0 && ((microtime(true) * 1000) - $startTime) > $this->timeout) {
                break;
            }
        }

        foreach ($coroutinesWaiting as $coroutineWaiting) {
            $responsePath = ($coroutinePath . sha1($coroutineWaiting['coroutine']['hash']));
            if (file_exists($responsePath) === false) {
                foreach ($coroutinesWaiting as $coroutineWaiting) {
                    unlink($coroutinePath . $coroutineWaiting['coroutine']['hash']);
                }
                throw new Timeout('Coroutine exceeds maximum execution time of ' . $this->timeout . 'ms.');
            }
            $data = unserialize(file_get_contents($responsePath));
            if ($data === false) {
                $results[] = null;
            } else {
                if ($echoOutput === true && $data['buffer'] !== '') {
                    echo $data['buffer'];
                }
                $results[] = unserialize($data['response']);
            }
            unlink($coroutinePath . $coroutineWaiting['coroutine']['hash']);
            unlink($responsePath);
        }

        return $results;
    }

    private static function __getCaller()
    {
        $lastFunc = null;

        $debugBacktrace = debug_backtrace();
        foreach ($debugBacktrace as $_debugBacktrace) {
            if ($_debugBacktrace['class'] === 'Flames\\Coroutine') {
                continue;
            }
            $lastFunc = $_debugBacktrace['class'];
            break;
        }

        return $lastFunc;
    }
}