<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle;

/**
 * Defines aliases for Events in this bundle
 */
final class Events
{
    /**
     * The human_direct_locale.change event is thrown each time the locale changes.
     * The available locales to be chosen can be restricted through the allowed_languages configuration.
     * The event listener receives an HumanDirect\LocaleBundle\Event\FilterLocaleSwitchEvent instance
     *
     * @var string
     */
    public const LOCALE_CHANGE = 'human_direct_locale.change';
}
