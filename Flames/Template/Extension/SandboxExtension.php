<?php

/*
 * This file is part of Template.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\Template\Extension;

use Flames\Template\NodeVisitor\SandboxNodeVisitor;
use Flames\Template\Sandbox\SecurityNotAllowedMethodError;
use Flames\Template\Sandbox\SecurityNotAllowedPropertyError;
use Flames\Template\Sandbox\SecurityPolicyInterface;
use Flames\Template\Sandbox\SourcePolicyInterface;
use Flames\Template\Source;
use Flames\Template\TokenParser\SandboxTokenParser;

/**
 * @internal
 */
final class SandboxExtension extends AbstractExtension
{
    private $sandboxedGlobally;
    private $sandboxed;
    private $policy;
    private $sourcePolicy;

    public function __construct(SecurityPolicyInterface $policy, $sandboxed = false, ?SourcePolicyInterface $sourcePolicy = null)
    {
        $this->policy = $policy;
        $this->sandboxedGlobally = $sandboxed;
        $this->sourcePolicy = $sourcePolicy;
    }

    public function getTokenParsers(): array
    {
        return [new SandboxTokenParser()];
    }

    public function getNodeVisitors(): array
    {
        return [new SandboxNodeVisitor()];
    }

    public function enableSandbox(): void
    {
        $this->sandboxed = true;
    }

    public function disableSandbox(): void
    {
        $this->sandboxed = false;
    }

    public function isSandboxed(?Source $source = null): bool
    {
        return $this->sandboxedGlobally || $this->sandboxed || $this->isSourceSandboxed($source);
    }

    public function isSandboxedGlobally(): bool
    {
        return $this->sandboxedGlobally;
    }

    private function isSourceSandboxed(?Source $source): bool
    {
        if (null === $source || null === $this->sourcePolicy) {
            return false;
        }

        return $this->sourcePolicy->enableSandbox($source);
    }

    public function setSecurityPolicy(SecurityPolicyInterface $policy)
    {
        $this->policy = $policy;
    }

    public function getSecurityPolicy(): SecurityPolicyInterface
    {
        return $this->policy;
    }

    public function checkSecurity($tags, $filters, $functions, ?Source $source = null): void
    {
        if ($this->isSandboxed($source)) {
            $this->policy->checkSecurity($tags, $filters, $functions);
        }
    }

    public function checkMethodAllowed($obj, $method, int $lineno = -1, ?Source $source = null): void
    {
        if ($this->isSandboxed($source)) {
            try {
                $this->policy->checkMethodAllowed($obj, $method);
            } catch (SecurityNotAllowedMethodError $e) {
                $e->setSourceContext($source);
                $e->setTemplateLine($lineno);

                throw $e;
            }
        }
    }

    public function checkPropertyAllowed($obj, $property, int $lineno = -1, ?Source $source = null): void
    {
        if ($this->isSandboxed($source)) {
            try {
                $this->policy->checkPropertyAllowed($obj, $property);
            } catch (SecurityNotAllowedPropertyError $e) {
                $e->setSourceContext($source);
                $e->setTemplateLine($lineno);

                throw $e;
            }
        }
    }

    public function ensureToStringAllowed($obj, int $lineno = -1, ?Source $source = null)
    {
        if ($this->isSandboxed($source) && \is_object($obj) && method_exists($obj, '__toString')) {
            try {
                $this->policy->checkMethodAllowed($obj, '__toString');
            } catch (SecurityNotAllowedMethodError $e) {
                $e->setSourceContext($source);
                $e->setTemplateLine($lineno);

                throw $e;
            }
        }

        return $obj;
    }
}
