<?php

namespace Flames;

use Exception;
use Flames\Collection\Arr;

/**
 * Description for the class
 * @property string|null $uid
 * @property string|null $tag
 * @property Arr $attributes
 * @property Arr $classes
 * @property string|null $value
 * @property string|null $checked
 * @property Element\Event|null $event
 */
class Element
{
    protected string|null $uid = null;
    protected string|null $tag = null;
    protected Arr $attributes;
    protected Arr $classes;
    protected string|null $value = null;
    protected string|null $checked = null;
    protected Element\Event|null $event = null;

    public function __construct(string $uid = null)
    {
        if (Kernel::MODULE === 'SERVER') {
            throw new Exception('Method only works on client.');
        }

        $this->attributes = Arr();
        $this->classes    = Arr();

        if ($uid !== null) {
            $this->uid = $uid;
            $this->sync();
        }
    }

    public function __get(string $key) : mixed
    {
        $this->sync();

        $key = strtolower($key);
        if ($key === 'uid') {
            return $this->uid;
        }
        elseif ($key === 'tag') {
            return $this->tag;
        }
        elseif ($key === 'attributes') {
            return $this->attributes;
        }
        elseif ($key === 'classes') {
            return $this->classes;
        }
        elseif ($key === 'value') {
            return $this->value;
        }
        elseif ($key === 'checked') {
            return $this->checked;
        }
        elseif ($key === 'event') {
            if ($this->event === null) {
                $this->event = new Element\Event($this->uid);
            }
            return $this->event;
        }

        return null;
    }

    public function hasClass(string $class) : bool
    {
        $class = strtolower($class);
        $this->sync();
        return ($this->classes->contains($class));
    }

    public function addClass(string $class) : void
    {
        $class = strtolower($class);
        if ($this->hasClass($class) === true) {
            return;
        }

        $classes = '';
        foreach ($this->classes as $_class) {
            $classes .= ($_class . ' ');
        }
        $classes .= $class;

        $this->execFunc("className =  '" . $classes . "'");
        $this->sync();
    }

    public function removeClass(string $class) : void
    {
        $class = strtolower($class);
        if ($this->hasClass($class) === false) {
            return;
        }

        $classes = '';
        foreach ($this->classes as $_class) {
            if ($_class === $class) {
                continue;
            }
            $classes .= ($_class . ' ');
        }
        if ($classes !== '') {
            $classes = substr($classes, 0, -1);
        }

        $this->execFunc("className =  '" . $classes . "'");
        $this->sync();
    }

    public function toogleClass(string $class) : void
    {
        $class = strtolower($class);
        if ($this->hasClass($class) === true) {
            $this->removeClass($class);
            return;
        }

        $this->addClass($class);
    }

    public function hasAttribute(string $attribute) : bool
    {
        $attribute = strtolower($attribute);
        $this->sync();
        return ($this->attributes->containsKey($attribute));
    }

    public function getAttribute(string $attribute) : string|null
    {
        $attribute = strtolower($attribute);
        $this->sync();
        if ($this->attributes->containsKey($attribute)) {
            return $this->attributes[$attribute];
        }
        return null;
    }

    public function setAttribute(string $attribute, string $value) : void
    {
        $attribute = strtolower($attribute);
        $this->execFunc("setAttribute('" . $attribute . "', '" . $value . "')");
        $this->sync();
    }

    public function removeAttribute(string $attribute) : void
    {
        $attribute = strtolower($attribute);
        $this->execFunc("removeAttribute('" . $attribute . "')");
        $this->sync();
    }

    public function destroy()
    {
        $this->execFunc("remove()");
        $this->uid        = null;
        $this->tag        = null;
        $this->attributes = Arr();
        $this->classes    = Arr();
        $this->value      = null;
        $this->checked    = null;
    }

    public function sync()
    {
        $data = JS::eval("
            (function() {
                var element = document.querySelector('[' + Flames.Internal.char + 'uid=\"" . $this->uid . "\"]');
                if (element !== null) {
                    var data = [];
                    data.tag = element.tagName.toLowerCase();
                    data.attributes = {};
                    var attributes = element.attributes;
                    for (var i = 0; i < attributes.length; i++) {
                        if (attributes[i].name === 'class' || attributes[i].name === Flames.Internal.char + 'uid') {
                            continue;
                        }
                        data.attributes[attributes[i].name] = attributes[i].value;
                    }
                    data.classes = element.className.toLowerCase().split(' ');
                    data.value = element.value;
                    if (data.value === undefined) {
                        data.value = null;
                    }
                    data.checked = element.checked;
                    if (data.checked === undefined) {
                        data.checked = null;
                    }
                    return data;
                }
            })();
        ");

        if (isset($data)) {
            $this->tag        = $data->tag;
            $this->attributes = Arr((array)$data->attributes);
            $this->classes    = Arr((array)$data->classes);
            $this->value      = $data->value;
            $this->checked    = $data->checked;
        }
    }

    protected function execFunc(string $code) : void
    {
        $data = JS::eval("
            (function() {
                var element = document.querySelector('[Flames.Internal.char + 'uid'=\"" . $this->uid . "\"]');
                if (element !== null) {
                    element." . $code . ";
                }
            })();
        ");
    }

    public static function query(string $query) : Element|null
    {
        if (Kernel::MODULE === 'SERVER') {
            throw new Exception('Method only works on client.');
        }

        $uid = JS::eval("
            (function() {
                var element = document.querySelector('" . $query . "');
                if (element !== null) {
                    if (element.getAttribute(Flames.Internal.char + 'uid') === null) {
                        Flames.Internal.uid++;
                        element.setAttribute(Flames.Internal.char + 'uid', Flames.Internal.generateUid(Flames.Internal.uid));
                    }
                    
                    return element.getAttribute(Flames.Internal.char + 'uid');
                }
            })();
        ");

        if (isset($uid)) {
            return new Element($uid);
        }
        else {
            return null;
        }
    }

    public static function queryAll(string $query) : Arr|null
    {
        if (Kernel::MODULE === 'SERVER') {
            throw new Exception('Method only works on client.');
        }
    }
}