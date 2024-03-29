<?php
/**
 * ErrorHandler - php errors for cool kids
 * @author Filipe Dobreira <http://github.com/filp>
 */

namespace Flames\ErrorHandler\Inspector;

/**
 * @internal
 */
interface InspectorFactoryInterface
{
    /**
     * @param \Throwable $exception
     * @return InspectorInterface
     */
    public function create($exception);
}
