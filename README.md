Starfruit Ecommerce Bundle
<!-- [TOC] -->

# Requirement

- Install [E-Commerce Framework](https://pimcore.com/docs/platform/Ecommerce_Framework/ "E-Commerce Framework") 

# Installation

1. On your Pimcore 11 root project:
```bash
$ composer require starfruit/ecommerce-bundle
```

2. Update `config/bundles.php` file:
```bash
return [
    ....
    Starfruit\EcommerceBundle\StarfruitEcommerceBundle::class => ['all' => true],
];
```

3. Extends Data Object **Product** with class `\Starfruit\EcommerceBundle\Model\Product\AbstractProduct`
