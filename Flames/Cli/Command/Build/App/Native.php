<?php

namespace Flames\Cli\Command\Build\App;

use Flames\Collection\Arr;
use Flames\Collection\Strings;
use Flames\Environment;
use Flames\Kernel\Tools\WinIco;
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

    protected ?string $platform = null;

    protected bool $installer = false;
    protected bool $run = false;

    public function __construct($data)
    {
        if ($data->option->contains('windows') === true) {
            $this->platform = 'windows';
        }
        if ($data->option->contains('linux') === true) {
            $this->platform = 'linux';
        }
        if ($data->option->contains('installer') === true) {
            $this->installer = true;
        }
        if ($data->option->contains('run') === true) {
            $this->run = true;
        }
    }

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

        $this->checkBuildPath();
        $this->cleanBuild();

        if ($this->verifyDependencies() === false) { return false; }

        if ($this->mountNodeApp() === false) { return false; }
        if ($this->installNodeModules() === false) { return false; }
        if ($this->installElectron() === false) { return false; }
        if ($this->prepareApp() === false) { return false; }
        if ($this->buildIcon() === false) { return false; }
        if ($this->buildApp() === false) { return false; }
        if ($this->packBuild() === false) { return false; }

        self::$isRunningBuild = false;
        return true;
    }

    protected function checkBuildPath(): void
    {
        if (is_dir($this->buildPath) === false) {
            $mask = umask(0);
            mkdir($this->buildPath, 0777, true);
            umask($mask);
        }
    }

    protected function verifyDependencies(): bool
    {
        $process = new Shell('npm -v');
        $npmVersion = $process->getOutput();
        $output = (int)$npmVersion;

        if ($process->getCode() !== Shell\Code::CODE_SUCESS || $output === 0) {
            $this->log("NPM not found.\n");
            $this->reportNodeMissing();
            return false;
        }

        $process = new Shell('npx -v');
        $npxVersion = $process->getOutput();
        $output = (int)$npxVersion;

        if ($process->getCode() !== Shell\Code::CODE_SUCESS || $output === 0) {
            $this->log("NPX not found.\n");
            $this->reportNodeMissing();
            return false;
        }

        if (\Flames\Server\Os::isWindows() === false) {
            $process = new Shell('rpmbuild --version');
            $rpmVersion = Strings::split($process->getOutput(), ' ');
            $rpmVersion = $rpmVersion->last;
            $output = (int)$rpmVersion;

            if ($process->getCode() !== Shell\Code::CODE_SUCESS || $output === 0) {
                $this->log("RPM not found.\n");
                $this->log("Install RPM on Ubuntu using command: 'apt install rpm -y'.\n");
                $this->log("Install RPM on others Unix based OS: 'apt install rpmbuild -y'.\n");
                return false;
            }
        }

        $this->log("Dependencies checks: NPM version " . $npmVersion . " and NPX version " . $npxVersion . ".\n");
        return true;
    }

    protected function reportNodeMissing(): void
    {
        if (\Flames\Server\Os::isWindows() === true) {
            $this->log("Please install Node.JS using command: 'choco install nodejs -y'.\n");
            $this->log("Alternatively, you can download the installer from 'https://nodejs.org/en/download'.\n");
        } else {
            $this->log("Please install NodeJS using command: 'apt install nodejs -y'.\n");
        }
    }

    protected function mountNodeApp(): bool
    {
        $packageData = json_decode(file_get_contents($this->assetsPath . 'package.template.json'));

        $appTitle = Environment::get('APP_TITLE');
        if (!empty($appTitle)) { $packageData->name = $appTitle; }
        else { $this->log("Please set APP_TITLE environment variable in .env. Using default value.\n"); sleep(1); }

        $appVersion = Environment::get('APP_VERSION');
        if (!empty($appVersion)) { $packageData->version = $appVersion; }
        else { $this->log("Please set APP_VERSION environment variable in .env. Using default value.\n"); sleep(1); }

        $appAuthor = Environment::get('APP_AUTHOR');
        if (!empty($appAuthor)) { $packageData->author = $appAuthor; }
        else { $this->log("Please set APP_AUTHOR environment variable in .env. Using default value.\n"); sleep(1); }

        $appDescription = Environment::get('APP_DESCRIPTION');
        if (!empty($appDescription)) { $packageData->description = $appDescription; }
        else { $this->log("Please set APP_DESCRIPTION environment variable in .env. Using default value.\n"); sleep(1); }

        $packageDataMount = json_encode($packageData, JSON_PRETTY_PRINT);
        file_put_contents($this->buildPath . 'package.json', $packageDataMount);

        return true;
    }

    protected function installNodeModules(): bool
    {
        $this->log("Installing node modules. It could take up to several minutes...\n");

        $currentPath = getcwd();
        $this->checkBuildPath();
        chdir($this->buildPath);

        $process = new Shell('npm install --force');
        chdir($currentPath);

        if ($process->getCode() !== Shell\Code::CODE_SUCESS) {
            $this->log("Error installing node modules.\n");
            return false;
        }

        return true;
    }

    protected function installElectron(): bool
    {
        $this->log("Installing Electron. It could take up to several minutes...\n");

        $currentPath = getcwd();
        chdir($this->buildPath);
        $process = new Shell('npm install --save-dev @electron-forge/cli');

        if ($process->getCode() !== Shell\Code::CODE_SUCESS) {
            chdir($currentPath);
            $this->log("Error installing Electron.\n");
            return false;
        }

        $process = new Shell('npx electron-forge import');
        chdir($currentPath);

        if ($process->getCode() !== Shell\Code::CODE_SUCESS) {
            $this->log("Error importing Electron.\n");
            return false;
        }

        return true;
    }

    protected function prepareApp(): bool
    {
        file_put_contents($this->buildPath . 'main.js', file_get_contents($this->assetsPath . 'main.js'));
        file_put_contents($this->buildPath . 'forge.config.js', file_get_contents($this->assetsPath . 'forge.config.js'));

        $assetsAppPath = ($this->buildPath . 'Kernel/');
        if (is_dir($assetsAppPath) === false) {
            $mask = umask(0);
            mkdir($assetsAppPath, 0777, true);
            umask($mask);
        }

        file_put_contents($assetsAppPath . 'BrowserView.js', file_get_contents($this->assetsPath . 'Kernel/BrowserView.js'));
        file_put_contents($assetsAppPath . 'BrowserWindow.js', file_get_contents($this->assetsPath . 'Kernel/BrowserWindow.js'));
        file_put_contents($assetsAppPath . 'Register.js', file_get_contents($this->assetsPath . 'Kernel/Register.js'));

        $appDomain = Environment::get('APP_DOMAIN');
        if (empty($appDomain)) {
            $this->log("Please set APP_DOMAIN environment variable in .env. Using default value.\n");
            sleep(1);
            $appDomain = 'flamesphp.com';
        }

        $appProtocol = Environment::get('APP_PROTOCOL');
        if (empty($appProtocol)) {
            $this->log("Please set APP_PROTOCOL environment variable in .env. Using default value.\n");
            sleep(1);
            $appProtocol = 'https';
        }

        $appEnv = file_get_contents($this->assetsPath . 'env.template.js');
        $appEnv = str_replace('{{ url }}', $appProtocol . '://' . $appDomain, $appEnv);
        file_put_contents($this->buildPath . 'env.js', $appEnv);

        return true;
    }

    protected function buildIcon(): bool
    {
        $buildResourcePath = ($this->buildPath . 'Resource/');
        if (is_dir($buildResourcePath) === false) {
            $mask = umask(0);
            mkdir($buildResourcePath, 0777, true);
            umask($mask);
        }

        $iconPath = (APP_PATH . 'Client/Resource/icon.png');
        if (file_exists($iconPath) === false) {
            $iconPath = (FLAMES_PATH . 'Kernel/Client/Engine/Flames.png');
            $this->log("App Icon not found, please put at 'App/Client/Resource/icon.png'. Using default value.\n");
            sleep(1);
        }
        copy($iconPath, ($buildResourcePath . 'icon.png'));

        $winIco = new WinIco($buildResourcePath . 'icon.png');
        $winIco->save($buildResourcePath . 'icon.ico');

        return true;
    }

    protected function buildApp(): bool
    {
        $this->log("Building native app... It could take up to several minutes...\n");

        $currentPath = getcwd();
        chdir($this->buildPath);

        $process = new Shell('npm run make');
        chdir($currentPath);

        if ($process->getCode() !== Shell\Code::CODE_SUCESS) {
            $this->log("Error building app.\n");
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
            $squirrelPath = ($outputPath . 'make/squirrel.windows/x64/');

            if (is_dir($squirrelPath)) {
                $files = scandir($squirrelPath);
                $outputFile = null;
                foreach ($files as $file) {
                    if (Strings::endsWith($file, '.nupkg') === true) {
                        $outputFile = $file;
                        break;
                    }
                }

                if ($outputFile !== null) {
                    $outputFilePath = ($squirrelPath . $outputFile);
                    $fileName = ('build_' . $this->getBuildFilePrefix() . '.nupkg');
                    copy($outputFilePath, (APP_PATH . 'Client/Build/' . $fileName));
                } else {
                    $this->log("No nupkg build file found in output directory.\n");
                }
            }

            $this->packZip($outputPath);

            if ($this->installer === true) {
                if ($this->buildInstaller($outputPath) === false) {
                    return false;
                }
            }

            if ($this->run === true) {
                $this->runBuild($outputPath);
            }
            return true;
        }

        $this->packBuildBundleUnix($outputPath, 'deb');
        $this->packBuildBundleUnix($outputPath, 'rpm');
        if ($this->packZip($outputPath) === false) { return false; }

        if ($this->run === true) {
            $this->runBuild($outputPath);
        }

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
                $this->log('No ' . $type . " build file found in output directory.\n");
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
            $this->log("No output directory found.\n");
            return false;
        }

        @unlink($outputDir . '/LICENSES.chromium.html');
        @unlink($outputDir . '/Squirrel.exe');

        $this->buildZip($outputDir);

        return true;
    }

    protected function buildInstaller(string $outputPath): bool
    {
        return true;
    }

    protected function runBuild(string $outputPath): void
    {
        $outDirs = scandir($outputPath);
        $outputDir = null;
        foreach ($outDirs as $outDir) {
            if ($outDir !== '.' && $outDir !== '..' && $outDir !== 'make') {
                $outputDir = ($outputPath . $outDir . '/');
            }
        }

        if ($outputDir === null) {
            $this->log("No output directory found. Can't run build.\n");
            return;
        }

        $files = scandir($outputDir);
        $exeFile = null;
        foreach ($files as $file) {
            if (Strings::endsWith($file, '.exe') === true) {
                $exeFile = $file;
                break;
            }
        }

        if ($exeFile === null) {
            $this->log("No executable file found. Can't run build.\n");
            return;
        }

        $exePath = ($outputDir . $exeFile);
        proc_open("start /b " . $exePath, [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
        ], $pipes);
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
        $this->log("Building zip file... It could take up to several minutes...\n");

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
            @exec('del /s /q "' . $this->buildPath . '"');
            sleep(1);
            $this->checkBuildPath();
        } else {
            chdir($this->buildPath);
            @exec('rm -rf *');
            chdir($currentPath);
        }

        if (is_dir($this->buildPath) === false) {
            return;
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

        $this->checkBuildPath();
    }

    protected function getDirContents($dir, &$results = array()) {
        if (!is_dir($dir)) {
            return [];
        }
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
    
    protected function log(string $message)
    {
        echo $message;
        @flush();
        @ob_flush();
    }
}