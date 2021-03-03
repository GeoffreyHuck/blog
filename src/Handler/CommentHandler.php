<?php
namespace App\Handler;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Manager\CommentManager;
use DateTime;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class CommentHandler
{
    use HandlerTrait;

    /** @var CommentManager */
    private $commentManager;

    /** @var string */
    private $url;

    /**
     * CommentHandler constructor.
     *
     * @param ContainerInterface $container The container.
     */
    public function __construct(ContainerInterface $container, CommentManager $commentManager)
    {
        $this->container = $container;
        $this->commentManager = $commentManager;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
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
        $comment = new Comment();
        $comment->setUrl($this->url);
        $comment->setStatus(Comment::STATUS_NEW);
        $comment->setCreatedAt(new DateTime());

        $this->form = $this->createForm(CommentType::class, $comment);

        $this->form->handleRequest($request);
        if ($this->form->isSubmitted()) {
            if ($this->form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $reply = $this->form->get('replyTo')->getData();
                if ($reply) {
                    $commentRepo = $em->getRepository(Comment::class);

                    $repliedComment = $commentRepo->getForReply($this->url, $reply);

                    $comment->setParent($repliedComment);
                }

                $em->persist($comment);

                $em->flush();

                $this->addFlash('success', 'Your comment has been sent.');

                return true;
            }
        }

        return false;
    }

    public function getViewParameters(): array
    {
        return [
            'formComment' => ($this->form) ? $this->form->createView() : null,
        ];
    }
}
