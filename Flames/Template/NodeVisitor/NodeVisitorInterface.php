<?php

/*
 * This file is part of Template.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\Template\NodeVisitor;

use Flames\Template\Environment;
use Flames\Template\Node\Node;

/**
 * @internal
 */
interface NodeVisitorInterface
{
    /**
     * Called before child nodes are visited.
     *
     * @return Node The modified node
     */
    public function enterNode(Node $node, Environment $env): Node;

    /**
     * Called after child nodes are visited.
     *
     * @return Node|null The modified node or null if the node must be removed
     */
    public function leaveNode(Node $node, Environment $env): ?Node;

    /**
     * Returns the priority for this visitor.
     *
     * Priority should be between -10 and 10 (0 is the default).
     *
     * @return int The priority level
     */
    public function getPriority();
}
