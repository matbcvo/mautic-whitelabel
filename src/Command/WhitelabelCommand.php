<?php

namespace Matbcvo\MauticWhitelabel\Command;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Dotenv\Dotenv;

class WhitelabelCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('mautic:whitelabel')
            ->setDescription('Whitelabels Mautic instance');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Executing whitelabel process...</info>');

        $projectRootPath = dirname(\Composer\Factory::getComposerFile());
        $envFile = $projectRootPath.'/.env';

        if (!file_exists($envFile)) {
            $output->writeln("<error>No .env file found! Skipping branding.</error>");
            return 1; // Failure
        }

        $dotenv = new Dotenv();
        $dotenv->loadEnv($envFile);

        $whitelabel['brand'] = $_ENV['WHITELABEL_BRAND'] ?? '';

        $output->writeln("Whitelabel variables:");
        $output->writeln('Brand: ' . $whitelabel['brand']);

        $composer = $this->requireComposer();
        $extra = $composer->getPackage()->getExtra();
        if (isset($extra['mautic-scaffold'], $extra['mautic-scaffold']['locations'], $extra['mautic-scaffold']['locations']['web-root'])) {
            $webRoot = $extra['mautic-scaffold']['locations']['web-root'];
            $output->writeln("Mautic web-root path: $webRoot");
        } else {
            $output->writeln("<error>web-root path not defined in composer.json.</error>");
            return 1;
        }

        return 0; // Success
    }
}