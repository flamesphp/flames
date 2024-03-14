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

/**
 * @internal
 */
#[YieldReady]
class AutoEscapeNode extends Node
{
    public function __construct($value, Node $body, int $lineno, string $tag = 'autoescape')
    {
        parent::__construct(['body' => $body], ['value' => $value], $lineno, $tag);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->subcompile($this->getNode('body'));
    }
}
