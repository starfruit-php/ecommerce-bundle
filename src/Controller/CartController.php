<?php

namespace Starfruit\EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Pimcore\Translation\Translator;
use Pimcore\Bundle\EcommerceFrameworkBundle\Factory;
use Pimcore\Bundle\EcommerceFrameworkBundle\CartManager\CartInterface;
use Pimcore\Bundle\EcommerceFrameworkBundle\Model\ProductInterface;
use Pimcore\Bundle\EcommerceFrameworkBundle\Model\CheckoutableInterface;
use Starfruit\EcommerceBundle\Model\Product\AbstractProduct;
use Starfruit\EcommerceBundle\Event\CartEvents;
use Starfruit\EcommerceBundle\Event\Model\CartEvent;

class CartController extends BaseController
{
    const MAX_CART_ITEMS = 99;

    /**
     * @var Factory
     */
    protected $factory;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @return CartInterface
     */
    protected function getCart()
    {
        $cartManager = $this->factory->getCartManager();

        return $cartManager->getOrCreateCartByName(self::DEFAULT_CART_NAME);
    }

    private function renderCart()
    {
        return $this->redirectToRoute('stf-cart');
    }

    /**
     * @Route("/cart/add-to-cart", name="stf-cart-add-to", methods={"POST"})
     *
     * @throws \Exception
     */
    public function addToCartAction(Request $request, Factory $ecommerceFactory): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('addToCart', $request->request->get('_csrf_token'))) {
            throw new \Exception('Invalid request');
        }

        $id = (int) $request->get('id');
        $product = AbstractProduct::getById($id);

        if (null === $product) {
            return $this->renderCart();
        }

        $cart = $this->getCart();
        if ($cart->getItemCount() > self::MAX_CART_ITEMS) {
            return $this->renderCart();
        }

        $amount = $request->request->getInt('amount');

        // preAddToCartEvent
        $preAddToCartEvent = new CartEvent($request);
        \Pimcore::getEventDispatcher()->dispatch($preAddToCartEvent, CartEvents::PRE_ADD);

        $cart->addItem($product, $amount);
        $cart->save();

        // postAddToCartEvent
        $postAddToCartEvent = new CartEvent($request);
        \Pimcore::getEventDispatcher()->dispatch($postAddToCartEvent, CartEvents::POST_ADD);

        return $this->renderCart();
    }

    /**
     * @Route("/cart", name="stf-cart")
     *
     * @param Request $request
     * @param Factory $ecommerceFactory
     *
     * @return Response
     */
    public function cartListingAction(Request $request, Factory $ecommerceFactory)
    {
        $cart = $this->getCart();

        if ($request->getMethod() == Request::METHOD_POST) {
            if (!$this->isCsrfTokenValid('cartListing', $request->get('_csrf_token'))) {
                throw new \Exception('Invalid request');
            }

            $items = $request->get('items');

            // preUpdateCartEvent
            $preUpdateCartEvent = new CartEvent($request);
            \Pimcore::getEventDispatcher()->dispatch($preUpdateCartEvent, CartEvents::PRE_UPDATE);

            foreach ($items as $itemKey => $quantity) {
                if (!is_numeric($quantity)) {
                    continue;
                }

                if ($cart->getItemCount() > self::MAX_CART_ITEMS) {
                    break;
                }

                $product = AbstractProduct::getById($itemKey);
                if ($product instanceof CheckoutableInterface) {
                    $cart->updateItem($itemKey, $product, floor($quantity), true);
                }
            }
            $cart->save();

            // postUpdateCartEvent
            $postUpdateCartEvent = new CartEvent($request);
            \Pimcore::getEventDispatcher()->dispatch($postUpdateCartEvent, CartEvents::POST_UPDATE);
        }

        $params = array_merge($request->request->all(), $request->query->all());

        return $this->render($this->getCartDefaultView(), array_merge($params, ['cart' => $cart]));
    }

    /**
     * @Route("/cart/remove-from-cart", name="stf-cart-remove-from", methods={"POST"})
     */
    public function removeFromCartAction(Request $request, Factory $ecommerceFactory): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('removeFromCart', $request->request->get('_csrf_token'))) {
            throw new \Exception('Invalid request');
        }

        // preRemoveFromCartEvent
        $preRemoveFromCartEvent = new CartEvent($request);
        \Pimcore::getEventDispatcher()->dispatch($preRemoveFromCartEvent, CartEvents::PRE_REMOVE);

        $id = (int) $request->get('id');
        $product = AbstractProduct::getById($id);

        $cart = $this->getCart();
        $cart->removeItem($id);
        $cart->save();

        if ($product instanceof ProductInterface) {
            // postRemoveFromCartEvent
            $postRemoveFromCartEvent = new CartEvent($request);
            \Pimcore::getEventDispatcher()->dispatch($postRemoveFromCartEvent, CartEvents::POST_REMOVE);
        }

        return $this->renderCart();
    }

    /**
     * @Route("/cart/apply-voucher", name="stf-cart-apply-voucher")
     *
     * @param Request $request
     * @param Translator $translator
     * @param Factory $ecommerceFactory
     *
     * @return RedirectResponse
     *
     * @throws \Exception
     */
    public function applyVoucherAction(Request $request, Translator $translator, Factory $ecommerceFactory)
    {
        if ($token = strip_tags($request->get('voucher-code'))) {
            $cart = $this->getCart();

            try {
                $success = $cart->addVoucherToken($token);
                if ($success) {
                    $this->addFlash('success', $translator->trans('cart.voucher-code-added'));

                    $trackingManager = $ecommerceFactory->getTrackingManager();
                    $trackingManager->trackCartUpdate($cart);
                } else {
                    $this->addFlash('danger', $translator->trans('cart.voucher-code-could-not-be-added'));
                }
            } catch (VoucherServiceException $e) {
                $this->addFlash('danger', $translator->trans('cart.error-voucher-code-' . $e->getCode()));
            }
        } else {
            $this->addFlash('danger', $translator->trans('cart.empty-voucher-code'));
        }

        return $this->renderCart();
    }

    /**
     * @Route("/cart/remove-voucher", name="stf-cart-remove-voucher")
     *
     * @param Request $request
     * @param Translator $translator
     * @param Factory $ecommerceFactory
     *
     * @return RedirectResponse
     */
    public function removeVoucherAction(Request $request, Translator $translator, Factory $ecommerceFactory)
    {
        if ($token = strip_tags($request->get('voucher-code'))) {
            $cart = $this->getCart();

            try {
                $cart->removeVoucherToken($token);
                $this->addFlash('success', $translator->trans('cart.voucher-code-removed'));

                $trackingManager = $ecommerceFactory->getTrackingManager();
                $trackingManager->trackCartUpdate($cart);
            } catch (VoucherServiceException $e) {
                $this->addFlash('danger', $translator->trans('cart.error-voucher-code-' . $e->getCode()));
            }
        } else {
            $this->addFlash('danger', $translator->trans('cart.empty-voucher-code'));
        }

        return $this->renderCart();
    }
}
