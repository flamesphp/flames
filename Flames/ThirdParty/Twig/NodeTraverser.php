<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\ThirdParty\Twig;

use Flames\ThirdParty\Twig\Node\Node;
use Flames\ThirdParty\Twig\NodeVisitor\NodeVisitorInterface;

/**
 * @internal
 */
final class NodeTraverser
{
    private $env;
    private $visitors = [];

    /**
     * @param NodeVisitorInterface[] $visitors
     */
    public function __construct(Environment $env, array $visitors = [])
    {
        $this->env = $env;
        foreach ($visitors as $visitor) {
            $this->addVisitor($visitor);
        }
    }

    public function addVisitor(NodeVisitorInterface $visitor): void
    {
        $this->visitors[$visitor->getPriority()][] = $visitor;
    }

    /**
     * Traverses a node and calls the registered visitors.
     */
    public function traverse(Node $node): Node
    {
        ksort($this->visitors);
        foreach ($this->visitors as $visitors) {
            foreach ($visitors as $visitor) {
                $node = $this->traverseForVisitor($visitor, $node);
            }
        }

        return $node;
    }

    private function traverseForVisitor(NodeVisitorInterface $visitor, Node $node): ?Node
    {
        $node = $visitor->enterNode($node, $this->env);

        foreach ($node as $k => $n) {
            if (null !== $m = $this->traverseForVisitor($visitor, $n)) {
                if ($m !== $n) {
                    $node->setNode($k, $m);
                }
            } else {
                $node->removeNode($k);
            }
        }

        return $visitor->leaveNode($node, $this->env);
    }
}
