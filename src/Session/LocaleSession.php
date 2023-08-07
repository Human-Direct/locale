<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Session;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LocaleSession
{
    private SessionInterface $session;

    public function __construct(
        RequestStack $requestStack,
        private readonly string $sessionKey = 'human_direct_locale'
    ) {
        $this->session = $requestStack->getSession();
    }

    /**
     * Checks if the locale has changes.
     */
    public function hasLocaleChanged(string $locale): bool
    {
        return $locale !== $this->session->get($this->sessionKey);
    }

    public function setLocale(string $locale): void
    {
        $this->session->set($this->sessionKey, $locale);
    }

    public function getLocale(?string $defaultLocale = null): ?string
    {
        $locale = $this->session->get($this->sessionKey, $defaultLocale);
        if (!\is_string($locale)) {
            return null;
        }

        return $locale;
    }

    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }
}
