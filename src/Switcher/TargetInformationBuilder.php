<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Switcher;

use HumanDirect\LocaleBundle\LocaleInformation\AllowedLocalesProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\ConfigurableRequirementsInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Builder to generate information about the switcher links.
 *
 * @author Matthias Breddin <mb@lunetics.com>
 * @author Christophe Willemsen <willemsen.christophe@gmail.com>
 */
class TargetInformationBuilder
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly RouterInterface $router,
        private readonly AllowedLocalesProvider $allowedLocalesProvider,
        private readonly bool $showCurrentLocale = false
    ) {
    }

    /**
     * Builds a bunch of information in order to build a switcher template.
     *
     * Will return something like this (let's say current locale is fr :
     *
     * current_route: hello_route
     * current_locale: fr
     * locales:
     *   en:
     *     link: http://app_dev.php/en/... or http://app_dev.php?_locale=en
     *     locale: en
     *     locale_target_language: English
     *     locale_current_language: Anglais
     *
     * @param array<mixed> $parameters Parameters
     *
     * @throws \Exception
     *
     * @return array{current_locale: string, current_route: mixed, locales: array<string, array{locale_current_language: string, locale_target_language: string, link: string, locale: string}>}
     */
    public function getTargetInformation(?string $targetRoute = null, array $parameters = []): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            /** @phpstan-ignore-next-line */
            return [];
        }

        $router = $this->router;
        $route = $request->attributes->get('_route');

        if (method_exists($router, 'getGenerator')) {
            $generator = $router->getGenerator();
            if ($generator instanceof ConfigurableRequirementsInterface && !$generator->isStrictRequirements()) {
                $strict = false;
            }
        }

        $info = [
            'current_locale' => $request->getLocale(),
            'current_route' => $route,
            'locales' => [],
        ];

        $parameters = array_merge(
            (array) $request->attributes->get('_route_params'),
            $request->query->all(),
            $parameters
        );

        foreach ($this->allowedLocalesProvider->getAllowedLocales() as $locale) {
            $strPos = str_starts_with($request->getLocale(), $locale);
            if (($this->showCurrentLocale && $strPos) || !$strPos) {
                $targetLocaleTargetLang = Languages::getName($locale, $locale);
                $targetLocaleCurrentLang = Languages::getName($locale, $request->getLocale());
                $parameters['_locale'] = $locale;

                try {
                    if (null !== $targetRoute && '' !== $targetRoute) {
                        $switchRoute = $router->generate($targetRoute, $parameters);
                    } elseif (\is_string($route)) {
                        $switchRoute = $router->generate($route, $parameters);
                    } else {
                        continue;
                    }
                } catch (RouteNotFoundException|InvalidParameterException) {
                    // skip routes for which we cannot generate a URL for the given locale
                    continue;
                } catch (\Exception $e) {
                    if (isset($generator, $strict)) {
                        $generator->setStrictRequirements(false);
                    }

                    throw $e;
                }

                $info['locales'][$locale] = [
                    'locale_current_language' => $targetLocaleCurrentLang,
                    'locale_target_language' => $targetLocaleTargetLang,
                    'link' => $switchRoute,
                    'locale' => $locale,
                ];
            }
        }

        if (isset($generator, $strict)) {
            $generator->setStrictRequirements(false);
        }

        return $info;
    }
}
