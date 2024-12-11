<?php

namespace Flames\Element;

use Flames\Collection\Strings;
use Flames\Element;
use Flames\Event\Element\Click;
use Flames\Event\Element\Change;
use Flames\Event\Element\Input;
use Flames\Js;

/**
 * @internal
 */
class Event
{
    private $element = null;

    public function __construct($element)
    {
        $this->element = $element;
    }

    public function click(\Closure $delegate)
    {
        $tag = Strings::toLower($this->element->tagName);
        $element = $this->element;

        $this->element->addEventListener('click', function ($event) use ($delegate, $tag, $element) {
            if ($tag === 'a') {
                $event->preventDefault();
            }

            $delegate(new Click(Element::fromNative($element)));
        });
    }

    public function change(\Closure $delegate)
    {
        $element = $this->element;
        $this->element->addEventListener('change', function ($event) use ($delegate, $element) {
            $delegate(new Change(Element::fromNative($element)));
        });
    }

    public function input(\Closure $delegate)
    {
        $element = $this->element;
        $this->element->addEventListener('input', function ($event) use ($delegate, $element) {
            $delegate(new Input(Element::fromNative($element)));
        });
    }
}