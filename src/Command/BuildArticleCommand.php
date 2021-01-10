<?php
namespace App\Command;

use App\Manager\ArticleManager;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class BuildArticleCommand extends Command
{
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
        $io = new SymfonyStyle($input, $output);

        $question = new Question('The directory (name, url) of the article ?');
        $question->setAutocompleterValues($this->articleManager->getAllNames());

        $name = $io->askQuestion($question);

        // This function is called whenever the input changes and new
        // suggestions are needed.
        $callback = function (string $userInput): array {
            // Strip any characters from the last slash to the end of the string
            // to keep only the last directory and generate suggestions for it
            $inputPath = preg_replace('%(/|^)[^/]*$%', '$1', $userInput);
            $inputPath = '' === $inputPath ? '.' : $inputPath;

            // CAUTION - this example code allows unrestricted access to the
            // entire filesystem. In real applications, restrict the directories
            // where files and dirs can be found
            $foundFilesAndDirs = @scandir($inputPath) ?: [];

            return array_map(function ($dirOrFile) use ($inputPath) {
                return $inputPath.$dirOrFile;
            }, $foundFilesAndDirs);
        };

        $question = new Question('Please provide the full path of a file to parse');
        $question->setAutocompleterCallback($callback);

        try {
            $this->articleManager->validateSource($name);
        } catch (Exception $e) {
            $io->error($e);

            return Command::FAILURE;
        }

        $this->articleManager->build($name);

        try {
            $article = $this->articleManager->get($name);
        } catch (Exception $e) {
            $io->error($e);

            return Command::FAILURE;
        }

        $io->success('The article has been successfully built !');

        if (!$article->isPublished()) {
            $io->info('The article is currently not published. To publish it, add a
             published_date: "YYYY-mm-dd" entry in metadata.json and build it again.');
        }

        return Command::SUCCESS;
    }
}
