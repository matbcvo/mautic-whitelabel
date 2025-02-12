<?php

namespace Matbcvo\MauticWhitelabel;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use Composer\Script\Event;
use Matbcvo\MauticWhitelabel\Command\WhitelabelCommand;

class Plugin implements PluginInterface, EventSubscriberInterface, Capable, CommandProvider
{
    protected $composer;
    protected $io;
    private const CALLBACK_PRIORITY = PHP_INT_MAX;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => [
                'onPostInstallOrUpdate', self::CALLBACK_PRIORITY
            ],
            ScriptEvents::POST_UPDATE_CMD => [
                'onPostInstallOrUpdate', self::CALLBACK_PRIORITY
            ],
        ];
    }

    public function getCapabilities()
    {
        return [
            CommandProvider::class => static::class,
        ];
    }

    public function getCommands(): array
    {
        return [
            new WhitelabelCommand(),
        ];
    }

    public function onPostInstallOrUpdate(Event $event)
    {
        $io = $event->getIO();
        $io->write("<info>Run [composer mautic:whitelabel] to whitelabel your Mautic instance.</info>");
    }
}
