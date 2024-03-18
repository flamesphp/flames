<?php

// Twig fork: https://github.com/twigphp/Twig

namespace Flames\TemplateEngine;

use Flames\TemplateEngine\Node\Expression\TestExpression;

/**
 * @internal
 */
final class TemplateEngineTest
{
    private $name;
    private $callable;
    private $options;
    private $arguments = [];

    /**
     * @param callable|array{class-string, string}|null $callable A callable implementing the test. If null, you need to overwrite the "node_class" option to customize compilation.
     */
    public function __construct(string $name, $callable = null, array $options = [])
    {
        $this->name = $name;
        $this->callable = $callable;
        $this->options = array_merge([
            'is_variadic' => false,
            'node_class' => TestExpression::class,
            'deprecated' => false,
            'alternative' => null,
            'one_mandatory_argument' => false,
        ], $options);
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the callable to execute for this test.
     *
     * @return callable|array{class-string, string}|null
     */
    public function getCallable()
    {
        return $this->callable;
    }

    public function getNodeClass(): string
    {
        return $this->options['node_class'];
    }

    public function setArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function isVariadic(): bool
    {
        return (bool) $this->options['is_variadic'];
    }

    public function isDeprecated(): bool
    {
        return (bool) $this->options['deprecated'];
    }

    public function getDeprecatedVersion(): string
    {
        return \is_bool($this->options['deprecated']) ? '' : $this->options['deprecated'];
    }

    public function getAlternative(): ?string
    {
        return $this->options['alternative'];
    }

    public function hasOneMandatoryArgument(): bool
    {
        return (bool) $this->options['one_mandatory_argument'];
    }
}
