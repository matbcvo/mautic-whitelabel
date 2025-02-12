<?php

namespace Matbcvo\MauticWhitelabel\Command;

use Composer\Command\BaseCommand;
use Composer\Factory as ComposerFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Dotenv\Exception\PathException;

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

        $projectRootPath = dirname(ComposerFactory::getComposerFile());
        $envFile = $projectRootPath.'/.env';

        try {
            $dotenv = new Dotenv();
            $dotenv->loadEnv($envFile);
        } catch (PathException $e) {
            $output->writeln("<error>Environment variables file not found!</error>");
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }

        $whitelabel['brand'] = $_ENV['WHITELABEL_BRAND'] ?? '';

        $output->writeln("Whitelabel variables:");
        $output->writeln('Brand: ' . $whitelabel['brand']);

        $composer = $this->requireComposer();
        $extra = $composer->getPackage()->getExtra();
        if (isset($extra['mautic-scaffold'], $extra['mautic-scaffold']['locations'], $extra['mautic-scaffold']['locations']['web-root'])) {
            $mauticWebRoot = $extra['mautic-scaffold']['locations']['web-root'];
            $output->writeln("Mautic web-root path: $mauticWebRoot");
        } else {
            $output->writeln("<error>web-root path not defined in composer.json.</error>");
            return Command::SUCCESS;
        }

        $mauticThemesPath = $projectRootPath.'/'.$mauticWebRoot.'/themes';
        if (!is_dir($mauticThemesPath)) {
            $output->writeln("<error>Mautic themes directory not found!</error>");
            return Command::FAILURE;
        }

        $mauticSystemThemePath = $mauticThemesPath.'/system';
        if (!is_dir($mauticSystemThemePath)) {
            $output->writeln("<info>Creating Mautic system theme directory</info>");
            mkdir($mauticSystemThemePath);
        }

        // Login page
        // app/bundles/UserBundle/Resources/views/Security/base.html.twig
        $mauticLoginViewTemplatePath = $projectRootPath.'/'.$mauticWebRoot.'/app/bundles/UserBundle/Resources/views/Security';
        $output->writeln("mauticLoginViewTemplatePath: {$mauticLoginViewTemplatePath}");
        $overrideLoginViewTemplatePath = $mauticSystemThemePath.'/UserBundle/Resources/views/Security';
        $output->writeln("overrideLoginViewTemplatePath: {$overrideLoginViewTemplatePath}");

        // Create directory for overriding view template
        mkdir($mauticSystemThemePath.'/UserBundle/Resources/views/Security', $recursive = true);

        // Copy view template file to override
        copy($mauticLoginViewTemplatePath, $overrideLoginViewTemplatePath);

        $content = file_get_contents($overrideLoginViewTemplatePath);
        $pattern = '/(<div[^>]*class\s*=\s*["\'][^"\']*\bmautic-logo\b[^"\']*["\'][^>]*>)(.*?)(<\/div>)/is';
        $replacement = '$1test$3';
        $newContent = preg_replace($pattern, $replacement, $content);
        if (file_put_contents($overrideLoginViewTemplatePath, $newContent) === false) {
            $output->writeln("<error>Error writing to the override login view template file</error>");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
