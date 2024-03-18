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

use Flames\TemplateEngine\ExpressionParser;
use Flames\TemplateEngine\Node\Expression\Binary\AbstractBinary;
use Flames\TemplateEngine\Node\Expression\Unary\AbstractUnary;
use Flames\TemplateEngine\NodeVisitor\NodeVisitorInterface;
use Flames\TemplateEngine\TemplateEngineFilter;
use Flames\TemplateEngine\TemplateEngineFunction;
use Flames\TemplateEngine\TemplateEngineTest;
use Flames\TemplateEngine\TokenParser\TokenParserInterface;

/**
 * @internal
 */
interface ExtensionInterface
{
    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return TokenParserInterface[]
     */
    public function getTokenParsers();

    /**
     * Returns the node visitor instances to add to the existing list.
     *
     * @return NodeVisitorInterface[]
     */
    public function getNodeVisitors();

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return TemplateEngineFilter[]
     */
    public function getFilters();

    /**
     * Returns a list of tests to add to the existing list.
     *
     * @return TemplateEngineTest[]
     */
    public function getTests();

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return TemplateEngineFunction[]
     */
    public function getFunctions();

    /**
     * Returns a list of operators to add to the existing list.
     *
     * @return array<array> First array of unary operators, second array of binary operators
     *
     * @psalm-return array{
     *     array<string, array{precedence: int, class: class-string<AbstractUnary>}>,
     *     array<string, array{precedence: int, class: class-string<AbstractBinary>, associativity: ExpressionParser::OPERATOR_*}>
     * }
     */
    public function getOperators();
}
