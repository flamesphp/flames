<?php

namespace Flames\Cli\Command;

/**
 * Class Coroutine
 *
 * This class represents a coroutine, which is a lightweight component
 * that can be executed concurrently with other coroutines. It provides
 * the functionality to run a serialized coroutine and handle errors.
 *
 * @internal
 */
final class Coroutine
{
    protected static bool $isCoroutineRunning = false;
    protected static Coroutine|null $currentCoroutine = null;

    protected const BASE_FOLDER = '.cache/coroutine/';

    protected bool $debug = false;

    protected array|null $coroutine = null;

    /**
     * Constructor to initialize the class object.
     *
     * @param mixed $data The input data object.
     * @return void
     */
    public function __construct($data)
    {
        $coroutinePath = (ROOT_PATH . self::BASE_FOLDER);
        $serialized = file_get_contents($coroutinePath . $data->argument[0]);
        $coroutine = unserialize($serialized);
        if ($coroutine !== false) {
            $this->coroutine = $coroutine;
        }
    }

    /**
     * Runs the coroutine.
     *
     * @param bool $debug (optional) Determines whether to enable debugging mode or not. Defaults to false.
     *
     * @return bool Returns true if the coroutine was executed successfully, false otherwise.
     */
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
        if (file_exists($coroutinePath . $this->coroutine['hash']) === true) {
            file_put_contents($coroutinePath . sha1($this->coroutine['hash']), serialize([
                'buffer'  => $buffer,
                'response' => serialize($response),
                'error'    => null
            ]));
        }

        self::$isCoroutineRunning = false;
        return true;
    }

    /**
     * Checks if a coroutine is currently running.
     *
     * @return bool Returns true if a coroutine is currently running, false otherwise.
     */
    public static function isCoroutineRunning() : bool
    {
        return self::$isCoroutineRunning;
    }

    /**
     * Handles errors that occur during the execution of a coroutine.
     *
     * @return bool Returns true if the error handling was executed successfully.
     */
    public static function errorHandler() : bool
    {
        $buffer = ob_get_contents();
        ob_end_clean();

        $coroutinePath = (ROOT_PATH . self::BASE_FOLDER);
        if (file_exists($coroutinePath . self::$currentCoroutine->coroutine['hash']) === true) {
            file_put_contents($coroutinePath . sha1(self::$currentCoroutine->coroutine['hash']), serialize([
                'buffer'   => $buffer,
                'response' => serialize(null),
                'error'    => json_encode(error_get_last())
            ]));
        }

        return true;
    }
}