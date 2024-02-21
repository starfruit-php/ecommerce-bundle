<?php

namespace Starfruit\EcommerceBundle\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends FrontendController
{
    const DEFAULT_CART_NAME = 'cart';

    protected function getCartDefaultView()
    {
        return \Pimcore::getContainer()->getParameter('starfruit_ecommerce.view_templates')['cart']['default'];
    }

    protected function getCheckoutAddressView()
    {
        return \Pimcore::getContainer()->getParameter('starfruit_ecommerce.view_templates')['checkout']['address'];
    }
}
