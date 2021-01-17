<?php
namespace App\Entity;

interface RequestInfoInterface
{
    public function getIp(): ?string;

    public function setIp(?string $ip);

    public function getUserAgent(): ?string;

    public function setUserAgent(?string $userAgent);

    public function getReferer(): ?string;

    public function setReferer(?string $referer);

    public function getAcceptLanguage(): ?string;

    public function setAcceptLanguage(?string $acceptLanguage);
}
