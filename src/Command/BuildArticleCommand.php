<?php
namespace App\Command;

use App\Manager\ArticleManager;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class BuildArticleCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'app:build-article';

    /**
     * @var ArticleManager
     */
    private $articleManager;

    /**
     * BuildArticleCommand constructor.
     *
     * @param ArticleManager $articleManager The article manager.
     */
    public function __construct(ArticleManager $articleManager)
    {
        parent::__construct();

        $this->articleManager = $articleManager;
    }

    protected function configure()
    {
        $this->setDescription('Build an article')
            ->addArgument('dir', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        $io = new SymfonyStyle($input, $output);

        $name = $input->getArgument('dir');
        if (!$name) {

            $question = new Question('The directory (name, url) of the article ?');
            $question->setAutocompleterValues($this->articleManager->getAllDirectoryNames());

            $name = $io->askQuestion($question);
        }

        try {
            $this->articleManager->validateSource($name);

            $this->articleManager->build($name);

            $this->articleManager->synchronize($name);
        } catch (Exception $e) {
            $io->error($e);

            $this->release();

            return Command::FAILURE;
        }

        $io->success('The article has been successfully built !');

        $this->release();

        return Command::SUCCESS;
    }
}
