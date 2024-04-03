<?php

namespace Flames\CLI\Command\Build\Project;

use Flames\Collection\Arr;
use Flames\Command;
use Flames\Controller\Response;
use Flames\Environment;
use Flames\Event;
use Flames\Header;
use Flames\Kernel;
use Flames\Kernel\Route;
use ZipArchive;

/**
 * @internal
 */
class StaticEx
{
    protected bool $debug = false;
    protected string $buildPath;
    protected Arr|null $inputs;

    protected static bool $isRunningBuild = false;

    public function run(bool $debug = false) : bool
    {
        // Stack overflow protection
        if (self::$isRunningBuild === true) {
            return false;
        }
        self::$isRunningBuild = true;

        $this->debug = $debug;

        $this->buildPath = (ROOT_PATH . '.cache/build/');
        if (is_dir($this->buildPath) === false) {
            mkdir($this->buildPath, 0777, true);
        }

        $this->copyPublic();
        $this->saveInputs();

        $router = Kernel::getDefaultRouter();
        $metadatas = $router->getMetadata();

        foreach ($metadatas as $metadata) {
            if (str_contains($metadata->methods, 'GET') !== true) {
                continue;
            }

            $_SERVER['REQUEST_URI'] = $metadata->routeFormatted;
            $match = $router->getMatch();
            $responseData = self::getResponse($match);

            $this->saveResponse($metadata, $responseData);
        }

        Command::run('build:assets');

        $this->buildFlames();
        $this->buildKernel();
        $this->buildZip();
        $this->restoreInputs();

        self::$isRunningBuild = false;
        return true;
    }

    protected function buildZip()
    {
        $buildZipPath = (ROOT_PATH . 'App/Client/Build/');
        if (is_dir($buildZipPath) === false) {
            mkdir($buildZipPath, 0777, true);
        }

        $zipName = '';
        $appName = Environment::get('APPLICATION_NAME');
        if (!empty($appName)) {
            $zipName = (strtolower($appName) . '_');
        }

        $zipName .= (new \DateTime())->format('Y_m_d_His');
        $zipPath = ($buildZipPath . $zipName . '.zip');


        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $buildPathLen = strlen($this->buildPath);
        $buildFiles = $this->getDirContents($this->buildPath);
        foreach ($buildFiles as $buildFile) {
            if (is_dir($buildFile) === true) {
                continue;
            }

            $zipFilePath = substr($buildFile, $buildPathLen);
            $zip->addFile($buildFile, $zipFilePath);
        }
        $zip->close();
    }

    protected function buildFlames() : void
    {
        $buildStream  = fopen($this->buildPath . '.flames.js', 'w+');

        $clientPath = (ROOT_PATH . 'App/Client/Resource/client.js');
        if (file_exists($clientPath) === true) {
            $fileStream = fopen($clientPath, 'r');
            while(!feof($fileStream)) {
                $buffer = fgets($fileStream, 128000); // 128 kb
                fputs($buildStream, $buffer);
            }
            fclose($fileStream);
        }

        $fileStream = fopen(ROOT_PATH . 'Flames/Kernel/Client/Engine/Flames.js', 'r');
        while(!feof($fileStream)) {
            $buffer = fgets($fileStream, 128000); // 128 kb
            fputs($buildStream, $buffer);
        }
        fclose($fileStream);
        fclose($buildStream);

        $flamesDir = ($this->buildPath . '.flames');
        if (is_dir($flamesDir) === false) {
            mkdir($flamesDir, 0777, true);
        }
    }

    protected function buildKernel() : void
    {
        $flamesKernelDir = ($this->buildPath . '.flames/kernel');
        if (is_dir($flamesKernelDir) === false) {
            mkdir($flamesKernelDir, 0777, true);
        }

        copy(ROOT_PATH . 'Flames/Kernel/Client/Engine/Flames/Kernel.mjs', $this->buildPath . '.flames/kernel.mjs');
        copy(ROOT_PATH . 'Flames/Kernel/Client/Engine/Flames/Kernel/Base.mjs', $this->buildPath . '.flames/kernel/base.mjs');
        copy(ROOT_PATH . 'Flames/Kernel/Client/Engine/Flames/Kernel/Web.mjs', $this->buildPath . '.flames/kernel/web.mjs');
        copy(ROOT_PATH . 'Flames/Kernel/Client/Engine/Flames/Kernel/Web.wasm', $this->buildPath . '.flames/kernel/web.wasm');
    }

    protected function copyPublic() : void
    {
        $publicPath = (ROOT_PATH . 'App/Client/Public/');
        if (is_dir($publicPath) === false) {
            return;
        }

        $publicPathLen = strlen($publicPath);
        $publicFiles = $this->getDirContents($publicPath);
        foreach ($publicFiles as $publicFile) {
            if (is_dir($publicFile) === true) {
                continue;
            }

            $buildFile = ($this->buildPath . substr($publicFile, $publicPathLen));
            $buildDir  = dirname($buildFile);

            if (is_dir($buildDir) === false) {
                mkdir($buildDir, 0777, true);
            }

            copy($publicFile, $buildFile);
        }
    }

    protected function getDirContents($dir, &$results = array()) {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            } else if ($value !== '.' && $value !== '..') {
                $this->getDirContents($path, $results);
                $results[] = $path;
            }
        }

        return $results;
    }


    protected function saveResponse($metadata, $responseData) : void
    {
        $url = $metadata->routeFormatted;
        if (str_ends_with($url, '/') === false) {
            $url .= '/';
        }

        $urls = null;
        if ($url !== '/') {
            $urls = [$url . 'index.html', substr($url, 0, -1) . '.html'];
        } else {
            $urls[] = '/index.html';
        }

        $urlCount = count($urls);
        if (str_contains(ROOT_PATH, '/') === true) {
            for ($i = 0; $i < $urlCount; $i++) {
                $urls[$i] = str_replace('\\', '/', substr($urls[$i], 1));
            }

        } else {
            for ($i = 0; $i < $urlCount; $i++) {
                $urls[$i] = str_replace('/', '\\', substr($urls[$i], 1));
            }
        }

        foreach ($urls as $url) {
            $path = $this->buildPath . $url;
            $dirPath = dirname($path);
            if (is_dir($dirPath) === false) {
                mkdir($dirPath, 0777, true);
            }

            $output = $responseData->output;
            if ($output === null) {
                $output = '';
            }
            file_put_contents($path, $output);
        }

        $this->saveHeader($metadata, $responseData, $urls);
    }

    protected function saveHeader($metadata, $responseData, $urls) : void {}

    public static function getResponse($routeData) : bool|Arr
    {
        Header::set('X-Powered-By', 'Flames');
        if (Event::dispatch('Initialize', 'onInitialize') === false) {
            return false;
        }

        $requestData = Route::mountRequestData($routeData);
        $requestDataAllow = Event::dispatch('Route', 'onMatch', $requestData);
        if ($requestDataAllow === false) {
            return false;
        }

        $controller = new $routeData->controller();
        $response = $controller->{$routeData->delegate}($requestData);

        if (($response instanceof Response) === false) {
            $response = new Response($response);
        }

        $output = $response->output;

        $_output = Event::dispatch('Output', 'onOutput', $requestData, $output);
        if ($_output !== null) {
            $output = (string)$_output;
        }

        Header::set('Code', $response->code);
        if ($response->headers !== null) {
            foreach ($response->headers as $key => $value) {
                Header::set($key, $value);
            }
        }

        $data = Arr([
            'header' => Header::getAll(),
            'output' => $output
        ]);

        Header::clear();
        return $data;
    }

    protected function saveInputs() : void
    {
        $this->inputs = Arr([
            'get'       => $_GET,
            'post'      => $_POST,
            'request'   => $_REQUEST,
            'cookie'    => $_COOKIE,
            'uri'       => @$_SERVER['REQUEST_URI'],
            'method'    => @$_SERVER['REQUEST_METHOD'],
            'header'    => Header::getAll(),
            'client_ip' => @$_SERVER['HTTP_CLIENT_IP'],
            'forwarded' => @$_SERVER['HTTP_X_FORWARDED_FOR'],
            'addr'      => @$_SERVER['REMOTE_ADDR'],
            'script'    => @$_SERVER['SCRIPT_FILENAME'],
            'svname'    => @$_SERVER['SERVER_NAME'],
            'svport'    => @$_SERVER['SERVER_PORT']

        ]);

        $_GET     = [];
        $_POST    = [];
        $_REQUEST = [];
        $_COOKIE  = [];

        $_SERVER['REQUEST_URI']          = '/';
        $_SERVER['REQUEST_METHOD']       = 'GET';
        $_SERVER['REMOTE_ADDR']          = null;
        $_SERVER['HTTP_CLIENT_IP']       = null;
        $_SERVER['HTTP_X_FORWARDED_FOR'] = null;
        $_SERVER['SCRIPT_FILENAME']      = null;
        $_SERVER['SERVER_NAME']          = 'localhost';
        $_SERVER['SERVER_PORT']          = '80';

        Header::clear();
    }

    protected function restoreInputs()
    {
        $_GET     = $this->inputs->get;
        $_POST    = $this->inputs->post;
        $_REQUEST = $this->inputs->request;
        $_COOKIE  = $this->inputs->cookie;

        $_SERVER['REQUEST_URI']          = $this->inputs->uri;
        $_SERVER['REQUEST_METHOD']       = $this->inputs->method;
        $_SERVER['HTTP_CLIENT_IP']       = $this->inputs->client_ip;
        $_SERVER['HTTP_X_FORWARDED_FOR'] = $this->inputs->forwarded;
        $_SERVER['REMOTE_ADDR']          = $this->inputs->addr;
        $_SERVER['SCRIPT_FILENAME']      = $this->inputs->script;
        $_SERVER['SERVER_NAME']          = $this->inputs->svname;
        $_SERVER['SERVER_PORT']          = $this->inputs->svport;

        Header::clear();
        foreach ($this->inputs->header as $key => $value) {
            Header::set($key, $value);
        }
    }
}