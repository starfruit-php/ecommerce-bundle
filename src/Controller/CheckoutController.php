<?php

namespace Starfruit\EcommerceBundle\Controller;

use App\Form\DeliveryAddressFormType;
use Pimcore\Bundle\EcommerceFrameworkBundle\Factory;
use Pimcore\Model\DataObject\OnlineShopOrder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CheckoutController extends BaseController
{
    /**
     * @Route("/checkout/address", name="stf-checkout-address")
     *
     * @param Factory $factory
     * @param Request $request
     * @param Factory $ecommerceFactory
     *
     * @return Response|RedirectResponse
     */
    public function checkoutAddressAction(Factory $factory, Request $request, Factory $ecommerceFactory)
    {
        $cartManager = $factory->getCartManager();
        $cart = $cartManager->getOrCreateCartByName('cart');

        $checkoutManager = $factory->getCheckoutManager($cart);

        $deliveryAddress = $checkoutManager->getCheckoutStep('deliveryaddress');
        $deliveryAddressDataArray = $this->fillDeliveryAddressFromCustomer($deliveryAddress->getData());

        $form = $this->createForm(DeliveryAddressFormType::class, $deliveryAddressDataArray, []);
        $form->handleRequest($request);

        if ($request->getMethod() == Request::METHOD_POST) {
            $address = new \stdClass();

            $formData = $form->getData();
            foreach ($formData as $key => $value) {
                $address->{$key} = $value;
            }

            // save address if we have no errors
            if (count($form->getErrors()) === 0) {
                // commit step
                $checkoutManager->commitStep($deliveryAddress, $address);

                //TODO remove this - only needed, because one step only is not supported by the framework right now
                $confirm = $checkoutManager->getCheckoutStep('confirm');
                $checkoutManager->commitStep($confirm, true);

                return $this->redirectToRoute('shop-checkout-payment');
            }
        }

        $trackingManager = $ecommerceFactory->getTrackingManager();
        $trackingManager->trackCheckoutStep($deliveryAddress, $cart, 1);

        return $this->render('checkout/checkout_address.html.twig', [
            'cart' => $cart,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param mixed $deliveryAddress
     *
     * @return array|null
     */
    protected function fillDeliveryAddressFromCustomer($deliveryAddress)
    {
        $user = $this->getUser();

        $deliveryAddress = (array) $deliveryAddress;

        if ($user) {
            if ($deliveryAddress === null) {
                $deliveryAddress = [];
            }

            $params = ['email', 'firstname', 'lastname', 'street', 'zip', 'city', 'countryCode'];
            foreach ($params as $param) {
                if (empty($deliveryAddress[$param])) {
                    $deliveryAddress[$param] = $user->{'get' . ucfirst($param)}();
                }
            }
        }

        return $deliveryAddress;
    }

    /**
     * @Route("/checkout/completed", name="shop-checkout-completed")
     *
     * @param Factory $ecommerceFactory
     *
     * @return Response
     */
    public function checkoutCompletedAction(Request $request, Factory $ecommerceFactory)
    {
        $orderId = $request->getSession()->get('last_order_id');

        $order = OnlineShopOrder::getById($orderId);

        $trackingManager = $ecommerceFactory->getTrackingManager();
        $trackingManager->trackCheckoutComplete($order);

        return $this->render('checkout/checkout_completed.html.twig', [
            'order' => $order,
            'hideBreadcrumbs' => true
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function confirmationMailAction(Request $request)
    {
        $order = $request->get('order');

        if ($request->get('order-id')) {
            $order = OnlineShopOrder::getById($request->get('order-id'));
        }

        return $this->render('checkout/confirmation_mail.html.twig', [
            'order' => $order,
            'ordernumber' => $request->get('ordernumber')
        ]);
    }
}
