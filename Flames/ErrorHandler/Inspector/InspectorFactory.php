<?php
/**
 * ErrorHandler - php errors for cool kids
 * @author Filipe Dobreira <http://github.com/filp>
 */

namespace Flames\ErrorHandler\Inspector;

use Flames\ErrorHandler\Exception\Inspector;

/**
 * @internal
 */
class InspectorFactory implements InspectorFactoryInterface
{
    /**
     * @param \Throwable $exception
     * @return InspectorInterface
     */
    public function create($exception)
    {
        return new Inspector($exception, $this);
    }
}
