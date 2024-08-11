<?php
namespace Marallyn\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(
    name: 'auth',
    description: 'Generate an auth token.',
    hidden: false,
)]

class AuthTokenCommand extends Command
{
    protected int $length;

    protected function configure(): void
    {
        $this->addOption('length', 'l', InputOption::VALUE_REQUIRED, 'The length of the auth token');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $this->length = intval($input->getOption('length'));

        while ($this->length <= 0) {
            $question = new Question('What length of auth token would you like? [32] ', 32);

            $this->length = $helper->ask($input, $output, $question);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $this->length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $output->writeln("Here is your cool authToken: $randomString");

        return Command::SUCCESS;
    }
}
