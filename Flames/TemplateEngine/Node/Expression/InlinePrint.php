<?php

/*
 * This file is part of TemplateEngine.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\TemplateEngine\Node\Expression;

use Flames\TemplateEngine\Compiler;
use Flames\TemplateEngine\Node\Node;

/**
 * @internal
 */
final class InlinePrint extends AbstractExpression
{
    public function __construct(Node $node, int $lineno)
    {
        parent::__construct(['node' => $node], [], $lineno);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->raw('yield ')
            ->subcompile($this->getNode('node'))
        ;
    }
}
