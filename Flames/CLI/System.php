<?php

namespace Flames\CLI;

use /**
 * Class Coroutine
 *
 * This class represents a command that starts a coroutine.
 *
 * @package Flames\CLI\Command
 */
    Flames\CLI\Command\Coroutine;
use /**
 * Class Install
 *
 * This class represents the install command for Flames CLI tool.
 * It is responsible for executing the installation process.
 */
    Flames\CLI\Command\Install;
use /**
 * Class KeyGenerate
 *
 * The KeyGenerate class provides a CLI command to generate a new encryption key.
 */
    Flames\CLI\Command\Key\Generate as KeyGenerate;
use /**
 * Class CryptographyKeyGenerate
 *
 * This class represents the CLI command for generating a cryptographic key.
 */
    Flames\CLI\Command\Cryptography\Key\Generate as CryptographyKeyGenerate;
use /**
 * Class Assets
 *
 * This class represents the command to build assets for a project.
 */
    Flames\CLI\Command\Build\Assets;
use /**
 * Class StaticEx
 *
 * This class is responsible for building the static files of a project.
 * It contains methods to generate CSS and JS files from the source code.
 */
    Flames\CLI\Command\Build\Project\StaticEx;
use /**
 * Class Data
 *
 * This class is responsible for handling and manipulating CLI data.
 */
    Flames\CLI\Data;
use /**
 * Class Arr
 *
 * A collection of static methods for performing various operations on arrays.
 */
    Flames\Collection\Arr;

/**
 * Class System
 *
 * Represents the system for executing commands.
 *
 * @internal
 */
final class System
{
    protected static $commands = [
        'install'                   => Install::class,
        'key:generate'              => KeyGenerate::class,
        'cryptography:key:generate' => CryptographyKeyGenerate::class,
        'build:assets'              => Assets::class,
        'build:project:static'      => StaticEx::class,
        'internal:coroutine'        => Coroutine::class
    ];

    protected Arr $data;
    protected bool $debug;

    /**
     * Constructor method for the class.
     *
     * @param Arr|null $data (optional) The data to be assigned to the object. If not provided, data will be fetched from Data::getData().
     * @param bool $debug (optional) Indicates whether debugging is enabled or not. Default is true.
     * @return void
     */
    public function __construct(Arr $data = null, bool $debug = true)
    {
        $this->debug = $debug;

        if ($data === null) {
            $this->data = Data::getData();
            return;
        }

        $this->data = $data;
    }

    /**
     * Run the command.
     *
     * @return bool Indicates if the command was executed successfully.
     */
    public function run() : bool
    {
        if ($this->data->command === null || isset(self::$commands[$this->data->command]) === false) {
            $this->dispatchHelper();
            return false;
        }

        if ($this->data->command === 'internal:coroutine') {
            $this->debug = false;
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

    /**
     * Dispatch the base command.
     *
     * Displays a logo and information about the command.
     * Only executed if the debug mode is enabled.
     */
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

    /**
     * Dispatch helper method for executing commands when the specific command is not found or is null.
     *
     * @return void
     */
    protected function dispatchHelper() : void
    {
        $this->dispatchBase();

        if ($this->debug === true) {
            $buffer = '
Available commands:
  * install                           | install
  * install --nokey                   | install without generate unique key
  * install --nocryptographykey       | install without generate cryptography unique key
  * install --noexample               | install without example project
  * key:generate                      | create or update project unique key
  * build:assets                      | build clientside assets, like controllers, events, components and resource
  * build:project:static              | build complete project as static html pages
  * build:project:static --cloudflare | build complete project as static html pages for CloudFlare Pages
';
            echo $buffer;
        }
    }
}