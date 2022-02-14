<?php

namespace Themosis\ComposerExcludeFiles\Tests;

use Composer\IO\BufferIO;
use Composer\Package\Link;
use Composer\Package\Package;
use Composer\Package\RootPackage;
use Composer\Script\ScriptEvents;
use Composer\Semver\Constraint\MatchAllConstraint;
use Themosis\ComposerExcludeFiles\ExcludeFilesPlugin;

/**
 * @covers \Themosis\ComposerExcludeFiles\ExcludeFilesPlugin
 */
class ExcludeFilesPluginTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_registered_on_pre_dump_autoload_event(): void
    {
        $this->assertArrayHasKey(ScriptEvents::PRE_AUTOLOAD_DUMP, ExcludeFilesPlugin::getSubscribedEvents());
    }

    /**
     * @test
     */
    public function it_can_exclude_files_from_autoloader_defined_in_root_package(): void
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

        $this->composer->setPackage($rootPackage);

        $packageA = new Package('fake/package-a', '1.0.0', '1.0.0');
        $packageA->setRequires([
            'fake/package-b' => new Link('fake/package-a', 'fake/package-b', new MatchAllConstraint())
        ]);
        $packageA->setAutoload([
            'files' => [
                'src/helpers.php',
            ]
        ]);

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

        $this->repository->method('getDevPackageNames')
            ->willReturn([]);

        $this->repository->method('getCanonicalPackages')
            ->willReturn([
                $packageA,
                $packageB,
            ]);

        $plugin = new ExcludeFilesPlugin();
        $plugin->activate($this->composer, new BufferIO());

        $plugin->onPreAutoloadDump();

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
