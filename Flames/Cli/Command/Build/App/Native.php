<?php

namespace Flames\Cli\Command\Build\App;

use Flames\Client\Os;
use Flames\Collection\Arr;
use Flames\Collection\Strings;
use Flames\Command;
use Flames\Controller\Response;
use Flames\Environment;
use Flames\Event;
use Flames\Header;
use Flames\Kernel;
use Flames\Kernel\Route;
use Flames\Process;
use Flames\Server\Shell;
use ZipArchive;

class Native
{
    protected bool $debug = false;
    protected string $buildPath;
    protected string $assetsPath;
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

        $this->buildPath = (ROOT_PATH . '.cache/build-native/');
        $this->assetsPath = (FLAMES_PATH . 'Cli/Command/Build/App/Native/Desktop/');

        if (is_dir($this->buildPath) === false) {
            $mask = umask(0);
            mkdir($this->buildPath, 0777, true);
            umask($mask);
        }

        $this->cleanBuild();

        if ($this->verifyDependencies() === false) { return false; }
        if ($this->mountNodeApp() === false) { return false; }
        if ($this->installNodeModules() === false) { return false; }
        if ($this->installElectron() === false) { return false; }
        if ($this->prepareApp() === false) { return false; }
        if ($this->buildApp() === false) { return false; }
        if ($this->packBuild() === false) { return false; }

        self::$isRunningBuild = false;
        return true;
    }

    protected function verifyDependencies(): bool
    {
        $process = new Shell('npm -v');
        $npmVersion = $process->getOutput();
        $output = (int)$npmVersion;

        if ($process->getCode() !== Shell\Code::CODE_SUCESS || $output === 0) {
            echo "NPM not found.\n";
            $this->reportNodeMissing();
            return false;
        }

        $process = new Shell('npx -v');
        $npxVersion = $process->getOutput();
        $output = (int)$npxVersion;

        if ($process->getCode() !== Shell\Code::CODE_SUCESS || $output === 0) {
            echo "NPX not found.\n";
            $this->reportNodeMissing();
            return false;
        }

        if (\Flames\Server\Os::isWindows() === false) {
            $process = new Shell('rpmbuild --version');
            $rpmVersion = Strings::split($process->getOutput(), ' ');
            $rpmVersion = $rpmVersion->last;
            $output = (int)$rpmVersion;

            if ($process->getCode() !== Shell\Code::CODE_SUCESS || $output === 0) {
                echo "RPM not found.\n";
                echo "Install RPM on Ubuntu using command: 'apt install rpm -y'.\n";
                echo "Install RPM on others Unix based OS: 'apt install rpmbuild -y'.\n";
                return false;
            }
        }

        echo "Dependencies checks: NPM version " . $npmVersion . " and NPX version " . $npxVersion . ".\n";
        return true;
    }

    protected function reportNodeMissing(): void
    {
        if (\Flames\Server\Os::isWindows() === true) {
            echo "Please install Node.JS using command: 'choco install nodejs -y'.\n";
            echo "Alternatively, you can download the installer from 'https://nodejs.org/en/download'.\n";
        } else {
            echo "Please install NodeJS using command: 'apt install nodejs -y'.\n";
        }
    }

    protected function mountNodeApp(): bool
    {
        $packageData = json_decode(file_get_contents($this->assetsPath . 'package.template.json'));

        $appTitle = Environment::get('APP_TITLE');
        if (!empty($appTitle)) { $packageData->name = $appTitle; }
        else { echo "Please set APP_TITLE environment variable in .env. Using default value.\n"; sleep(1); }

        $appVersion = Environment::get('APP_VERSION');
        if (!empty($appVersion)) { $packageData->version = $appVersion; }
        else { echo "Please set APP_VERSION environment variable in .env. Using default value.\n"; sleep(1); }

        $appAuthor = Environment::get('APP_AUTHOR');
        if (!empty($appAuthor)) { $packageData->author = $appAuthor; }
        else { echo "Please set APP_AUTHOR environment variable in .env. Using default value.\n"; sleep(1); }

        $appDescription = Environment::get('APP_DESCRIPTION');
        if (!empty($appDescription)) { $packageData->description = $appDescription; }
        else { echo "Please set APP_DESCRIPTION environment variable in .env. Using default value.\n"; sleep(1); }

        $packageDataMount = json_encode($packageData, JSON_PRETTY_PRINT);
        file_put_contents($this->buildPath . 'package.json', $packageDataMount);

        return true;
    }

    protected function installNodeModules(): bool
    {
        echo "Installing node modules. It could take up to several minutes...\n";

        $currentPath = getcwd();
        chdir($this->buildPath);

        $process = new Shell('npm install');
        chdir($currentPath);

        if ($process->getCode() !== Shell\Code::CODE_SUCESS) {
            echo "Error installing node modules.\n";
            return false;
        }

        return true;
    }

    protected function installElectron(): bool
    {
        echo "Installing Electron. It could take up to several minutes...\n";

        $currentPath = getcwd();
        chdir($this->buildPath);
        $process = new Shell('npm install --save-dev @electron-forge/cli');

        if ($process->getCode() !== Shell\Code::CODE_SUCESS) {
            chdir($currentPath);
            echo "Error installing Electron.\n";
            return false;
        }

        $process = new Shell('npx electron-forge import');
        chdir($currentPath);

        if ($process->getCode() !== Shell\Code::CODE_SUCESS) {
            echo "Error importing Electron.\n";
            return false;
        }

        return true;
    }

    protected function prepareApp(): bool
    {
        file_put_contents($this->buildPath . 'main.js', file_get_contents($this->assetsPath . 'main.js'));
        file_put_contents($this->buildPath . 'forge.config.js', file_get_contents($this->assetsPath . 'forge.config.js'));

        $assetsAppPath = ($this->buildPath . 'App/');
        if (is_dir($assetsAppPath) === false) {
            $mask = umask(0);
            mkdir($assetsAppPath, 0777, true);
            umask($mask);
        }

        file_put_contents($assetsAppPath . 'BrowserView.js', file_get_contents($this->assetsPath . 'App/BrowserView.js'));
        file_put_contents($assetsAppPath . 'BrowserWindow.js', file_get_contents($this->assetsPath . 'App/BrowserWindow.js'));
        file_put_contents($assetsAppPath . 'Register.js', file_get_contents($this->assetsPath . 'App/Register.js'));

        $appDomain = Environment::get('APP_DOMAIN');
        if (empty($appDomain)) {
            echo "Please set APP_DOMAIN environment variable in .env. Using default value.\n";
            sleep(1);
            $appDomain = 'flamesphp.com';
        }

        $appProtocol = Environment::get('APP_PROTOCOL');
        if (empty($appProtocol)) {
            echo "Please set APP_PROTOCOL environment variable in .env. Using default value.\n";
            sleep(1);
            $appProtocol = 'https';
        }

        $appEnv = file_get_contents($this->assetsPath . 'env.template.js');
        $appEnv = str_replace('{{ url }}', $appProtocol . '://' . $appDomain, $appEnv);
        file_put_contents($this->buildPath . 'env.js', $appEnv);

        return true;
    }

    protected function buildApp(): bool
    {
        echo "Building native app... It could take up to several minutes...\n";

        $currentPath = getcwd();
        chdir($this->buildPath);

        $process = new Shell('npm run make');
        chdir($currentPath);

        if ($process->getCode() !== Shell\Code::CODE_SUCESS) {
            echo "Error building app.\n";
            return false;
        }

        return true;
    }

    protected function packBuild(): bool
    {
        $buildZipPath = (APP_PATH . 'Client/Build/');
        if (is_dir($buildZipPath) === false) {
            $mask = umask(0);
            mkdir($buildZipPath, 0777, true);
            umask($mask);
        }

        $outputPath = ($this->buildPath . 'out/');

        if (\Flames\Server\Os::isWindows() === true) {
            $this->packZip($outputPath);
            return true;
        }

        $this->packBuildBundleUnix($outputPath, 'deb');
        $this->packBuildBundleUnix($outputPath, 'rpm');
        if ($this->packZip($outputPath) === false) { return false; }

        return true;
    }

    protected function packBuildBundleUnix(string $outputPath, string $type): void
    {
        $outputPath = ($outputPath . 'make/' . $type . '/x64/');

        if (is_dir($outputPath)) {
            $files = scandir($outputPath);
            $outputFile = null;
            foreach ($files as $file) {
                if (Strings::endsWith($file, '.' . $type) === true) {
                    $outputFile = $file;
                    break;
                }
            }

            if ($outputFile !== null) {
                $outputFilePath = ($outputPath . $outputFile);
                $fileName = ('build_' . $this->getBuildFilePrefix() . '.' . $type);
                copy($outputFilePath, (APP_PATH . 'Client/Build/' . $fileName));
            } else {
                echo ('No ' . $type . " build file found in output directory.\n");
            }
        }
    }

    protected function packZip(string $outputPath): bool
    {
        $outDirs = scandir($outputPath);
        $outputDir = null;
        foreach ($outDirs as $outDir) {
            if ($outDir !== '.' && $outDir !== '..' && $outDir !== 'make') {
                $outputDir = ($outputPath . $outDir . '/');
            }
        }

        if ($outputDir === null) {
            echo ("No output directory found.\n");
            return false;
        }

        @unlink($outputDir . '/LICENSES.chromium.html');
        @unlink($outputDir . '/Squirrel.exe');

        $this->buildZip($outputDir);

        // TODO: SFX installer
        return true;
    }

    protected function getBuildFilePrefix(): string
    {
        $pathName = '';
        $appName = Environment::get('APP_NAME');
        if (!empty($appName)) {
            $pathName = (strtolower($appName) . '_');
        }

        $pathName .= (new \DateTime())->format('Y_m_d_His');
        return $pathName;
    }

    protected function buildZip(string $buildPath): void
    {
        echo "Building zip file... It could take up to several minutes...\n";

        $buildZipPath = (APP_PATH . 'Client/Build/');
        if (is_dir($buildZipPath) === false) {
            $mask = umask(0);
            mkdir($buildZipPath, 0777, true);
            umask($mask);
        }

        $zipName = 'build_';
        $appName = Environment::get('APP_NAME');
        if (!empty($appName)) {
            $zipName .= (strtolower($appName) . '_');
        }

        $zipName .= (new \DateTime())->format('Y_m_d_His');
        $zipPath = ($buildZipPath . $zipName . '.zip');


        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $buildPathLen = strlen($buildPath);
        $buildFiles = $this->getDirContents($buildPath);
        foreach ($buildFiles as $buildFile) {
            if (is_dir($buildFile) === true) {
                continue;
            }

            $zipFilePath = substr($buildFile, $buildPathLen);
            $zip->addFile($buildFile, $zipFilePath);
        }
        $zip->close();
    }

    protected function cleanBuild() : void
    {
        $currentPath = getcwd();

        if (\Flames\Server\Os::isWindows() === true) {
            exec('rmdir ' . $this->buildPath . ' /S /Q ');
        } else {
            chdir($this->buildPath);
            exec('rm -rf *');
            chdir($currentPath);
        }

        $buildFiles = $this->getDirContents($this->buildPath);
        foreach ($buildFiles as $buildFile) {
            if (is_file($buildFile) === true) {
                @unlink($buildFile);
            }
        }
        foreach ($buildFiles as $buildFile) {
            if (is_dir($buildFile) === true) {
                @rmdir($buildFile);
            }
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
}