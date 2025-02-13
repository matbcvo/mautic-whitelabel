<?php

namespace Matbcvo\MauticWhitelabel\Command;

use Composer\Command\BaseCommand;
use Composer\Factory as ComposerFactory;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Dotenv\Exception\PathException;
use Symfony\Component\Filesystem\Filesystem;

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

        try {
            $whitelabel = $this->getWhitelabelConfig($projectRootPath, $output);
        } catch (\RuntimeException $e) {
            $output->writeln(sprintf("<error>%s</error>", $e->getMessage()));
            return Command::FAILURE;
        }

        $composer = $this->requireComposer();
        $extra = $composer->getPackage()->getExtra();
        if (isset($extra['mautic-scaffold'], $extra['mautic-scaffold']['locations'], $extra['mautic-scaffold']['locations']['web-root'])) {
            $mauticWebRoot = $extra['mautic-scaffold']['locations']['web-root'];
            $output->writeln("Mautic web-root path: $mauticWebRoot");
        } else {
            $output->writeln("<error>web-root path not defined in composer.json.</error>");
            return Command::SUCCESS;
        }

        try {
            $mauticSystemThemePath = $this->createSystemTheme($projectRootPath, $mauticWebRoot);
            $this->copyLoginViewTemplate($projectRootPath, $mauticWebRoot, $mauticSystemThemePath, $output);
            $this->overrideLoginViewTemplate($mauticSystemThemePath, $whitelabel);
            $this->clearMauticCache($projectRootPath);
        } catch (\RuntimeException $e) {
            $output->writeln(sprintf("<error>%s</error>", $e->getMessage()));
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function getWhitelabelConfig(string $projectRootPath, OutputInterface $output): array
    {
        $envFile = $projectRootPath.'/.env';

        try {
            $dotenv = new Dotenv();
            $dotenv->loadEnv($envFile);
        } catch (PathException $e) {
            throw new \RuntimeException("Environment variables file not found!");
        }

        $prefix = 'WHITELABEL_';
        $whitelabel = [];

        foreach ($_ENV as $envKey => $envValue) {
            if (strpos($envKey, $prefix) === 0) {
                $key = strtolower(substr($envKey, strlen($prefix)));
                $whitelabel[$key] = $envValue;
            }
        }

        $output->writeln("Whitelabel config:");
        foreach ($whitelabel as $key => $value) {
            $output->writeln(ucfirst($key) . ": " . $value);
        }

        return $whitelabel;
    }

    private function createSystemTheme(string $projectRootPath, string $mauticWebRoot): string
    {
        $mauticThemesPath = $projectRootPath.'/'.$mauticWebRoot.'themes';
        if (!is_dir($mauticThemesPath)) {
            throw new \RuntimeException("Mautic themes directory not found!");
        }

        $mauticSystemThemePath = $mauticThemesPath.'/system';
        if (!is_dir($mauticSystemThemePath)) {
            mkdir($mauticSystemThemePath);
        }

        return $mauticSystemThemePath;
    }

    private function copyLoginViewTemplate(string $projectRootPath, string $mauticWebRoot, string $mauticSystemThemePath, OutputInterface $output): void
    {
        // Login page
        // app/bundles/UserBundle/Resources/views/Security/base.html.twig
        $mauticLoginViewTemplatePath = $projectRootPath.'/'.$mauticWebRoot.'app/bundles/UserBundle/Resources/views/Security';
        $output->writeln("mauticLoginViewTemplatePath: {$mauticLoginViewTemplatePath}");
        $overrideLoginViewTemplatePath = $mauticSystemThemePath.'/UserBundle/Resources/views/Security';
        $output->writeln("overrideLoginViewTemplatePath: {$overrideLoginViewTemplatePath}");

        // Create directory for overriding view template
        mkdir($mauticSystemThemePath.'/UserBundle/Resources/views/Security', $permissions = 0777, $recursive = true);

        if (!is_dir($mauticSystemThemePath.'/UserBundle/Resources/views/Security')) {
            throw new \RuntimeException("Creating themes/system/UserBundle/Resources/views/Security directory was not successful");
        }

        // Copy view template file to override
        copy($mauticLoginViewTemplatePath.'/base.html.twig', $overrideLoginViewTemplatePath.'/base.html.twig');
    }

    private function overrideLoginViewTemplate(string $mauticSystemThemePath, array $whitelabel): void
    {
        $path = $mauticSystemThemePath.'/UserBundle/Resources/views/Security/base.html.twig';
        $content = file_get_contents($path);
        $newInnerHTML = '<img src="{{ LOGIN_LOGO }}" style="width: {{ LOGIN_LOGO_WIDTH }}px; margin: {{ LOGIN_LOGO_MARGIN_TOP }}px 0 {{ LOGIN_LOGO_MARGIN_BOTTOM }}px 0;" />';
        $pattern = '/(<div[^>]*class\s*=\s*["\'][^"\']*\bmautic-logo\b[^"\']*["\'][^>]*>)(.*?)(<\/div>)/is';
        $replacement = '$1test$3';
        $newContent = preg_replace($pattern, $replacement, $content);
        if (file_put_contents($path, $newContent) === false) {
            throw new \RuntimeException('Error writing to the override login view template file');
        }
    }

    private function clearMauticCache(string $projectRootPath): void
    {
        $cachePath = $projectRootPath.'/var/cache';
        $filesystem = new Filesystem();
        $cacheFiles = glob($cachePath.'/*');

        if ($cacheFiles !== false) {
            foreach ($cacheFiles as $cacheFile) {
                $filesystem->remove($cacheFile);
            }
        }
    }
}
