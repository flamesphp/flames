<?php

/*
 * This file is part of TemplateEngine.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\TemplateEngine\Node\Expression\Filter;

use Flames\TemplateEngine\Compiler;
use Flames\TemplateEngine\Node\Expression\ConditionalExpression;
use Flames\TemplateEngine\Node\Expression\ConstantExpression;
use Flames\TemplateEngine\Node\Expression\FilterExpression;
use Flames\TemplateEngine\Node\Expression\GetAttrExpression;
use Flames\TemplateEngine\Node\Expression\NameExpression;
use Flames\TemplateEngine\Node\Expression\Test\DefinedTest;
use Flames\TemplateEngine\Node\Node;

/**
 * @internal
 */
class DefaultFilter extends FilterExpression
{
    public function __construct(Node $node, ConstantExpression $filterName, Node $arguments, int $lineno, ?string $tag = null)
    {
        $default = new FilterExpression($node, new ConstantExpression('default', $node->getTemplateLine()), $arguments, $node->getTemplateLine());

        if ('default' === $filterName->getAttribute('value') && ($node instanceof NameExpression || $node instanceof GetAttrExpression)) {
            $test = new DefinedTest(clone $node, 'defined', new Node(), $node->getTemplateLine());
            $false = \count($arguments) ? $arguments->getNode('0') : new ConstantExpression('', $node->getTemplateLine());

            $node = new ConditionalExpression($test, $default, $false, $node->getTemplateLine());
        } else {
            $node = $default;
        }

        parent::__construct($node, $filterName, $arguments, $lineno, $tag);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->subcompile($this->getNode('node'));
    }
}