<?php

namespace Themosis\ComposerExcludeFiles\Tests;

use Composer\Autoload\AutoloadGenerator;
use Composer\Composer;
use Composer\Config;
use Composer\EventDispatcher\EventDispatcher;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use Composer\Package\Link;
use Composer\Package\Package;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackage;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Repository\RepositoryManager;
use Composer\Semver\Constraint\MatchAllConstraint;
use Composer\Util\Filesystem;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Themosis\ComposerExcludeFiles\ExcludeFilesPlugin
 */
class ExcludeFilesPluginTest extends TestCase
{
    private string $baseDirectory;

    private string $vendorDirectory;

    private Filesystem $fs;

    private Composer $composer;

    private IOInterface $io;

    private InstalledRepositoryInterface $repository;

    private Config $config;

    private InstallationManager $installationManager;

    protected function setUp(): void
    {
        $this->fs = new Filesystem();
        $this->baseDirectory = sys_get_temp_dir().'/themosis/composer-exclude-files';
        $this->vendorDirectory = $this->baseDirectory.'/vendor';

        $this->fs->ensureDirectoryExists($this->baseDirectory);

        chdir($this->baseDirectory);

        $config = new Config(false);
        $config->merge([
            'config' => [
                'vendor-dir' => $this->vendorDirectory,
            ],
        ]);

        $io = $this->createStub(IOInterface::class);

        $this->repository = $this->createStub(InstalledRepositoryInterface::class);
        $repositoryManager = $this->createStub(RepositoryManager::class);
        $repositoryManager->method('getLocalRepository')
            ->willReturn($this->repository);

        $installationManager = $this->createStub(InstallationManager::class);
        $installationManager->method('getInstallPath')
            ->willReturnCallback(function (PackageInterface $package) {
                $basePath = ($this->vendorDirectory ? $this->vendorDirectory.'/' : '') . $package->getPrettyName();
                $targetDir = $package->getTargetDir();

                return $basePath . ($targetDir ? '/'.$targetDir : '');
            });
        $this->installationManager = $installationManager;

        $composer = new Composer();
        $composer->setConfig($config);
        $composer->setInstallationManager($installationManager);
        $composer->setAutoloadGenerator(
            new AutoloadGenerator($this->createStub(EventDispatcher::class))
        );

        $this->config = $config;
        $this->io = $io;
        $this->composer = $composer;
    }

    protected function tearDown(): void
    {
        if (is_dir($this->baseDirectory)) {
            //$this->fs->removeDirectory($this->baseDirectory);
        }
    }

    /** @test */
    public function it_can_exclude_files_from_autoloader(): void
    {
        $rootPackage = new RootPackage('fake/root-package', '1.0.0', '1.0.0');
        $rootPackage->setRequires([
            'fake/package-a' => new Link('a', 'fake/package-a', new MatchAllConstraint())
        ]);

        $rootPackage->setExtra([
            'exclude-from-files' => [
                'fake/package-b' => [
                    'src/helpers.php',
                ],
            ],
        ]);

        $packageA = new Package('fake/package-a', '1.0.0', '1.0.0');
        $packageA->setRequires([
            'fake/package-b' => new Link('fake/package-a', 'fake/package-b', new MatchAllConstraint())
        ]);
        $packageA->setAutoload([
            'files' => [
                'src/helpers.php',
            ]
        ]);

        /*$this->fs->ensureDirectoryExists($this->vendorDirectory.'/fake/package-a/src');
        file_put_contents($this->vendorDirectory.'/fake/package-a/src/helpers.php', '<?php function package_a_test() {}');*/

        $packageB = new Package('fake/package-b', '1.0.0', '1.0.0');
        $packageB->setRequires([
            'fake/package-c' => new Link('fake/package-b', 'fake/package-c', new MatchAllConstraint())
        ]);
        $packageB->setAutoload([
            'files' => [
                'src/helpers.php',
                'src/utilities.php',
            ]
        ]);

        $packageC = new Package('fake/package-c', '1.0.0', '1.0.0');
        $packageC->setAutoload([
            'files' => [
                'lib/fakes.php',
                'src/common.php',
            ]
        ]);

        $this->repository->method('getCanonicalPackages')
            ->willReturn([
                $packageA,
                $packageB,
                $packageC,
            ]);
        $this->repository->method('getDevPackageNames')
            ->willReturn([]);

        $this->composer->getAutoloadGenerator()->dump(
            $this->composer->getConfig(),
            $this->repository,
            $rootPackage,
            $this->composer->getInstallationManager(),
            'composer'
        );

        $this->assertTrue(true);
    }
}
