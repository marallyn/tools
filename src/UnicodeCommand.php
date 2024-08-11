<?php
namespace Marallyn\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
#[AsCommand(
    name: 'app:unicode-decode',
    description: 'Decodes an escaped Unicode string.',
    hidden: false,
)]

class UnicodeCommand extends Command
{
    protected function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $question = new Question('Enter the escaped Unicode string: ');
        $question->setValidator(function ($answer) {
            if (strpos($answer, '\u') !== 0) {
                throw new \RuntimeException('Invalid Unicode string format. It should start with \\u.');
            }
            return $answer;
        });

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $unicodeString = $helper->ask($input, $output, $question);

        $decodedString = json_decode('["' . $unicodeString . '"]', JSON_UNESCAPED_UNICODE)[0];

        $output->writeln($decodedString);
        return Command::SUCCESS;
    }
}
