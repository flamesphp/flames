<?php

namespace Flames\Cli\Command\Build\Assets;

use Flames\Cli;
use Flames\Cli\Command\Build\Assets\Data;
use Flames\Command;
use Flames\Environment;

/**
 * Class Assets
 *
 * This class is responsible for handling the build assets data for the Flames CLI command.
 *
 * @internal
 */
final class Automate
{
    protected bool $debug = false;
    protected ?array $ignorePaths = null;
    protected ?array $files = null;

    public function run(bool $debug = false) : bool
    {
        $this->debug = $debug;

        $ignorePath = Environment::get('AUTO_BUILD_IGNORE_PATHS');
        if ($ignorePath !== null) {
            $ignorePathSplit = explode(',', $ignorePath);
            if (is_array($ignorePathSplit)) {
                $this->ignorePaths = $ignorePathSplit;
            }
        }

        $this->getCurrentFileTimes();
        $this->fileChangedTimeHandler();

        return true;
    }

    protected function fileChangedTimeHandler()
    {
        if (count($this->files) === 0) {
            return;
        }

        if (Cli::isCli() === false) {
            try {
                ini_set('max_execution_time', 60);
            } catch (\Exception|\Error|\ErrorException $_) {}
            set_time_limit(60);
        }

        while (true) {
            usleep(500000);
            $fileModified = $this->getFileModified();
            if ($fileModified !== null) {
                $data = (object)[];
                $recompile = false;
                if ($fileModified['type']=== 'view') {
                    $data->file = str_replace('\\', '/', substr($fileModified['path'], strlen(APP_PATH . 'Client/View/')));
                    $data->type = 'view';
                } elseif ($fileModified['type'] === 'public') {
                    $data->file = str_replace('\\', '/', substr($fileModified['path'], strlen(APP_PATH . 'Client/Public/')));
                    $data->type = 'public';
                } elseif ($fileModified['type'] === 'client') {
                    $recompile = true;
                    $data->file = str_replace('\\', '/', substr($fileModified['path'], strlen(APP_PATH . 'Client/')));
                    $split = explode('/', $data->file);
                    $data->type = strtolower($split[0]);
                    $data->file = '';
                    $splitCount = count($split);
                    for ($i = 1; $i < $splitCount; $i++) {
                        $data->file .= ($split[$i] . '/');
                    }
                    if ($data->file !== '') {
                        $data->file = substr($data->file, 0, -1);
                    }
                } elseif ($fileModified['type'] === 'config') {
                    $recompile = true;
                    $data->file = str_replace('\\', '/', substr($fileModified['path'], strlen(ROOT_PATH)));
                    $data->type = 'config';
                }

                if (Cli::isCli() === true) {
                    if ($this->debug === true) {
                        echo ('File changed [' . $data->type . '] ' . $data->file . "\n");
                    }
                    if ($recompile === true) {
                        echo ("\n");
                        Command::run('build:assets');
                    }
                }
                else {
                    if ($recompile === true) {
                        Command::run('build:assets');
                    }
                    header('Content-Type: application/json');
                    echo json_encode($data);
                    exit;
                }
                exit;
            }
        }
    }

    protected function getFileModified()
    {
        foreach ($this->files as $file) {
            if (file_exists($file['path']) === false) {
                continue;
            }

            if (filemtime($file['path']) !== $file['changed']) {
                return $file;
            }
        }
    }

    protected function getCurrentFileTimes()
    {
        $this->files = [];

        $files = [
            'view' => $this->getViewDirContents(APP_PATH . 'Client/View/'),
            'public' => $this->getPublicDirContents(APP_PATH . 'Client/Public/'),
            'client' => $this->getClientDirContents(APP_PATH . 'Client/')
        ];

        $envFile = (ROOT_PATH . '.env');;
        if (file_exists($envFile)) {
            $this->files[] = [
                'path' => $envFile,
                'changed' => filemtime($envFile),
                'type' => 'config'
            ];
        }

        foreach ($files['view'] as $path) {
            $this->files[] = [
                'path' => $path,
                'changed' => filemtime($path),
                'type' => 'view'
            ];
        }

        foreach ($files['public'] as $path) {
            $this->files[] = [
                'path' => $path,
                'changed' => filemtime($path),
                'type' => 'public'
            ];
        }

        foreach ($files['client'] as $path) {
            $this->files[] = [
                'path' => $path,
                'changed' => filemtime($path),
                'type' => 'client'
            ];
        }
    }

    protected function getViewDirContents($dir, &$results = array())
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);

            if ($this->ignorePaths !== null && $this->isIgnorePath($path) === true) {
                continue;
            }

            if (!is_dir($path)) {
                $pathLower = strtolower($path);
                if (str_ends_with($pathLower, '.twig') === true) {
                    $results[] = $path;
                }
            } else if ($value !== '.' && $value !== '..') {
                $this->getViewDirContents($path, $results);
            }
        }

        return $results;
    }

    protected function getPublicDirContents($dir, &$results = array())
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);

            if ($this->ignorePaths !== null && $this->isIgnorePath($path) === true) {
                continue;
            }

            if (!is_dir($path)) {
                $pathLower = strtolower($path);
                if (str_ends_with($pathLower, '.css')  === true || str_ends_with($pathLower, '.scss') === true || str_ends_with($pathLower, '.js') === true || str_ends_with($pathLower, '.sass') === true) {
                    $results[] = $path;
                }
            } else if ($value !== '.' && $value !== '..') {
                $this->getPublicDirContents($path, $results);
            }
        }

        return $results;
    }

    protected function getClientDirContents($dir, &$results = array())
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);

            if (str_starts_with($path, APP_PATH . 'Client/Public/') === true ||
                str_starts_with($path, APP_PATH . 'Client/View/') === true ||
                str_starts_with($path, APP_PATH . 'Client/Resource/') === true) {
                continue;
            }

            if ($this->ignorePaths !== null && $this->isIgnorePath($path) === true) {
                continue;
            }

            if (!is_dir($path)) {
                $pathLower = strtolower($path);
                if (str_ends_with($pathLower, '.php') === true) {
                    $results[] = $path;
                }
            } else if ($value !== '.' && $value !== '..') {
                $this->getClientDirContents($path, $results);
            }
        }

        return $results;
    }

    protected function isIgnorePath(string $path) : bool
    {
        foreach ($this->ignorePaths as $ignorePath) {
            if (str_starts_with($path, ROOT_PATH . $ignorePath) === true) {
                return true;
            }
        }

        return false;
    }
}