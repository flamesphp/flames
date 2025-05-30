<?php

/*
 * This file is part of Template.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\Template\Node;

use Flames\Template\Attribute\YieldReady;
use Flames\Template\Compiler;

/**
 * @internal
 */
#[YieldReady]
class WithNode extends Node
{
    public function __construct(Node $body, ?Node $variables, bool $only, int $lineno, ?string $tag = null)
    {
        $nodes = ['body' => $body];
        if (null !== $variables) {
            $nodes['variables'] = $variables;
        }

        parent::__construct($nodes, ['only' => $only], $lineno, $tag);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this);

        $parentContextName = $compiler->getVarName();

        $compiler->write(sprintf("\$%s = \$context;\n", $parentContextName));

        if ($this->hasNode('variables')) {
            $node = $this->getNode('variables');
            $varsName = $compiler->getVarName();
            $compiler
                ->write(sprintf('$%s = ', $varsName))
                ->subcompile($node)
                ->raw(";\n")
                ->write(sprintf("if (!is_iterable(\$%s)) {\n", $varsName))
                ->indent()
                ->write("throw new RuntimeError('Variables passed to the \"with\" tag must be a hash.', ")
                ->repr($node->getTemplateLine())
                ->raw(", \$this->getSourceContext());\n")
                ->outdent()
                ->write("}\n")
                ->write(sprintf("\$%s = CoreExtension::toArray(\$%s);\n", $varsName, $varsName))
            ;

            if ($this->getAttribute('only')) {
                $compiler->write("\$context = [];\n");
            }

            $compiler->write(sprintf("\$context = \$this->env->mergeGlobals(array_merge(\$context, \$%s));\n", $varsName));
        }

        $compiler
            ->subcompile($this->getNode('body'))
            ->write(sprintf("\$context = \$%s;\n", $parentContextName))
        ;
    }
}
