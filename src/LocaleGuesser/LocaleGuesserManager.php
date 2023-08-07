<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\LocaleGuesser;

use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Locale Guesser Manager
 *
 * This class is responsible for adding services with the 'human_direct_locale.guesser'
 * alias tag and run the detection.
 *
 * @author Christophe Willemsen <willemsen.christophe@gmail.com>
 * @author Matthias Breddin <mb@lunetics.com>
 */
class LocaleGuesserManager
{
    /**
     * @var array<string, LocaleGuesserInterface>
     */
    private array $guessers = [];
    /**
     * @var string[]
     */
    private array $preferredLocales = [];

    /**
     * @param string[] $guessingOrder
     */
    public function __construct(
        private readonly array $guessingOrder,
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    public function addGuesser(LocaleGuesserInterface $guesser, string $alias): void
    {
        $this->guessers[$alias] = $guesser;
    }

    public function getGuesser(string $alias): ?LocaleGuesserInterface
    {
        return $this->guessers[$alias] ?? null;
    }

    public function removeGuesser(string $alias): void
    {
        unset($this->guessers[$alias]);
    }

    /**
     * Loops through all the activated Locale Guessers and
     * calls the guessLocale methode and passing the current request.
     */
    public function guess(Request $request): ?string
    {
        $this->preferredLocales = $request->getLanguages();

        foreach ($this->guessingOrder as $guesser) {
            $guesserService = $this->getGuesser($guesser);
            if (!$guesserService) {
                throw new InvalidConfigurationException(sprintf(
                    'Locale guesser service "%s" does not exist.',
                    $guesser
                ));
            }

            $this->log('Locale %s Guessing Service Loaded', ucfirst($guesser));
            if (false !== $guesserService->guess($request)) {
                $locale = $guesserService->getIdentifiedLocale();
                $this->log('Locale has been identified by guessing service: ( %s )', ucfirst($guesser));

                return $locale;
            }
            $this->log('Locale has not been identified by the %s guessing service', ucfirst($guesser));
        }

        return null;
    }

    /**
     * @return string[]
     */
    public function getPreferredLocales(): array
    {
        return $this->preferredLocales;
    }

    /**
     * @return string[]
     */
    public function getGuessingOrder(): array
    {
        return $this->guessingOrder;
    }

    private function log(string $logMessage, string $parameters = null): void
    {
        $this->logger?->debug(sprintf($logMessage, $parameters));
    }
}
