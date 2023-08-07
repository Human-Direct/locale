<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\DependencyInjection;

use HumanDirect\LocaleBundle\DependencyInjection\HumanDirectLocaleExtension;
use HumanDirect\LocaleBundle\Matcher\BestLocaleMatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

/**
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class HumanDirectLocaleExtensionTest extends TestCase
{
    /**
     * @dataProvider getFullConfig
     *
     * @param array<string, mixed> $configs
     */
    public function testLoad(array $configs, bool $strictMatch): void
    {
        $loader = new HumanDirectLocaleExtension();
        $container = new ContainerBuilder();

        $loader->load($configs, $container);

        self::assertTrue($container->hasParameter('human_direct_locale.allowed_locales'));
        self::assertTrue($container->hasParameter('human_direct_locale.topleveldomain.locale_map'));

        $localeMap = $container->getParameter('human_direct_locale.topleveldomain.locale_map');
        self::assertIsArray($localeMap);
        self::assertArrayHasKey('be', $localeMap);

        self::assertTrue($container->hasParameter('human_direct_locale.domain.locale_map'));
        $domainLocaleMap = $container->getParameter('human_direct_locale.domain.locale_map');
        self::assertIsArray($domainLocaleMap);
        self::assertArrayHasKey('sub.dutchversion.be', $domainLocaleMap);
        self::assertArrayHasKey('dutchversion.be', $domainLocaleMap);
        self::assertArrayHasKey('dutch-version.be', $domainLocaleMap);

        self::assertEquals($strictMatch, $container->hasDefinition(BestLocaleMatcher::class));
    }

    public function testBundleLoadThrowsExceptionUnlessGuessingOrderIsSet(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/The child [a-z]+ "guessing_order" [a-z\s]+ "human_direct_locale" must be configured/');

        $loader = new HumanDirectLocaleExtension();
        $loader->load([], new ContainerBuilder());
    }

    public function testGetAlias(): void
    {
        $loader = new HumanDirectLocaleExtension();
        self::assertEquals('human_direct_locale', $loader->getAlias());
    }

    public function testBindParameters(): void
    {
        $loader = new HumanDirectLocaleExtension();
        $container = new ContainerBuilder();

        $config = [
            'key' => 'value',
        ];

        $loader->bindParameters($container, $loader->getAlias(), $config);

        self::assertTrue($container->hasParameter('human_direct_locale.key'));
        self::assertEquals('value', $container->getParameter('human_direct_locale.key'));
    }

    /**
     * @return array<int, array{mixed, bool}>
     */
    public function getFullConfig(): array
    {
        $parser = new Parser();
        $data = [];

        $yaml = <<<EOF
            human_direct_locale:
              allowed_locales:
                - en
                - fr
                - de
              guessing_order:
                - session
                - cookie
                - browser
                - query
                - router
                - header
              topleveldomain:
                locale_map:
                  com: en_US
                  be: nl_BE
              domain:
                locale_map:
                  sub.dutchversion.be: en_BE
                  frechversion.be: fr_BE
                  dutchversion.be: nl_BE
                  dutch-version.be: nl_BE
                  humandirect.eu: ~
            EOF;
        $data[] = [$parser->parse($yaml), false];

        $yaml = <<<EOF
            human_direct_locale:
              strict_match: true
              allowed_locales:
                - en
                - fr
                - de
              guessing_order:
                - session
                - cookie
                - browser
                - query
                - router
                - header
              topleveldomain:
                locale_map:
                  com: en_US
                  be: nl_BE
              domain:
                locale_map:
                  sub.dutchversion.be: en_BE
                  frechversion.be: fr_BE
                  dutchversion.be: nl_BE
                  dutch-version.be: nl_BE
                  humandirect.eu: ~
            EOF;
        $data[] = [$parser->parse($yaml), true];

        return $data;
    }
}
