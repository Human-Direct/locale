<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\LocaleGuesser;

use HumanDirect\LocaleBundle\Validator\MetaValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Webmozart\Assert\Assert;

/**
 * Locale Guesser for retrieving a previously detected locale from the session.
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class SessionLocaleGuesser extends AbstractLocaleGuesser
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly MetaValidator $metaValidator,
        private readonly string $sessionKey = 'human_direct_locale'
    ) {
    }

    /**
     * Guess the locale based on the session variable.
     */
    public function guess(Request $request): bool
    {
        $session = $this->getSession();
        if (!$session->isStarted() || !$session->has($this->sessionKey)) {
            return false;
        }

        $locale = $session->get($this->sessionKey);
        if (!\is_string($locale) || !$this->metaValidator->isAllowed($locale)) {
            return false;
        }

        $this->identifiedLocale = $locale;

        return true;
    }

    public function getSessionLocale(): ?string
    {
        $locale = $this->getSession()->get($this->sessionKey);
        if (!\is_string($locale)) {
            return null;
        }

        return $locale;
    }

    public function setSessionLocale(string $locale, bool $force = false): void
    {
        $session = $this->getSession();
        if ($force || !$session->has($this->sessionKey)) {
            $session->set($this->sessionKey, $locale);
        }
    }

    private function getSession(): SessionInterface
    {
        $request = $this->requestStack->getMainRequest();
        Assert::notNull($request);

        return $request->getSession();
    }
}
