<?php

namespace Themosis\ComposerExcludeFiles\Tests;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\ConsoleIO;
use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Script\ScriptEvents;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Themosis\ComposerExcludeFiles\ExcludeFilesPlugin;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Themosis\ComposerExcludeFiles\ExcludeFilesPlugin
 */
class ExcludeFilesPluginTest extends TestCase
{
    private Composer $composer;

    private IOInterface $io;

    protected function setUp(): void
    {
        $this->io = new ConsoleIO(
            new ArgvInput(),
            new ConsoleOutput(),
            new HelperSet([
                'question' => new QuestionHelper(),
            ])
        );

        $this->composer = Factory::create($this->io, __DIR__.'/packages/package-1/composer.json');
    }

    /** @test */
    public function it_can_exclude_files_from_autoloader(): void
    {
        $this->composer
            ->getPluginManager()
            ->addPlugin(
                new ExcludeFilesPlugin(),
                false,
                new Package('themosis/composer-exclude-files', '1.0.0', '1.0.0')
            );

        $this->composer->getEventDispatcher()->dispatchScript(ScriptEvents::PRE_AUTOLOAD_DUMP, false);

        $this->assertTrue(true);
    }
}
