<?php

namespace Themosis\ComposerExcludeFiles;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;

class ExcludeFilesPlugin implements PluginInterface, EventSubscriberInterface
{
    private Composer $composer;
    private IOInterface $io;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;

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
             * @var PackageInterface $package
             * @var string $path
             */
            list($package, $path) = $item;

            $autoload = $package->getAutoload();

            dump($autoload);
        }
    }

    protected function getExcludedFiles(array $packageMap): array
    {
        $exclusionList = [];

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
                    $exclusionList[] = trim($packageName, '\/').'/'.trim($relativeFilePath, '\/');
                }
            }
        }

        return array_unique($exclusionList);
    }
}