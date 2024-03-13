<?php
namespace Marallyn\Command;

use Throwable;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(
    name: 'uuid',
    description: 'Generate some uuids.',
    hidden: false,
)]

class UuidCommand extends Command
{
    protected int $count;
    protected int $version;
    protected string $substring;

    protected function configure(): void
    {
        $this->addOption('uuidversion', 'u', InputOption::VALUE_REQUIRED, 'The version of uuid to generate.');
        $this->addOption('count', 'c', InputOption::VALUE_REQUIRED, 'The number of uuids to generate.');
        $this->addOption('substring', 's', InputOption::VALUE_REQUIRED, 'A substring required in the generated uuids.');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $this->version = intval($input->getOption('uuidversion'));
        $this->count = intval($input->getOption('count'));
        $this->substring = $input->getOption('substring') ?? '';

        while ($this->version <= 0 || $this->version > 7) {
            $question = new Question('What version of uuid would you like (1-7)? [4] ', 4);

            $this->version = $helper->ask($input, $output, $question);
        }

        if ($this->count === 0) {
            $question = new Question('How many uuids would you like? [10] ', 10);

            $this->count = $helper->ask($input, $output, $question);

            $output->writeln('You have just selected: '.$this->count);
        }

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $msg = sprintf("%d version %d uuids", $this->count, $this->version);
        if ($this->substring) {
            $msg .= sprintf(" containing '%s'", $this->substring);
        }
        $output->writeln([
            $msg,
            str_repeat('=', strlen($msg)),
            '',
        ]);

        $function = "uuid{$this->version}";

        $count = 0;
        while ($count < $this->count) {
            try {
                $uuid = Uuid::{$function}();
                if (!$this->substring || str_contains(str_replace('-', '', $uuid), $this->substring)) {
                    $count++;
                    $output->writeln(sprintf("%s", $uuid));
                }
            } catch (Throwable $e) {
                $output->writeln(sprintf("uuid version %d is not currently supported.", $this->version));

                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }
}
