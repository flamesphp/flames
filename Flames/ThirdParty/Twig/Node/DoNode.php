<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\ThirdParty\Twig\Node;

use Flames\ThirdParty\Twig\Attribute\YieldReady;
use Flames\ThirdParty\Twig\Compiler;
use Flames\ThirdParty\Twig\Node\Expression\AbstractExpression;

/**
 * @internal
 */
#[YieldReady]
class DoNode extends Node
{
    public function __construct(AbstractExpression $expr, int $lineno, ?string $tag = null)
    {
        parent::__construct(['expr' => $expr], [], $lineno, $tag);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->addDebugInfo($this)
            ->write('')
            ->subcompile($this->getNode('expr'))
            ->raw(";\n")
        ;
    }
}
