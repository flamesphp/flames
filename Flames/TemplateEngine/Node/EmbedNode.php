<?php

/*
 * This file is part of TemplateEngine.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\TemplateEngine\Node;

use Flames\TemplateEngine\Attribute\YieldReady;
use Flames\TemplateEngine\Compiler;
use Flames\TemplateEngine\Node\Expression\AbstractExpression;
use Flames\TemplateEngine\Node\Expression\ConstantExpression;

/**
 * @internal
 */
#[YieldReady]
class EmbedNode extends IncludeNode
{
    // we don't inject the module to avoid node visitors to traverse it twice (as it will be already visited in the main module)
    public function __construct(string $name, int $index, ?AbstractExpression $variables, bool $only, bool $ignoreMissing, int $lineno, ?string $tag = null)
    {
        parent::__construct(new ConstantExpression('not_used', $lineno), $variables, $only, $ignoreMissing, $lineno, $tag);

        $this->setAttribute('name', $name);
        $this->setAttribute('index', $index);
    }

    protected function addGetTemplate(Compiler $compiler): void
    {
        $compiler
            ->write('$this->loadTemplate(')
            ->string($this->getAttribute('name'))
            ->raw(', ')
            ->repr($this->getTemplateName())
            ->raw(', ')
            ->repr($this->getTemplateLine())
            ->raw(', ')
            ->string($this->getAttribute('index'))
            ->raw(')')
        ;
    }
}
