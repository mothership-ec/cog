<?php

namespace Message\Cog\Templating\Twig\Extension;

class PriceTwigExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('price', array($this, 'priceFilter')),
        );
    }

    public function priceFilter($value, $currency = 'GBP', $locale = null)
    {
 		$locale = ($locale == null ? \Locale::getDefault() : $locale);
 		$formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);

        $price = $formatter->formatCurrency($value, $currency);

        return $price;
    }

    public function getName()
    {
        return 'price_twig_extension';
    }
}