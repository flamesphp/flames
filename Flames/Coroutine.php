<?php

namespace Flames;

use Flames\Collection\Arr;
use Flames\Cryptography\Hash;

class Coroutine
{
    protected const BASE_FOLDER = '.cache/coroutine/';

    protected string $caller;
    protected array $coroutines = [];

    public function __construct()
    {
        $this->caller = self::__getCaller();
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

        // TODO: get by pid (or by file in case of docker)
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
        }

        foreach ($coroutinesWaiting as $coroutineWaiting) {
            $responsePath = ($coroutinePath . sha1($coroutineWaiting['coroutine']['hash']));
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