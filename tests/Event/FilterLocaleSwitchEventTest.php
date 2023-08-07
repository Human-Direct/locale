<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\Event;

use HumanDirect\LocaleBundle\Event\FilterLocaleSwitchEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class FilterLocaleSwitchEventTest extends TestCase
{
    public function testFilterLocaleSwitchEvent(): void
    {
        $request = Request::create('/');
        $locale = 'de';
        $filter = new FilterLocaleSwitchEvent($request, $locale);
        self::assertEquals('/', $filter->getRequest()->getPathInfo());
        self::assertEquals('de', $filter->getLocale());
    }
}
