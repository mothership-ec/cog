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

    public function priceFilter($value, $currency = null, $locale = null)
    {
        // if no locale was given, use default one
 		$locale = (is_null($locale) ? \Locale::getDefault() : $locale);

        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);

        // if no currency-code was given, get currency code from locale
        $currency = (is_null($currency) ? $formatter->getTextAttribute(\NumberFormatter::CURRENCY_CODE) : $currency);
        $price = $formatter->formatCurrency($value, $currency);

        return $price;
    }

    public function getName()
    {
        return 'price_twig_extension';
    }
}