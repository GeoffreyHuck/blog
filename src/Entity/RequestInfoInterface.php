<?php
namespace App\Entity;

interface RequestInfoInterface
{
    public function getIp(): ?string;

    public function setIp(?string $ip): self;

    public function getUserAgent(): ?string;

    public function setUserAgent(?string $userAgent): self;

    public function getReferer(): ?string;

    public function setReferer(?string $referer): self;

    public function getAcceptLanguage(): ?string;

    public function setAcceptLanguage(?string $acceptLanguage): self;
}
