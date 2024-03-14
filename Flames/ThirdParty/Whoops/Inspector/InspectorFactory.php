<?php
/**
 * Whoops - php errors for cool kids
 * @author Filipe Dobreira <http://github.com/filp>
 */

namespace Flames\ThirdParty\Whoops\Inspector;

use Flames\ThirdParty\Whoops\Exception\Inspector;
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
