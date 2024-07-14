<?php

/*
 * This file is part of TemplateEngine.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\TemplateEngine\Node\Expression\Binary;

use Flames\TemplateEngine\Compiler;

/**
 * @internal
 */
class PowerBinary extends AbstractBinary
{
    public function operator(Compiler $compiler): Compiler
    {
        return $compiler->raw('**');
    }
}
