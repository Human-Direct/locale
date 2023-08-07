<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Filter for the LocaleSwitchEvent.
 */
class FilterLocaleSwitchEvent extends Event
{
    public function __construct(
        protected readonly Request $request,
        protected readonly string $locale
    ) {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
