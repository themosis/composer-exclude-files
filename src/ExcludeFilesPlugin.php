<?php

namespace Themosis\ComposerExcludeFiles;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;

class ExcludeFilesPlugin implements PluginInterface, EventSubscriberInterface
{
    private Composer $composer;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;

        $io->write("Composer Exclude Files plugin activated.");
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        // Nothing here...
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        // Nothing here...
    }

    public static function getSubscribedEvents(): array
    {
        return [
             ScriptEvents::PRE_AUTOLOAD_DUMP => 'onPreAutoloadDump',
        ];
    }

    public function onPreAutoloadDump(): void
    {
        $packages = $this->composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();

        $generator = $this->composer->getAutoloadGenerator();
        $packageMap = $generator->buildPackageMap(
            $this->composer->getInstallationManager(),
            $this->composer->getPackage(),
            $packages,
        );

        $this->filterPackagesAutoload($packageMap);
    }

    protected function filterPackagesAutoload(array $packageMap): void
    {
        $excludedFiles = $this->getExcludedFiles($packageMap);

        foreach ($packageMap as $item) {
            /**
             * @var Package $package
             * @var string $basepath
             */
            list($package, $basepath) = $item;

            $autoload = $package->getAutoload();

            if (! $this->hasFilesType($autoload)) {
                continue;
            }

            foreach ($autoload['files'] as $key => $relativePath) {
                $path = $basepath.'/'.$relativePath;

                if (in_array($path, $excludedFiles, true)) {
                    unset($autoload['files'][$key]);
                }
            }

            $package->setAutoload($autoload);
        }
    }

    protected function getExcludedFiles(array $packageMap): array
    {
        $exclusionList = [];

        $basepaths = $this->getAllBasePaths($packageMap);

        foreach ($packageMap as $item) {
            /**
             * @var PackageInterface $package
             */
            list($package) = $item;

            $extra = $package->getExtra();

            if (empty($extra['exclude-from-files'])) {
                continue;
            }

            foreach ($extra['exclude-from-files'] as $packageName => $files) {
                if (! is_string($packageName) || ! is_array($files) || empty($files)) {
                    continue;
                }

                foreach ($files as $relativeFilePath) {
                    if ($basepath = $this->getPackageBasePath($packageName, $basepaths)) {
                        $exclusionList[] = $basepath.'/'.trim($relativeFilePath, '\/');
                    }
                }
            }
        }

        return array_unique($exclusionList);
    }

    protected function hasFilesType(array $autoload): bool
    {
        return isset($autoload['files']);
    }

    protected function getAllBasePaths(array $packageMap): array
    {
        $paths = [];

        foreach ($packageMap as $item) {
            if (isset($item[1])) {
                $paths[] = $item[1];
            }
        }

        return $paths;
    }

    protected function getPackageBasePath(string $packageName, array $basepaths): ?string
    {
        if (empty($basepaths)) {
            return null;
        }

        $foundpaths = array_values(
            array_filter($basepaths, function (string $basepath) use ($packageName) {
                return strrpos($basepath, $packageName) !== false;
            })
        );

        if (empty($foundpaths)) {
            return null;
        }

        return array_shift($foundpaths);
    }
}
