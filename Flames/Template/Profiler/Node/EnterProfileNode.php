<?php

/*
 * This file is part of Template.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\Template\Profiler\Node;

use Flames\Template\Attribute\YieldReady;
use Flames\Template\Compiler;
use Flames\Template\Node\Node;

/**
 * @internal
 */
#[YieldReady]
class EnterProfileNode extends Node
{
    public function __construct(string $extensionName, string $type, string $name, string $varName)
    {
        parent::__construct([], ['extension_name' => $extensionName, 'name' => $name, 'type' => $type, 'var_name' => $varName]);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->write(sprintf('$%s = $this->extensions[', $this->getAttribute('var_name')))
            ->repr($this->getAttribute('extension_name'))
            ->raw("];\n")
            ->write(sprintf('$%s->enter($%s = new \Template\Profiler\Profile($this->getTemplateName(), ', $this->getAttribute('var_name'), $this->getAttribute('var_name').'_prof'))
            ->repr($this->getAttribute('type'))
            ->raw(', ')
            ->repr($this->getAttribute('name'))
            ->raw("));\n\n")
        ;
    }
}
