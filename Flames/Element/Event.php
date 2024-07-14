<?php

namespace Flames\Element;

use Flames\Element;
use Flames\Event\Element\Click;
use Flames\Event\Element\Change;
use Flames\Event\Element\Input;
use Flames\JS;

/**
 * @internal
 */
class Event
{
    protected string|null $uid = null;

    protected static array $delegates = [];

    public function __construct(string $uid)
    {
        $this->uid = $uid;
    }

    public function click($delegate)
    {
        self::$delegates[] = $delegate;
        $delegateId = (count(self::$delegates) -1);

        Js::eval("
            (function() {
                var element = document.querySelector('[' + Flames.Internal.char + 'uid=\"" . $this->uid . "\"]');
                if (element !== null) {
                    element.addEventListener('click', function(event) {
                        if (element.tagName === 'A') {
                            event.preventDefault();
                        }

                        var id = $delegateId;
                        var uid = '$this->uid';
                        window.PHP.eval('<?php \\\\Flames\\\\Element\\\\Event::onClick(' + id + ',\'' + uid + '\'); ?>');
                    });
                }
            })();
        ");
    }

    public static function onClick(string $id, string $uid)
    {
        self::$delegates[$id](new Click(new Element($uid)));
    }

    public function change($delegate)
    {
        self::$delegates[] = $delegate;
        $delegateId = (count(self::$delegates) -1);

        Js::eval("
            (function() {
                var element = document.querySelector('[' + Flames.Internal.char + 'uid=\"" . $this->uid . "\"]');
                if (element !== null) {
                    element.addEventListener('change', function() {
                        var id = $delegateId;
                        var uid = '$this->uid';
                        window.PHP.eval('<?php \\\\Flames\\\\Element\\\\Event::onChange(' + id + ',\'' + uid + '\'); ?>');
                    });
                }
            })();
        ");
    }

    public static function onChange(string $id, string $uid)
    {
        self::$delegates[$id](new Change(new Element($uid)));
    }

    public function input($delegate)
    {
        self::$delegates[] = $delegate;
        $delegateId = (count(self::$delegates) -1);

        Js::eval("
            (function() {
                var element = document.querySelector('[' + Flames.Internal.char + 'uid=\"" . $this->uid . "\"]');
                if (element !== null) {
                    element.addEventListener('input', function() {
                        var id = $delegateId;
                        var uid = '$this->uid';
                        window.PHP.eval('<?php \\\\Flames\\\\Element\\\\Event::onInput(' + id + ',\'' + uid + '\'); ?>');
                    });
                }
            })();
        ");
    }

    public static function onInput(string $id, string $uid)
    {
        self::$delegates[$id](new Input(new Element($uid)));
    }
}