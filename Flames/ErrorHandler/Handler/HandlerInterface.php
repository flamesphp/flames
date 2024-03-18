<?php
/**
 * ErrorHandler - php errors for cool kids
 * @author Filipe Dobreira <http://github.com/filp>
 */

namespace Flames\ErrorHandler\Handler;

use Flames\ErrorHandler\Inspector\InspectorInterface;
use Flames\ErrorHandler\RunInterface;

/**
 * @internal
 */
interface HandlerInterface
{
    /**
     * @return int|null A handler may return nothing, or a Handler::HANDLE_* constant
     */
    public function handle();

    /**
     * @param  RunInterface  $run
     * @return void
     */
    public function setRun(RunInterface $run);

    /**
     * @param  \Throwable $exception
     * @return void
     */
    public function setException($exception);

    /**
     * @param  InspectorInterface $inspector
     * @return void
     */
    public function setInspector(InspectorInterface $inspector);
}
