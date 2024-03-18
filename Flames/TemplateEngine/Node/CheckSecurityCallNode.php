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

/**
 * @internal
 */
#[YieldReady]
class CheckSecurityCallNode extends Node
{
    public function compile(Compiler $compiler)
    {
        $compiler
            ->write("\$this->sandbox = \$this->env->getExtension('\TemplateEngine\Extension\SandboxExtension');\n")
            ->write("\$this->checkSecurity();\n")
        ;
    }
}
