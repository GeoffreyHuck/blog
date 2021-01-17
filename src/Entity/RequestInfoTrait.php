<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait RequestInfoTrait
{
    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", nullable=true)
     */
    private $ip;

    /**
     * @var string
     *
     * @ORM\Column(name="user_agent", type="string", nullable=true)
     */
    private $userAgent;

    /**
     * @var string
     *
     * @ORM\Column(name="referer", type="string", nullable=true)
     */
    private $referer;

    /**
     * @var string
     *
     * @ORM\Column(name="accept_language", type="string", nullable=true)
     */
    private $acceptLanguage;

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip)
    {
        $this->ip = $ip;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getReferer(): ?string
    {
        return $this->referer;
    }

    public function setReferer(?string $referer)
    {
        $this->referer = $referer;

        return $this;
    }

    public function getAcceptLanguage(): ?string
    {
        return $this->acceptLanguage;
    }

    public function setAcceptLanguage(?string $acceptLanguage)
    {
        $this->acceptLanguage = $acceptLanguage;

        return $this;
    }
}
