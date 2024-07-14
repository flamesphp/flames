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

namespace Flames\TemplateEngine\Node;

use Flames\TemplateEngine\Attribute\YieldReady;
use Flames\TemplateEngine\Compiler;
use Flames\TemplateEngine\Node\Expression\AbstractExpression;

/**
 * @internal
 */
#[YieldReady]
class PrintNode extends Node implements NodeOutputInterface
{
    public function __construct(AbstractExpression $expr, int $lineno, ?string $tag = null)
    {
        parent::__construct(['expr' => $expr], [], $lineno, $tag);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this);

        $compiler
            ->write('yield ')
            ->subcompile($this->getNode('expr'))
            ->raw(";\n")
        ;
    }
}
