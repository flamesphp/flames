<?php

/*
 * This file is part of Template.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\Template\Util;

/**
 * @internal
 */
class TemplateDirIterator extends \IteratorIterator
{
    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return file_get_contents(parent::current());
    }

    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return (string) parent::key();
    }
}
