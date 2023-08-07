<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Cookie;

use Symfony\Component\HttpFoundation\Cookie;

/**
 * @author Christophe Willemsen <willemsen.christophe@gmail.com>
 */
class LocaleCookie
{
    public function __construct(
        private readonly string $name,
        private readonly int $ttl,
        private readonly string $path,
        private readonly ?string $domain,
        private readonly ?bool $secure,
        private readonly bool $httpOnly,
        private readonly bool $setOnChange
    ) {
    }

    public function getLocaleCookie(string $locale): Cookie
    {
        return Cookie::create(
            $this->name,
            $locale,
            $this->computeExpireTime(),
            $this->path,
            $this->domain,
            $this->secure,
            $this->httpOnly
        );
    }

    public function setCookieOnChange(): bool
    {
        return $this->setOnChange;
    }

    public function getName(): string
    {
        return $this->name;
    }

    private function computeExpireTime(): \DateTime
    {
        $expireTime = time() + $this->ttl;
        $date = new \DateTime();
        $date->setTimestamp($expireTime);

        return $date;
    }
}
