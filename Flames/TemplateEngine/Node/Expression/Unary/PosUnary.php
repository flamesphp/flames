<?php

/*
 * This file is part of TemplateEngine.
 *
 * (c) Fabien Potencier
 * (c) Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\TemplateEngine\Node\Expression\Unary;

use Flames\TemplateEngine\Compiler;

/**
 * @internal
 */
class PosUnary extends AbstractUnary
{
    public function operator(Compiler $compiler): Compiler
    {
        return $compiler->raw('+');
    }
}
