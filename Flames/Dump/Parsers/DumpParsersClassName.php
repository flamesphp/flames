<?php

namespace Flames\Dump\Parsers;

use Exception;
use Flames\Dump\Inc\DumpHelper;
use Flames\Dump\Parsers\DumpParserInterface;
use Flames\Dump\Dump;
use ReflectionClass;
use Throwable;

/**
 * @internal
 */
class DumpParsersClassName implements DumpParserInterface
{
    public function replacesAllOtherParsers()
    {
        return false;
    }

    public function parse(&$variable, $varData)
    {
        if (
            Dump::enabled() === Dump::MODE_TEXT_ONLY
            || !DumpHelper::php53orLater()
            || empty($variable)
            || !is_string($variable)
            || strlen($variable) < 3
        ) {
            return false;
        }

        try {
            if (!@class_exists($variable)) {
                return false;
            }
        } catch (Throwable $t) {
            return false;
        } catch (Exception $e) {
            return false;
        }

        $reflector = new ReflectionClass($variable);
        if (!$reflector->isUserDefined()) {
            return false;
        }

        if (DumpHelper::isRichMode()) {
            $varData->addTabToView(
                $variable,
                'Existing class',
                DumpHelper::ideLink(
                    $reflector->getFileName(),
                    $reflector->getStartLine(),
                    $reflector->getShortName()
                )
            );
        } else {
            if (DumpHelper::isHtmlMode()) {
                $varData->extendedValue =
                    array(
                        'Existing class' => DumpHelper::ideLink(
                            $reflector->getFileName(),
                            $reflector->getStartLine(),
                            $reflector->getShortName()
                        )
                    );
            } else {
                $varData->extendedValue =
                    array('Existing class' => $reflector->getFileName() . ':' . $reflector->getStartLine());
            }
        }
    }
}
