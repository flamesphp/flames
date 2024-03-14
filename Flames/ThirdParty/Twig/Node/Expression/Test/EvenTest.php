<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\ThirdParty\Twig\Node\Expression\Test;

use Flames\ThirdParty\Twig\Compiler;
use Flames\ThirdParty\Twig\Node\Expression\TestExpression;

/**
 * @internal
 */
class EvenTest extends TestExpression
{
    public function compile(Compiler $compiler): void
    {
        $compiler
            ->raw('(')
            ->subcompile($this->getNode('node'))
            ->raw(' % 2 == 0')
            ->raw(')')
        ;
    }
}
