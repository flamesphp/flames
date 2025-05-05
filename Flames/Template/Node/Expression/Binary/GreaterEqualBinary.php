<?php

/*
 * This file is part of Template.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\Template\Node\Expression\Binary;

use Flames\Template\Compiler;

/**
 * @internal
 */
class GreaterEqualBinary extends AbstractBinary
{
    public function compile(Compiler $compiler): void
    {
        if (\PHP_VERSION_ID >= 80000) {
            parent::compile($compiler);

            return;
        }

        $compiler
            ->raw('(0 <= CoreExtension::compare(')
            ->subcompile($this->getNode('left'))
            ->raw(', ')
            ->subcompile($this->getNode('right'))
            ->raw('))')
        ;
    }

    public function operator(Compiler $compiler): Compiler
    {
        return $compiler->raw('>=');
    }
}
