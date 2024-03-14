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
use Flames\ThirdParty\Twig\Error\SyntaxError;

/**
 * @internal
 */
#[YieldReady]
class MacroNode extends Node
{
    public const VARARGS_NAME = 'varargs';

    public function __construct(string $name, Node $body, Node $arguments, int $lineno, ?string $tag = null)
    {
        foreach ($arguments as $argumentName => $argument) {
            if (self::VARARGS_NAME === $argumentName) {
                throw new SyntaxError(sprintf('The argument "%s" in macro "%s" cannot be defined because the variable "%s" is reserved for arbitrary arguments.', self::VARARGS_NAME, $name, self::VARARGS_NAME), $argument->getTemplateLine(), $argument->getSourceContext());
            }
        }

        parent::__construct(['body' => $body, 'arguments' => $arguments], ['name' => $name], $lineno, $tag);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->addDebugInfo($this)
            ->write(sprintf('public function macro_%s(', $this->getAttribute('name')))
        ;

        $count = \count($this->getNode('arguments'));
        $pos = 0;
        foreach ($this->getNode('arguments') as $name => $default) {
            $compiler
                ->raw('$__'.$name.'__ = ')
                ->subcompile($default)
            ;

            if (++$pos < $count) {
                $compiler->raw(', ');
            }
        }

        if ($count) {
            $compiler->raw(', ');
        }

        $compiler
            ->raw('...$__varargs__')
            ->raw(")\n")
            ->write("{\n")
            ->indent()
            ->write("\$macros = \$this->macros;\n")
            ->write("\$context = \$this->env->mergeGlobals([\n")
            ->indent()
        ;

        foreach ($this->getNode('arguments') as $name => $default) {
            $compiler
                ->write('')
                ->string($name)
                ->raw(' => $__'.$name.'__')
                ->raw(",\n")
            ;
        }

        $compiler
            ->write('')
            ->string(self::VARARGS_NAME)
            ->raw(' => ')
            ->raw("\$__varargs__,\n")
            ->outdent()
            ->write("]);\n\n")
            ->write("\$blocks = [];\n\n")
            ->write('return ')
            ->subcompile(new CaptureNode($this->getNode('body'), $this->getNode('body')->lineno, $this->getNode('body')->tag))
            ->raw("\n")
            ->outdent()
            ->write("}\n\n")
        ;
    }
}
