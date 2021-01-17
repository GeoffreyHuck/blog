<?php
namespace App\Command;

use App\Entity\Comment;
use App\Manager\CommentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class VerifyCommentsCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'app:verify:comments';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var CommentManager
     */
    private $commentManager;

    /** @var MailerInterface */
    private $mailer;

    /**
     * VerifyCommentsCommand constructor.
     *
     * @param EntityManagerInterface $em             The entity manager.
     * @param CommentManager         $commentManager The comment manager.
     * @param MailerInterface        $mailer         The mailer.
     */
    public function __construct(EntityManagerInterface $em, CommentManager $commentManager, MailerInterface $mailer)
    {
        parent::__construct();

        $this->em = $em;
        $this->commentManager = $commentManager;
        $this->mailer = $mailer;
    }

    protected function configure()
    {
        $this->setDescription('Verify the comments');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        $io = new SymfonyStyle($input, $output);

        $commentRepo = $this->em->getRepository(Comment::class);
        $comments = $commentRepo->findBy([
            'status' => Comment::STATUS_NEW,
        ]);

        $io->writeln('Found ' . count($comments) . ' comments.');

        foreach ($comments as $comment) {
            $io->writeln('Comment from ' . $comment->getAuthor() . ' at ' . $comment->getCreatedAt()->format('d/m/Y H:i:s'));

            $emails = $this->commentManager->getEmailsToNotifyForPostingComment($comment);

            $io->writeln('Send emails to : ' . implode(', ', $emails));

            foreach ($emails as $email) {
                $email = (new TemplatedEmail())
                    ->from(new Address('blog@geoffreyhuck.com', 'Geoffrey Huck Blog'))
                    ->to($email)
                    ->subject('Things are getting hot since your last comment !')
                    ->htmlTemplate('emails/notify_comment.html.twig')
                    ->context([
                        'comment' => $comment,
                    ]);

                $this->mailer->send($email);
            }

            $comment->setStatus(Comment::STATUS_NOTIFIED);

            $this->em->persist($comment);
            $this->em->flush();
        }

        $io->writeln('Done');

        $this->release();

        return Command::SUCCESS;
    }
}
