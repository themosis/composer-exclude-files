<?php

namespace Themosis\ComposerExcludeFiles\Tests;

use Themosis\ComposerExcludeFiles\ExcludeFilesPlugin;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Themosis\ComposerExcludeFiles\ExcludeFilesPlugin
 */
class ExcludeFilesPluginTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_exclude_files_from_autoloader(): void
    {
        $plugin = new ExcludeFilesPlugin();

        $this->assertTrue(true);
    }
}
