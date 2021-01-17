<?php
namespace App\Service;

use App\Entity\Comment;
use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Akismet
{
    const NO_SPAM = 0;
    const MAYBE_SPAM = 1;
    const BLATANT_SPAM = 2;

    /**
     * @var string
     */
    private $akismetKey;

    /**
     * @var string
     */
    private $siteUrl;

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * Akismet constructor.
     *
     * @param HttpClientInterface $httpClient The HTTP client.
     * @param string              $siteUrl    The site url.
     * @param string              $akismetKey The akismet key.
     */
    public function __construct(HttpClientInterface $httpClient, string $siteUrl, string $akismetKey)
    {
        $this->httpClient = $httpClient;
        $this->siteUrl = $siteUrl;
        $this->akismetKey = $akismetKey;
    }

    /**
     * Gets the spam score of a comment.
     *
     * @param Comment $comment The comment.
     *
     * @return int Spam score: NO_SPAM, MAYBE_SPAM, BLATANT_SPAM.
     *
     * @throws RuntimeException
     */
    public function getSpamScore(Comment $comment): int
    {
        $endpoint = 'https://' . $this->akismetKey . '.rest.akismet.com/1.1/comment-check';

        $response = $this->httpClient->request('POST', $endpoint, [
            'body' => [
                'blog' => $this->siteUrl,
                'comment_type' => 'comment',
                'comment_author' => $comment->getAuthor(),
                'comment_author_email' => $comment->getEmail(),
                'comment_content' => $comment->getContent(),
                'comment_date_gmt' => $comment->getCreatedAt()->format('c'),
                'user_ip' => $comment->getIp(),
                'user_agent' => $comment->getUserAgent(),
                'referrer' => $comment->getReferer(),
                'permalink' => $comment->getUrl(),
                'blog_lang' => 'en',
                'blog_charset' => 'UTF-8',
                'is_test' => true,
            ],
        ]);

        $headers = $response->getHeaders();
        if (isset($headers['X-akismet-pro-tip']) && $headers['X-akismet-pro-tip'][0] === 'discard') {
            return self::BLATANT_SPAM;
        }

        $content = $response->getContent();
        if (isset($headers['X-akismet-debug-help'])) {
            throw new RuntimeException(sprintf('Unable to check for spam: %s (%s).', $content, $headers['x-akismet-debug-help'][0]));
        }

        return ('true' === $content) ? self::MAYBE_SPAM : self::NO_SPAM;
    }
}
