<?php

namespace Framework\Cli;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class CreateApplicationCommand extends Command
{
    
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
    $this
            ->setDefinition(array(
                new InputArgument('name', InputArgument::OPTIONAL, 'The application name'),
            ))
            ->setName('create')
            ->setDescription('Create new application')
            ->setHelp(<<<EOF
The <info>create</info> command creates a new application.
EOF
            );
    }
    
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        var_dump($input->getArgument('name'));
    }
}