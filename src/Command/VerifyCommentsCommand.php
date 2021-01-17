<?php
namespace App\Command;

use App\Entity\Comment;
use App\Manager\CommentManager;
use App\Service\Akismet;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
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

    /** * @var EntityManagerInterface */
    private $em;

    /** @var CommentManager */
    private $commentManager;

    /** @var Akismet */
    private $akismet;

    /** @var MailerInterface */
    private $mailer;

    /**
     * VerifyCommentsCommand constructor.
     *
     * @param EntityManagerInterface $em             The entity manager.
     * @param CommentManager         $commentManager The comment manager.
     * @param Akismet                $akismet        The akismet service.
     * @param MailerInterface        $mailer         The mailer.
     */
    public function __construct(EntityManagerInterface $em, CommentManager $commentManager, Akismet $akismet, MailerInterface $mailer)
    {
        parent::__construct();

        $this->em = $em;
        $this->commentManager = $commentManager;
        $this->akismet = $akismet;
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
        $newComments = $commentRepo->findBy([
            'status' => Comment::STATUS_NEW,
        ]);

        $io->writeln('Found ' . count($newComments) . ' new comments.');

        foreach ($newComments as $newComment) {
            $io->writeln('Comment from ' . $newComment->getAuthor() . ' at ' . $newComment->getCreatedAt()->format('d/m/Y H:i:s'));

            $spamScore = $this->akismet->getSpamScore($newComment);
            if ($spamScore == Akismet::BLATANT_SPAM) {
                $io->writeln('Blatant spam.');

                $newComment->setStatus(Comment::STATUS_SPAM);
            } elseif ($spamScore == Akismet::MAYBE_SPAM) {
                $io->writeln('Maybe spam : verify manually.');

                $email = (new TemplatedEmail())
                    ->from(new Address('blog@geoffreyhuck.com', 'Geoffrey Huck Blog'))
                    ->to('geoffrey@geot.fr')
                    ->subject('Comment to verify')
                    ->htmlTemplate('emails/verify_comment.html.twig')
                    ->context([
                        'comment' => $newComment,
                    ]);

                $this->mailer->send($email);

                $newComment->setStatus(Comment::STATUS_MANUAL);
            } else {
                $io->writeln('Verified.');

                $newComment->setStatus(Comment::STATUS_VERIFIED);
            }

            $this->em->persist($newComment);
            $this->em->flush();
        }

        $verifiedComments = $commentRepo->findBy([
            'status' => Comment::STATUS_VERIFIED,
        ]);

        $io->writeln('Found ' . count($verifiedComments) . ' verified comments.');

        foreach ($verifiedComments as $verifiedComment) {
            $io->writeln('Comment from ' . $verifiedComment->getAuthor() . ' at ' . $verifiedComment->getCreatedAt()->format('d/m/Y H:i:s'));

            $emails = $this->commentManager->getEmailsToNotifyForPostingComment($verifiedComment);

            $io->writeln('Send emails to : ' . implode(', ', $emails));

            foreach ($emails as $email) {
                $email = (new TemplatedEmail())
                    ->from(new Address('blog@geoffreyhuck.com', 'Geoffrey Huck Blog'))
                    ->to($email)
                    ->subject('Things are getting hot since your last comment !')
                    ->htmlTemplate('emails/notify_comment.html.twig')
                    ->context([
                        'comment' => $verifiedComment,
                    ]);

                $this->mailer->send($email);
            }

            $verifiedComment->setStatus(Comment::STATUS_NOTIFIED);

            $this->em->persist($verifiedComment);
            $this->em->flush();
        }

        $io->writeln('Done');

        $this->release();

        return Command::SUCCESS;
    }
}
