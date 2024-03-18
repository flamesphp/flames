<?php

/*
 * This file is part of TemplateEngine.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\TemplateEngine\NodeVisitor;

use Flames\TemplateEngine\Environment;
use Flames\TemplateEngine\Node\Expression\AssignNameExpression;
use Flames\TemplateEngine\Node\Expression\ConstantExpression;
use Flames\TemplateEngine\Node\Expression\GetAttrExpression;
use Flames\TemplateEngine\Node\Expression\MethodCallExpression;
use Flames\TemplateEngine\Node\Expression\NameExpression;
use Flames\TemplateEngine\Node\ImportNode;
use Flames\TemplateEngine\Node\ModuleNode;
use Flames\TemplateEngine\Node\Node;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @internal
 */
final class MacroAutoImportNodeVisitor implements NodeVisitorInterface
{
    private $inAModule = false;
    private $hasMacroCalls = false;

    public function enterNode(Node $node, Environment $env): Node
    {
        if ($node instanceof ModuleNode) {
            $this->inAModule = true;
            $this->hasMacroCalls = false;
        }

        return $node;
    }

    public function leaveNode(Node $node, Environment $env): Node
    {
        if ($node instanceof ModuleNode) {
            $this->inAModule = false;
            if ($this->hasMacroCalls) {
                $node->getNode('constructor_end')->setNode('_auto_macro_import', new ImportNode(new NameExpression('_self', 0), new AssignNameExpression('_self', 0), 0, 'import', true));
            }
        } elseif ($this->inAModule) {
            if (
                $node instanceof GetAttrExpression
                && $node->getNode('node') instanceof NameExpression
                && '_self' === $node->getNode('node')->getAttribute('name')
                && $node->getNode('attribute') instanceof ConstantExpression
            ) {
                $this->hasMacroCalls = true;

                $name = $node->getNode('attribute')->getAttribute('value');
                $node = new MethodCallExpression($node->getNode('node'), 'macro_'.$name, $node->getNode('arguments'), $node->getTemplateLine());
                $node->setAttribute('safe', true);
            }
        }

        return $node;
    }

    public function getPriority(): int
    {
        // we must be ran before auto-escaping
        return -10;
    }
}
