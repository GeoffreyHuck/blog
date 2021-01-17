<?php
namespace App\Command;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\SelfSaltingEncoderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EmailValidator;
use function get_class;

class CreateAdminCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'app:create:admin';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * CreateAdminCommand constructor.
     *
     * @param EntityManagerInterface  $em             The entity manager.
     * @param EncoderFactoryInterface $encoderFactory The encoder factory.
     */
    public function __construct(EntityManagerInterface $em, EncoderFactoryInterface $encoderFactory)
    {
        parent::__construct();

        $this->em = $em;
        $this->encoderFactory = $encoderFactory;
    }

    protected function configure()
    {
        $this->setDescription('Create an admin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        $io = new SymfonyStyle($input, $output);

        $encoder = $this->encoderFactory->getEncoder(Admin::class);

        $io->info('Encoder in use : ' . get_class($encoder));

        if (!($encoder instanceof SelfSaltingEncoderInterface)) {
            $io->error('The encoder require a salt.');

            $this->release();

            return Command::FAILURE;
        }

        $email = $io->ask('Email ?');

        $emailValidator = new EmailValidator();
        try {
            $emailValidator->validate($email, new Email());
        } catch (Exception $e) {
            $io->error('The email is not valid : ' . $e->getMessage());

            $this->release();

            return Command::FAILURE;
        }

        $passwordQuestion = new Question('Password ?');
        $passwordQuestion->setHidden(true);

        $plainPassword = $io->askQuestion($passwordQuestion);

        $encodedPassword = $encoder->encodePassword($plainPassword, null);

        $admin = new Admin();
        $admin->setEmail($email);
        $admin->setPassword($encodedPassword);
        $admin->setRoles(['ROLE_MEMBER', 'ROLE_SUPER_ADMIN']);

        $io->writeln('Done');

        $this->release();

        return Command::SUCCESS;
    }
}
