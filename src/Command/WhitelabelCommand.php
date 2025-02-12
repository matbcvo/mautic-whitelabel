<?php

namespace Matbcvo\MauticWhitelabel\Command;

use Composer\Command\BaseCommand;
use Dotenv\Dotenv;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

        $dotenv = Dotenv::createImmutable($projectRootPath);
        $dotenv->load();

        $logoPath = $_ENV['WHITELABEL_LOGO'] ?? '';

        $output->writeln("Applying Mautic Whitelabel...");
        $output->writeln(" - Logo Path: $logoPath");

        return 0; // Success
    }
}