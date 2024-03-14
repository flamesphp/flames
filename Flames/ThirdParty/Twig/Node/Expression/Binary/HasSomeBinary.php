<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\ThirdParty\Twig\Node\Expression\Binary;

use Flames\ThirdParty\Twig\Compiler;
/**
 * @internal
 */
class HasSomeBinary extends AbstractBinary
{
    public function compile(Compiler $compiler): void
    {
        $compiler
            ->raw('CoreExtension::arraySome($this->env, ')
            ->subcompile($this->getNode('left'))
            ->raw(', ')
            ->subcompile($this->getNode('right'))
            ->raw(')')
        ;
    }

    public function operator(Compiler $compiler): Compiler
    {
        return $compiler->raw('');
    }
}
