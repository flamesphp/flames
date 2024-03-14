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
class MatchesBinary extends AbstractBinary
{
    public function compile(Compiler $compiler): void
    {
        $compiler
            ->raw('CoreExtension::matches(')
            ->subcompile($this->getNode('right'))
            ->raw(', ')
            ->subcompile($this->getNode('left'))
            ->raw(')')
        ;
    }

    public function operator(Compiler $compiler): Compiler
    {
        return $compiler->raw('');
    }
}
