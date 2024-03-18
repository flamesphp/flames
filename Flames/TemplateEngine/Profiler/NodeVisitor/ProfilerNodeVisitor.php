<?php

/*
 * This file is part of TemplateEngine.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\TemplateEngine\Profiler\NodeVisitor;

use Flames\TemplateEngine\Environment;
use Flames\TemplateEngine\Node\BlockNode;
use Flames\TemplateEngine\Node\BodyNode;
use Flames\TemplateEngine\Node\MacroNode;
use Flames\TemplateEngine\Node\ModuleNode;
use Flames\TemplateEngine\Node\Node;
use Flames\TemplateEngine\NodeVisitor\NodeVisitorInterface;
use Flames\TemplateEngine\Profiler\Node\EnterProfileNode;
use Flames\TemplateEngine\Profiler\Node\LeaveProfileNode;
use Flames\TemplateEngine\Profiler\Profile;

/**
 * @internal
 */
final class ProfilerNodeVisitor implements NodeVisitorInterface
{
    private $extensionName;
    private $varName;

    public function __construct(string $extensionName)
    {
        $this->extensionName = $extensionName;
        $this->varName = sprintf('__internal_%s', hash(\PHP_VERSION_ID < 80100 ? 'sha256' : 'xxh128', $extensionName));
    }

    public function enterNode(Node $node, Environment $env): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, Environment $env): ?Node
    {
        if ($node instanceof ModuleNode) {
            $node->setNode('display_start', new Node([new EnterProfileNode($this->extensionName, Profile::TEMPLATE, $node->getTemplateName(), $this->varName), $node->getNode('display_start')]));
            $node->setNode('display_end', new Node([new LeaveProfileNode($this->varName), $node->getNode('display_end')]));
        } elseif ($node instanceof BlockNode) {
            $node->setNode('body', new BodyNode([
                new EnterProfileNode($this->extensionName, Profile::BLOCK, $node->getAttribute('name'), $this->varName),
                $node->getNode('body'),
                new LeaveProfileNode($this->varName),
            ]));
        } elseif ($node instanceof MacroNode) {
            $node->setNode('body', new BodyNode([
                new EnterProfileNode($this->extensionName, Profile::MACRO, $node->getAttribute('name'), $this->varName),
                $node->getNode('body'),
                new LeaveProfileNode($this->varName),
            ]));
        }

        return $node;
    }

    public function getPriority(): int
    {
        return 0;
    }
}
