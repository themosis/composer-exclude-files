<?php

namespace Themosis\ComposerExcludeFiles\Tests;

use Composer\Autoload\AutoloadGenerator;
use Composer\Composer;
use Composer\Config;
use Composer\EventDispatcher\EventDispatcher;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Util\Filesystem;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    const BASE_DIR_NAME = 'com.themosis.composer-exclude-files';

    protected Filesystem $filesystem;

    protected string $baseDirectory;

    protected Composer $composer;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();

        $this->baseDirectory = $baseDirectory = $this->getBaseDirectory();
        $vendorDirectory = $baseDirectory.'/vendor';

        $this->filesystem->ensureDirectoryExists($baseDirectory);

        chdir($baseDirectory);

        $config = $this->getConfiguration($baseDirectory, $vendorDirectory);

        $this->repository = $this->createStub(InstalledRepositoryInterface::class);

        $installationManager = $this->getInstallationManager($vendorDirectory);

        $composer = new Composer();
        $composer->setConfig($config);
        $composer->setInstallationManager($installationManager);
        $composer->setAutoloadGenerator(
            new AutoloadGenerator($this->createStub(EventDispatcher::class))
        );

        $this->composer = $composer;
    }

    protected function tearDown(): void
    {
        if (is_dir($this->baseDirectory)) {
            // Remove directory at test end.
            // $this->filesystem->removeDirectory($this->baseDirectory);
        }
    }

    private function getBaseDirectory(): string
    {
        return sys_get_temp_dir() . '/'.self::BASE_DIR_NAME;
    }

    private function getConfiguration(string $baseDirectory, string $vendorDirectory): Config
    {
        $config = new Config(false, $baseDirectory);

        $config->merge([
            'config' => [
                'vendor-dir' => $vendorDirectory,
            ],
        ]);

        return $config;
    }

    private function getInstallationManager(string $vendorDirectory): InstallationManager
    {
        $installationManager = $this->createStub(InstallationManager::class);
        $installationManager->method('getInstallPath')
            ->willReturnCallback(function (PackageInterface $package) use ($vendorDirectory) {
                $basePath = ($vendorDirectory ? $vendorDirectory . '/' : '') . $package->getPrettyName();
                $targetDir = $package->getTargetDir();

                return $basePath . ($targetDir ? '/'.$targetDir : '');
            });

        return $installationManager;
    }
}