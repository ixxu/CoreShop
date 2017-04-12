<?php

namespace CoreShop\Bundle\CoreBundle\Checkout\Step;

use CoreShop\Bundle\CoreBundle\Form\Type\Checkout\CarrierType;
use CoreShop\Bundle\CurrencyBundle\Formatter\MoneyFormatterInterface;
use CoreShop\Bundle\ShippingBundle\Calculator\CarrierPriceCalculatorInterface;
use CoreShop\Bundle\ShippingBundle\Processor\CartCarrierProcessorInterface;
use CoreShop\Component\Core\Model\CarrierInterface;
use CoreShop\Component\Currency\Context\CurrencyContextInterface;
use CoreShop\Component\Order\Checkout\CheckoutException;
use CoreShop\Component\Order\Checkout\CheckoutStepInterface;
use CoreShop\Component\Order\Model\CartInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Valid;

class ShippingCheckoutStep implements CheckoutStepInterface
{
    /**
     * @var CartCarrierProcessorInterface
     */
    private $cartCarrierProcessor;

    /**
     * @var CarrierPriceCalculatorInterface
     */
    private $carrierPriceCalculator;
    
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var CurrencyContextInterface
     */
    private $currencyContext;

    /**
     * @var MoneyFormatterInterface
     */
    private $moneyFormatter;

    /**
     * @param CartCarrierProcessorInterface $cartCarrierProcessor
     * @param CarrierPriceCalculatorInterface $carrierPriceCalculator
     * @param FormFactoryInterface $formFactory
     * @param CurrencyContextInterface $currencyContext
     * @param MoneyFormatterInterface $moneyFormatter
     */
    public function __construct(
        CartCarrierProcessorInterface $cartCarrierProcessor,
        CarrierPriceCalculatorInterface $carrierPriceCalculator,
        FormFactoryInterface $formFactory,
        CurrencyContextInterface $currencyContext,
        MoneyFormatterInterface $moneyFormatter
    )
    {
        $this->cartCarrierProcessor = $cartCarrierProcessor;
        $this->carrierPriceCalculator = $carrierPriceCalculator;
        $this->formFactory = $formFactory;
        $this->currencyContext = $currencyContext;
        $this->moneyFormatter = $moneyFormatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'shipping';
    }

    /**
     * {@inheritdoc}
     */
    public function doAutoForward()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(CartInterface $cart)
    {
        return $cart->getCarrier() instanceof CarrierInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function commitStep(CartInterface $cart, Request $request)
    {
        $form = $this->createForm($this->getCarriers($cart));

        $form->handleRequest($request);
        $formData = $form->getData();

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $cart->setCarrier($formData['carrier']->carrier);
                $cart->save();

                return true;
            }
            else {
                throw new CheckoutException('Address Form is invalid', 'coreshop_checkout_shipping_form_invalid');
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareStep(CartInterface $cart)
    {
        //Get Carriers
        $carriers = $this->getCarriers($cart);

        return [
            'carriers' => $carriers,
            'form' => $this->createForm($carriers)->createView()
        ];
    }

    /**
     * @param CartInterface $cart
     * @return array
     */
    private function getCarriers(CartInterface $cart) {
        $carriers = $this->cartCarrierProcessor->getCarriersForCart($cart, $cart->getShippingAddress());
        $availableCarriers = [];

        foreach ($carriers as $carrier) {
            $carrierPrice = $this->carrierPriceCalculator->getPrice($carrier, $cart, $cart->getShippingAddress());

            $availableCarriers[$carrier->getId()] = new \stdClass();
            $availableCarriers[$carrier->getId()]->carrier = $carrier;
            $availableCarriers[$carrier->getId()]->price = $carrierPrice;
        }

        return $availableCarriers;
    }

    /**
     * @param $carriers
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createForm($carriers) {
        $form = $this->formFactory->createNamed('', FormType::class);

        $form->add('carrier', ChoiceType::class, [
            'constraints' => [new Valid()],
            'choices' => $carriers,
            'expanded' => true,
            'choice_label' => function($carrier) {
                return $carrier->carrier->getLabel() . " " . $this->moneyFormatter->format($carrier->price, $this->currencyContext->getCurrency()->getIsoCode());
            }
        ]);

        return $form;
    }
}