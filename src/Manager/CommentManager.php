<?php
namespace App\Manager;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;

class CommentManager
{
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Gets emails to notify for positing a comment.
     *
     * @param Comment $comment The comment.
     *
     * @return string[]
     */
    public function getEmailsToNotifyForPostingComment(Comment $comment): array
    {
        $rootComment = $this->getRootOf($comment);

        $emails = $this->getEmailsToNotifyForPostingCommentRec($rootComment);

        // Remove the poster's email.
        $emails = array_diff($emails, [$comment->getEmail()]);

        $emails[] = 'geoffrey@geot.fr';

        return array_unique($emails);
    }

    private function getEmailsToNotifyForPostingCommentRec(Comment $comment): array
    {
        $emails = [$comment->getEmail()];
        foreach ($comment->getChildren() as $child) {
            $emails = array_merge($emails, $this->getEmailsToNotifyForPostingCommentRec($child));
        }

        return array_unique($emails);
    }

    /**
     * Get the root from a comment.
     *
     * @param Comment $comment The comment.
     *
     * @return Comment The root.
     */
    private function getRootOf(Comment $comment): Comment
    {
        $parent = $comment->getParent();

        if (!$parent) {
            return $comment;
        }

        return $this->getRootOf($parent);
    }
}
