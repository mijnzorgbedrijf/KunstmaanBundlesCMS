<?php

namespace Kunstmaan\AdminBundle\Twig;

use IntlDateFormatter as DateFormatter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class DateByLocaleExtension extends AbstractExtension
{
    /**
     * Get Twig filters defined in this extension.
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            new TwigFilter('localeDate', '\Kunstmaan\AdminBundle\Twig\DateByLocaleExtension::localeDateFilter'),
        ];
    }

    /**
     * A date formatting filter for Twig, renders the date using the specified parameters.
     *
     * @param mixed  $date     Unix timestamp to format
     * @param string $locale   The locale
     * @param string $dateType The date type
     * @param string $timeType The time type
     * @param string $pattern  The pattern to use
     *
     * @return string
     */
    public static function localeDateFilter($date, $locale = 'nl', $dateType = 'medium', $timeType = 'none', $pattern = null)
    {
        $values = [
            'none' => DateFormatter::NONE,
            'short' => DateFormatter::SHORT,
            'medium' => DateFormatter::MEDIUM,
            'long' => DateFormatter::LONG,
            'full' => DateFormatter::FULL,
        ];

        if (\is_null($pattern)) {
            $dateFormatter = DateFormatter::create(
                $locale,
                $values[$dateType],
                $values[$timeType], 'Europe/Brussels'
            );
        } else {
            $dateFormatter = DateFormatter::create(
                $locale,
                $values[$dateType],
                $values[$timeType], 'Europe/Brussels',
                DateFormatter::GREGORIAN,
                $pattern
            );
        }

        return $dateFormatter->format($date);
    }
}
