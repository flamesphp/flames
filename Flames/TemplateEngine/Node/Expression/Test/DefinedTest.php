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
use Flames\TemplateEngine\Error\SyntaxError;
use Flames\TemplateEngine\Node\Expression\ArrayExpression;
use Flames\TemplateEngine\Node\Expression\BlockReferenceExpression;
use Flames\TemplateEngine\Node\Expression\ConstantExpression;
use Flames\TemplateEngine\Node\Expression\FunctionExpression;
use Flames\TemplateEngine\Node\Expression\GetAttrExpression;
use Flames\TemplateEngine\Node\Expression\MethodCallExpression;
use Flames\TemplateEngine\Node\Expression\NameExpression;
use Flames\TemplateEngine\Node\Expression\TestExpression;
use Flames\TemplateEngine\Node\Node;

/**
 * @internal
 */
class DefinedTest extends TestExpression
{
    public function __construct(Node $node, string $name, ?Node $arguments, int $lineno)
    {
        if ($node instanceof NameExpression) {
            $node->setAttribute('is_defined_test', true);
        } elseif ($node instanceof GetAttrExpression) {
            $node->setAttribute('is_defined_test', true);
            $this->changeIgnoreStrictCheck($node);
        } elseif ($node instanceof BlockReferenceExpression) {
            $node->setAttribute('is_defined_test', true);
        } elseif ($node instanceof FunctionExpression && 'constant' === $node->getAttribute('name')) {
            $node->setAttribute('is_defined_test', true);
        } elseif ($node instanceof ConstantExpression || $node instanceof ArrayExpression) {
            $node = new ConstantExpression(true, $node->getTemplateLine());
        } elseif ($node instanceof MethodCallExpression) {
            $node->setAttribute('is_defined_test', true);
        } else {
            throw new SyntaxError('The "defined" test only works with simple variables.', $lineno);
        }

        parent::__construct($node, $name, $arguments, $lineno);
    }

    private function changeIgnoreStrictCheck(GetAttrExpression $node)
    {
        $node->setAttribute('optimizable', false);
        $node->setAttribute('ignore_strict_check', true);

        if ($node->getNode('node') instanceof GetAttrExpression) {
            $this->changeIgnoreStrictCheck($node->getNode('node'));
        }
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->subcompile($this->getNode('node'));
    }
}
