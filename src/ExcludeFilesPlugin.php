<?php

namespace Themosis\ComposerExcludeFiles;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class ExcludeFilesPlugin implements PluginInterface, EventSubscriberInterface
{
    private Composer $composer;
    private IOInterface $io;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;

        $io->write("Themosis Exclude Files plugin activated.");
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

    public function onPreAutoloadDump(Event $event): void
    {
        $packages = $this->composer->getRepositoryManager()->getLocalRepository()->getPackages();
        $repositories = $this->composer->getRepositoryManager()->getRepositories();

        foreach ($repositories as $repository) {
            $packages = $repository->getPackages();

            foreach ($packages as $package) {
                var_dump($package->getName());
            }
        }

        $generator = $this->composer->getAutoloadGenerator();
    }
}