<?php

namespace Kunstmaan\GeneratorBundle\Command;

use Kunstmaan\GeneratorBundle\Generator\ConfigGenerator;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generates config files
 */
class GenerateConfigCommand extends KunstmaanGenerateCommand
{
    /** @var string */
    private $projectDir;

    /** @var bool */
    private $overwriteSecurity;

    /** @var bool */
    private $overwriteLiipImagine;

    /** @var bool */
    private $overwriteFosHttpCache;

    /** @var bool */
    private $overwriteFosUser;

    /**
     * @param string $projectDir
     */
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;

        parent::__construct();
    }

    /**
     * @see Command
     */
    protected function configure()
    {
        $this->setDescription('Generates all needed config files not generated by recipes')
            ->addOption(
                'overwrite-security',
                '',
                InputOption::VALUE_REQUIRED,
                'Whether the command should generate an example or just overwrite the already existing config file'
            )
            ->addOption(
                'overwrite-liipimagine',
                '',
                InputOption::VALUE_REQUIRED,
                'Whether the command should generate an example or just overwrite the already existing config file'
            )
            ->addOption(
                'overwrite-foshttpcache',
                '',
                InputOption::VALUE_REQUIRED,
                'Whether the command should generate an example or just overwrite the already existing config file'
            )
            ->addOption(
                'overwrite-fosuser',
                '',
                InputOption::VALUE_REQUIRED,
                'Whether the command should generate an example or just overwrite the already existing config file'
            )
            ->setName('kuma:generate:config');
    }

    /**
     * {@inheritdoc}
     */
    protected function getWelcomeText()
    {
        return 'Welcome to the Kunstmaan config generator';
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute()
    {
        $this->assistant->writeSection('Config generation');

        $this->createGenerator()->generate(
            $this->projectDir,
            $this->overwriteSecurity,
            $this->overwriteLiipImagine,
            $this->overwriteFosHttpCache,
            $this->overwriteFosUser
        );

        $this->assistant->writeSection('Config successfully created', 'bg=green;fg=black');
    }

    /**
     * {@inheritdoc}
     */
    protected function doInteract()
    {
        $this->assistant->writeLine(["This helps you to set all default config files needed to run KunstmaanCMS.\n"]);

        $this->overwriteSecurity = $this->assistant->getOptionOrDefault('overwrite-security', null);
        $this->overwriteLiipImagine = $this->assistant->getOptionOrDefault('overwrite-liipimagine', null);
        $this->overwriteFosHttpCache = $this->assistant->getOptionOrDefault('overwrite-foshttpcache', null);
        $this->overwriteFosUser = $this->assistant->getOptionOrDefault('overwrite-fosuser', null);

        if (null === $this->overwriteSecurity) {
            $this->overwriteSecurity = $this->assistant->askConfirmation(
                'Do you want to overwrite the default security.yaml configuration file? (y/n)',
                'y'
            );
        }
        if (null === $this->overwriteLiipImagine) {
            $this->overwriteLiipImagine = $this->assistant->askConfirmation(
                'Do you want to overwrite the default liip_imagine.yaml configuration file? (y/n)',
                'y'
            );
        }
        if (null === $this->overwriteFosHttpCache) {
            $this->overwriteFosHttpCache = $this->assistant->askConfirmation(
                'Do you want to overwrite the production fos_http_cache.yaml configuration file? (y/n)',
                'y'
            );
        }
        if (null === $this->overwriteFosUser) {
            $this->overwriteFosUser = $this->assistant->askConfirmation(
                'Do you want to overwrite the fos_user.yaml configuration file? (y/n)',
                'y'
            );
        }
    }

    /**
     * @return ConfigGenerator
     */
    protected function createGenerator()
    {
        $filesystem = $this->getContainer()->get('filesystem');
        $registry = $this->getContainer()->get('doctrine');

        return new ConfigGenerator($filesystem, $registry, '/config', $this->assistant, $this->getContainer());
    }
}
