<?php

/*
 * This file is part of TemplateEngine.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\TemplateEngine\Extension;

/**
 * @internal
 */
interface GlobalsInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getGlobals(): array;
}
