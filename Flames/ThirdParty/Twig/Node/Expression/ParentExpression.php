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

namespace Flames\ThirdParty\Twig\Node\Expression;

use Flames\ThirdParty\Twig\Compiler;

/**
 * @internal
 */
class ParentExpression extends AbstractExpression
{
    public function __construct(string $name, int $lineno, ?string $tag = null)
    {
        parent::__construct([], ['output' => false, 'name' => $name], $lineno, $tag);
    }

    public function compile(Compiler $compiler): void
    {
        if ($this->getAttribute('output')) {
            $compiler
                ->addDebugInfo($this)
                ->write('yield from $this->yieldParentBlock(')
                ->string($this->getAttribute('name'))
                ->raw(", \$context, \$blocks);\n")
            ;
        } else {
            $compiler
                ->raw('$this->renderParentBlock(')
                ->string($this->getAttribute('name'))
                ->raw(', $context, $blocks)')
            ;
        }
    }
}
