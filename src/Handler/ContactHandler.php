<?php
namespace App\Handler;

use App\Entity\Contact;
use App\Form\ContactType;
use DateTime;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class ContactHandler
{
    use HandlerTrait;

    /** @var MailerInterface */
    private $mailer;

    /**
     * CommentHandler constructor.
     *
     * @param ContainerInterface $container The container.
     * @param MailerInterface    $mailer    The mailer.
     */
    public function __construct(ContainerInterface $container, MailerInterface $mailer)
    {
        $this->container = $container;
        $this->mailer = $mailer;
    }

    /**
     * Process the request.
     *
     * @param Request $request The request.
     *
     * @return bool Whether the request where to process and did so successfully.
     */
    public function processRequest(Request $request): bool
    {
        $contact = new Contact();
        $contact->setCreatedAt(new DateTime());

        $this->form = $this->createForm(ContactType::class, $contact);

        $this->form->handleRequest($request);
        if ($this->form->isSubmitted()) {
            if ($this->form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $em->persist($contact);

                $em->flush();

                $email = (new TemplatedEmail())
                    ->from(new Address('blog@geoffreyhuck.com', 'Geoffrey Huck Blog'))
                    ->to('geoffrey@geot.fr')
                    ->subject('Contact geoffreyhuck.com')
                    ->htmlTemplate('emails/contact.html.twig')
                    ->context([
                        'contact' => $contact,
                    ]);

                $this->mailer->send($email);

                $this->addFlash('success', 'Your message has been sent.');

                return true;
            }
        }

        return false;
    }

    public function getViewParameters(): array
    {
        return [
            'formContact' => ($this->form) ? $this->form->createView() : null,
        ];
    }
}
