<?php

namespace Starfruit\EcommerceBundle\Event;

final class CartEvents
{
    /**
     * @Event("Starfruit\EcommerceBundle\Event\Model\CartEvent")
     *
     * @var string
     * 
     * Pre add product to cart.
     */
    const PRE_ADD = 'starfruit.ecommerce.cart.pre.add';

    /**
     * @Event("Starfruit\EcommerceBundle\Event\Model\CartEvent")
     *
     * @var string
     * 
     * Post add product to cart.
     */
    const POST_ADD = 'starfruit.ecommerce.cart.post.add';

    /**
     * @Event("Starfruit\EcommerceBundle\Event\Model\CartEvent")
     *
     * @var string
     * 
     * Pre update items in cart.
     */
    const PRE_UPDATE = 'starfruit.ecommerce.cart.pre.update';

    /**
     * @Event("Starfruit\EcommerceBundle\Event\Model\CartEvent")
     *
     * @var string
     * 
     * Post update items in cart.
     */
    const POST_UPDATE = 'starfruit.ecommerce.cart.post.update';

    /**
     * @Event("Starfruit\EcommerceBundle\Event\Model\CartEvent")
     *
     * @var string
     * 
     * Pre remove item from cart.
     */
    const PRE_REMOVE = 'starfruit.ecommerce.cart.pre.remove';

    /**
     * @Event("Starfruit\EcommerceBundle\Event\Model\CartEvent")
     *
     * @var string
     * 
     * Post remove item from cart.
     */
    const POST_REMOVE = 'starfruit.ecommerce.cart.post.remove';
}
