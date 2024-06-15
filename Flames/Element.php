<?php

namespace Flames;

use Exception;
use Flames\Collection\Arr;

/**
 * Represents an HTML element.
 *
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

    /**
     * Class Constructor.
     *
     * @param string|null $uid The unique identifier for the instance. Optional, defaults to null.
     *
     * @return void
     * @throws Exception If the method is called on the server module.
     *
     */
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

    /**
     * Magic getter method.
     *
     * @param string $key The key to retrieve the value for.
     *
     * @return mixed|null The value for the specified key, or null if the key does not exist.
     *
     * @throws Exception If the method is called on the server module.
     */
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

    /**
     * Checks if the element has a specific class.
     *
     * @param string $class The class to check.
     *
     * @return bool True if the instance has the class, false otherwise.
     *
     */
    public function hasClass(string $class) : bool
    {
        $class = strtolower($class);
        $this->sync();
        return ($this->classes->contains($class));
    }

    /**
     * Adds a class to the element.
     *
     * @param string $class The class to be added.
     *
     * @return void
     *
     */
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

    /**
     * Remove a class from the element.
     *
     * @param string $class The class name to be removed.
     *
     * @return void
     *
     */
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

    /**
     * Toggles a class on the element.
     *
     * @param string $class The class to be toggled.
     *
     * @return void
     */
    public function toogleClass(string $class) : void
    {
        $class = strtolower($class);
        if ($this->hasClass($class) === true) {
            $this->removeClass($class);
            return;
        }

        $this->addClass($class);
    }

    /**
     * Checks if the element has a specific attribute.
     *
     * @param string $attribute The attribute to check for.
     *
     * @return bool Returns true if the element has the specified attribute, otherwise false.
     *
     */
    public function hasAttribute(string $attribute) : bool
    {
        $attribute = strtolower($attribute);
        $this->sync();
        return ($this->attributes->containsKey($attribute));
    }

    /**
     * Get the value of a specific attribute.
     *
     * @param string $attribute The name of the attribute.
     *
     * @return string|null The value of the attribute, or null if it doesn't exist.
     *
     */
    public function getAttribute(string $attribute) : string|null
    {
        $attribute = strtolower($attribute);
        $this->sync();
        if ($this->attributes->containsKey($attribute)) {
            return $this->attributes[$attribute];
        }
        return null;
    }

    /**
     * Sets the value of an attribute.
     *
     * @param string $attribute The name of the attribute.
     * @param string $value The value to set for the attribute.
     *
     * @return void
     */
    public function setAttribute(string $attribute, string $value) : void
    {
        $attribute = strtolower($attribute);
        $this->execFunc("setAttribute('" . $attribute . "', '" . $value . "')");
        $this->sync();
    }

    /**
     * Removes an attribute from the element.
     *
     * @param string $attribute The name of the attribute to remove.
     *
     * @return void
     *
     */
    public function removeAttribute(string $attribute) : void
    {
        $attribute = strtolower($attribute);
        $this->execFunc("removeAttribute('" . $attribute . "')");
        $this->sync();
    }

    /**
     * Destroys the element.
     *
     * @return void
     */
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

    /**
     * Synchronizes the data between the JavaScript element and the PHP object.
     *
     * This method retrieves the data from the JavaScript element with the specified UID
     * and updates the corresponding properties in the PHP object.
     *
     * @return void
     * @throws Exception when JavaScript engine is not found.
     */
    public function sync()
    {
        $data = Js::eval("
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

    /**
     * Executes the specified JavaScript code on the element with the specified UID.
     *
     * This method uses the Js::eval() function to execute the JavaScript code on the
     * element with the specified UID. The JavaScript code should be a valid code snippet
     * that manipulates the element in some way.
     *
     * @param string $code The JavaScript code to execute.
     *
     * @return void
     * @throws Exception when JavaScript engine is not found.
     */
    protected function execFunc(string $code) : void
    {
        $data = Js::eval("
            (function() {
                var element = document.querySelector('[Flames.Internal.char + 'uid'=\"" . $this->uid . "\"]');
                if (element !== null) {
                    element." . $code . ";
                }
            })();
        ");
    }

    /**
     * Queries the DOM for an element matching the given query string.
     *
     * @param string $query The query string used to search for the element.
     * @return Element|null Returns an instance of Element if an element is found, otherwise returns null.
     * @throws Exception Throws an exception if the method is called on the server.
     */
    public static function query(string $query) : Element|null
    {
        if (Kernel::MODULE === 'SERVER') {
            throw new Exception('Method only works on client.');
        }

        $uid = Js::eval("
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

        // TODO: query all
        return null;
    }
}