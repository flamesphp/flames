<?php

/*
 * This file is part of TemplateEngine.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\TemplateEngine\TokenParser;

use Flames\TemplateEngine\Parser;

/**
 * Base class for all token parsers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class AbstractTokenParser implements TokenParserInterface
{
    /**
     * @var Parser
     */
    protected $parser;

    public function setParser(Parser $parser): void
    {
        $this->parser = $parser;
    }
}
