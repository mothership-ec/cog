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

    public function getFunctions()
    {
        return [
            'currencySymbol' => new \Twig_Function_Method($this, 'currencySymbolFunction'),
        ];
    }

    public function priceFilter($value, $currency = null, $locale = null, array $extraOptions = [])
    {

        $locale = ($locale == null ? \Locale::getDefault() : $locale);
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        $currency = $currency?:$this->_defaultCurrency;

        $price = $formatter->formatCurrency($value, $currency);
        if (isset($extraOptions['no_decimal']) && $extraOptions['no_decimal']) {
            $price = str_replace('.00','',$price);
        }

        return $price;
    }

    /**
     * @todo This is the easiet way I could see to get a currency from a String such as 'GBP'
     * However it's not a very nice solution. This should probably be updated in the future.
     */
    public function currencySymbolFunction($currency = null, $locale= null)
    {
        $locale = ($locale == null ? \Locale::getDefault() : $locale);
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        $currency = $currency?:$this->_defaultCurrency;

        $symbol = substr($formatter->formatCurrency(0, $currency), 0, -4);

        return $symbol;
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