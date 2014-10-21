<?php

namespace Message\Cog\Templating\Twig\Extension;

class PriceTwigExtension extends \Twig_Extension
{
    private $_defaultCurrency;

    public function __construct($defaultCurrency = null)
    {
        $this->_defaultCurrency = $defaultCurrency?:'GBP';
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('price', array($this, 'priceFilter')),
        );
    }

    public function priceFilter($value, $currency = null, $locale = null)
    {
 		$locale = ($locale == null ? \Locale::getDefault() : $locale);
 		$formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        $currency = $currency?:$this->_defaultCurrency;

        $price = $formatter->formatCurrency($value, $currency);

        return $price;
    }

    public function setDefaultCurrency($currency)
    {
        $this->_defaultCurrency = $currency;

        return $this;
    }

    public function getName()
    {
        return 'price_twig_extension';
    }
}