<?php

namespace Flames\CLI;

use Flames\CLI\Command\Build\Assets;
use Flames\CLI\Command\Build\Project\StaticEx;
use Flames\CLI\Data;
use Flames\Collection\Arr;

/**
 * @internal
 */
class System
{
    protected static $commands = [
        'build:assets'         => Assets::class,
        'build:project:static' => StaticEx::class
    ];

    protected Arr $data;
    protected bool $debug;

    public function __construct(Arr $data = null, bool $debug = true)
    {
        $this->debug = $debug;

        if ($data === null) {
            $this->data = Data::getData();
            return;
        }

        $this->data = $data;
    }

    public function run() : bool
    {
        if ($this->data->command === null || isset(self::$commands[$this->data->command]) === false) {
            $this->dispatchHelper();
            return false;
        }

        $this->dispatchBase();

        if ($this->debug === true) {
            echo ' 
Initializing command ' . $this->data->command . '

';
        }

        $command = new self::$commands[$this->data->command]($this->data);
        $return = $command->run($this->debug);

        if ($this->debug === true) {
            echo "\n";
        }

        return $return;
    }

    protected function dispatchBase()
    {
        if ($this->debug === true) {
            $buffer = '
    ███████╗██╗      █████╗ ███╗   ███╗███████╗███████╗
    ██╔════╝██║     ██╔══██╗████╗ ████║██╔════╝██╔════╝
    █████╗  ██║     ███████║██╔████╔██║█████╗  ███████╗
    ██╔══╝  ██║     ██╔══██║██║╚██╔╝██║██╔══╝  ╚════██║
    ██║     ███████╗██║  ██║██║ ╚═╝ ██║███████╗███████║
    ╚═╝     ╚══════╝╚═╝  ╚═╝╚═╝     ╚═╝╚══════╝╚══════╝

    𝗖𝗿𝗲𝗮𝘁𝗲𝗱 𝗯𝘆 𝗚𝗮𝗯𝗿𝗶𝗲𝗹 \'𝗞𝗮𝘇𝘇\' 𝗠𝗼𝗿𝗴𝗮𝗱𝗼
    Github: https://github.com/flamesphp/flames
    Docs:   https://flamesphp.com   
    
    _________________________________________________________________ 
';
            echo $buffer;
        }
    }

    protected function dispatchHelper() : void
    {
        $this->dispatchBase();

        if ($this->debug === true) {
            $buffer = '
Available commands:
  * build:assets                      | build clientside assets, like controllers, events, components and resource
  * build:project:static              | build complete project as static html pages
  * build:project:static --cloudflare | build complete project as static html pages for CloudFlare Pages
  
';
            echo $buffer;
        }
    }
}