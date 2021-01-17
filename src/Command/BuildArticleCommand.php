<?php
namespace App\Command;

use App\Manager\ArticleManager;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
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
        $this->setDescription('Build an article');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        $io = new SymfonyStyle($input, $output);

        $question = new Question('The directory (name, url) of the article ?');
        $question->setAutocompleterValues($this->articleManager->getAllDirectoryNames());

        $name = $io->askQuestion($question);

        try {
            $this->articleManager->validateSource($name);

            $this->articleManager->build($name);

            $article = $this->articleManager->get($name);
        } catch (Exception $e) {
            $io->error($e);

            $this->release();

            return Command::FAILURE;
        }

        $io->success('The article has been successfully built !');

        if (!$article->isPublished()) {
            $io->info('The article is currently not published. To publish it, add a
             published_date: "YYYY-mm-dd" entry in metadata.json and build it again.');
        }

        $this->release();

        return Command::SUCCESS;
    }
}
