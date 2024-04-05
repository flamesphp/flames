<?php

namespace Flames\CLI\Command;

use Flames\Command;
use Flames\Environment;

/**
 * @internal
 */
class Coroutine
{
    protected static bool $isCoroutineRunning = false;
    protected static Coroutine|null $currentCoroutine = null;

    protected const BASE_FOLDER = '.cache/coroutine/';

    protected bool $debug = false;

    protected array|null $coroutine = null;

    public function __construct($data)
    {
        $coroutinePath = (ROOT_PATH . self::BASE_FOLDER);
        $serialized = file_get_contents($coroutinePath . $data->argument[0]);
        $coroutine = unserialize($serialized);
        if ($coroutine !== false) {
            $this->coroutine = $coroutine;
        }
    }

    public function run(bool $debug = false) : bool
    {
        if ($this->coroutine === null) {
            return false;
        }

        self::$isCoroutineRunning = true;
        self::$currentCoroutine = $this;



        $class = new $this->coroutine['caller']();
        $unserializeArgs = [];
        foreach ($this->coroutine['args'] as $arg) {
            $unserializeArgs[] = unserialize($arg);
        }

        while (count($unserializeArgs) < 16) {
            $unserializeArgs[] = null;
        }

        $response = $class->{$this->coroutine['method']}($unserializeArgs[0], $unserializeArgs[1], $unserializeArgs[2], $unserializeArgs[3], $unserializeArgs[4], $unserializeArgs[5], $unserializeArgs[6], $unserializeArgs[7], $unserializeArgs[8], $unserializeArgs[9], $unserializeArgs[10], $unserializeArgs[11], $unserializeArgs[12], $unserializeArgs[13], $unserializeArgs[14], $unserializeArgs[15]);
        $buffer = ob_get_contents();
        @ob_end_clean();

        $coroutinePath = (ROOT_PATH . self::BASE_FOLDER);
        file_put_contents($coroutinePath . sha1($this->coroutine['hash']), serialize([
            'buffer' => $buffer,
            'response' => serialize($response)
        ]));

        self::$isCoroutineRunning = false;
        return true;
    }

    public static function isCoroutineRunning() : bool
    {
        return self::$isCoroutineRunning;
    }

    public static function errorHandler() : bool
    {
        $buffer = ob_get_contents();
        ob_end_clean();

        $coroutinePath = (ROOT_PATH . self::BASE_FOLDER);
        file_put_contents($coroutinePath . sha1(self::$currentCoroutine->coroutine['hash']), serialize([
            'buffer' => $buffer,
            'response' => serialize(null)
        ]));

        return true;
    }
}