<?php

/*
 * This file is part of TemplateEngine.
 *
 * (c) Fabien Potencier
 * (c) Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\TemplateEngine\Error;

/**
 * @internal
 */
class SyntaxError extends Error
{
    /**
     * Tweaks the error message to include suggestions.
     *
     * @param string $name  The original name of the item that does not exist
     * @param array  $items An array of possible items
     */
    public function addSuggestions(string $name, array $items): void
    {
        $alternatives = [];
        foreach ($items as $item) {
            $lev = levenshtein($name, $item);
            if ($lev <= \strlen($name) / 3 || str_contains($item, $name)) {
                $alternatives[$item] = $lev;
            }
        }

        if (!$alternatives) {
            return;
        }

        asort($alternatives);

        $this->appendMessage(sprintf(' Did you mean "%s"?', implode('", "', array_keys($alternatives))));
    }
}
