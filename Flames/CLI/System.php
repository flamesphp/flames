<?php

namespace Flames\CLI;

use Flames\CLI\Command\Build\Assets;
use Flames\CLI\Command\Build\Project\StaticEx;
use Flames\Collection\Arr;
use Flames\CLI\Data;

class System
{
    protected static $commands = [
        'build:assets'         => Assets::class,
        'build:project:static' => StaticEx::class
    ];

    protected Arr $data;

    public function __construct()
    {
        $this->data = Data::getData();
    }

    public function run() : void
    {
        if ($this->data->command === null || isset(self::$commands[$this->data->command]) === false) {
            $this->dispatchHelper();
            return;
        }

        $this->dispatchBase();

        echo ' 
Initializing command ' . $this->data->command . '

';
        $command = new self::$commands[$this->data->command]($this->data);
        $command->run();

        echo "\n";
    }

    protected function dispatchBase()
    {
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

    protected function dispatchHelper() : void
    {
        $this->dispatchBase();

        $buffer = '
Available commands:
  * build:assets                      | build clientside assets, like controllers, events, components and resource
  * build:project:static              | build complete project as static html pages
  * build:project:static --cloudflare | build complete project as static html pages for CloudFlare Pages
  
';
        echo $buffer;
    }
}