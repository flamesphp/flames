<?php

/*
 * This file is part of TemplateEngine.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\TemplateEngine\Extension;

use Flames\TemplateEngine\NodeVisitor\NodeVisitorInterface;
use Flames\TemplateEngine\TemplateEngineFilter;
use Flames\TemplateEngine\TemplateEngineFunction;
use Flames\TemplateEngine\TemplateEngineTest;
use Flames\TemplateEngine\TokenParser\TokenParserInterface;

/**
 * Used by \TemplateEngine\Environment as a staging area.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @internal
 */
final class StagingExtension extends AbstractExtension
{
    private $functions = [];
    private $filters = [];
    private $visitors = [];
    private $tokenParsers = [];
    private $tests = [];

    public function addFunction(TemplateEngineFunction $function): void
    {
        if (isset($this->functions[$function->getName()])) {
            throw new \LogicException(sprintf('Function "%s" is already registered.', $function->getName()));
        }

        $this->functions[$function->getName()] = $function;
    }

    public function getFunctions(): array
    {
        return $this->functions;
    }

    public function addFilter(TemplateEngineFilter $filter): void
    {
        if (isset($this->filters[$filter->getName()])) {
            throw new \LogicException(sprintf('Filter "%s" is already registered.', $filter->getName()));
        }

        $this->filters[$filter->getName()] = $filter;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function addNodeVisitor(NodeVisitorInterface $visitor): void
    {
        $this->visitors[] = $visitor;
    }

    public function getNodeVisitors(): array
    {
        return $this->visitors;
    }

    public function addTokenParser(TokenParserInterface $parser): void
    {
        if (isset($this->tokenParsers[$parser->getTag()])) {
            throw new \LogicException(sprintf('Tag "%s" is already registered.', $parser->getTag()));
        }

        $this->tokenParsers[$parser->getTag()] = $parser;
    }

    public function getTokenParsers(): array
    {
        return $this->tokenParsers;
    }

    public function addTest(TemplateEngineTest $test): void
    {
        if (isset($this->tests[$test->getName()])) {
            throw new \LogicException(sprintf('Test "%s" is already registered.', $test->getName()));
        }

        $this->tests[$test->getName()] = $test;
    }

    public function getTests(): array
    {
        return $this->tests;
    }
}
