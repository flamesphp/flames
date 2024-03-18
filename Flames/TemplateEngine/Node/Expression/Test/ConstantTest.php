<?php

/*
 * This file is part of TemplateEngine.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\TemplateEngine\Node\Expression\Test;

use Flames\TemplateEngine\Compiler;
use Flames\TemplateEngine\Node\Expression\TestExpression;

/**
 * @internal
 */
class ConstantTest extends TestExpression
{
    public function compile(Compiler $compiler): void
    {
        $compiler
            ->raw('(')
            ->subcompile($this->getNode('node'))
            ->raw(' === constant(')
        ;

        if ($this->getNode('arguments')->hasNode('1')) {
            $compiler
                ->raw('get_class(')
                ->subcompile($this->getNode('arguments')->getNode('1'))
                ->raw(')."::".')
            ;
        }

        $compiler
            ->subcompile($this->getNode('arguments')->getNode('0'))
            ->raw('))')
        ;
    }
}
