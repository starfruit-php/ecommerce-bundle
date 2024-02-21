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

3. 

# Config
## Product Parent Class

Extends Data Object **Product** with class `\Starfruit\EcommerceBundle\Model\Product\AbstractProduct`

## View templates

Config view templates to render data from bundle Controller, please overwrite below default config in `config/config.yaml`:

```bash
    starfruit_ecommerce:
        view_templates:
            cart:
                default: 'cart/cart.html.twig'
            checkout:
                address: 'checkout/address.html.twig'
```

# Events and Event Listeners

[See list](Event "Events and Event Listeners")
