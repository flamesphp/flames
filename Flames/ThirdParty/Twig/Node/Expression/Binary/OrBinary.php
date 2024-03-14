<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 * (c) Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\ThirdParty\Twig\Node\Expression\Binary;

use Flames\ThirdParty\Twig\Compiler;
/**
 * @internal
 */
class OrBinary extends AbstractBinary
{
    public function operator(Compiler $compiler): Compiler
    {
        return $compiler->raw('||');
    }
}
