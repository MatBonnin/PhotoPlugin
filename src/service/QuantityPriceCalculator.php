<?php

namespace Sylius\Plugin\PhotoPlugin\service;

use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Pricing\Calculator\ProductVariantPriceCalculator as BasePriceCalculator;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Exception\ChannelPricingNotFoundException;
use Sylius\Component\Core\Calculator\ProductVariantPriceCalculatorInterface;
use Sylius\Component\Order\Context\CartContextInterface;

class QuantityPriceCalculator implements ProductVariantPriceCalculatorInterface
{
    private $cartContext;

    public function __construct(CartContextInterface $cartContext)
    {
        $this->cartContext = $cartContext;
    }

    public function calculate(ProductVariantInterface $productVariant, array $context): int
    {
        /** @var ChannelInterface $channel */
        $channel = $context['channel'] ?? $this->cartContext->getChannel();
        $quantity = $context['quantity'] ?? 1;

        $channelPricing = $productVariant->getChannelPricingForChannel($channel);

        if (null === $channelPricing) {
            throw new ChannelPricingNotFoundException($productVariant, $channel);
        }

        // Récupérer les prix par quantité
        $quantityPrices = $productVariant->getQuantityPrices();

        // Trouver le prix correspondant à la quantité
        foreach ($quantityPrices as $quantityPrice) {
            $min = $quantityPrice->getMinQuantity();
            $max = $quantityPrice->getMaxQuantity();

            if ($quantity >= $min && ($max === null || $quantity <= $max)) {
                return $quantityPrice->getPrice();
            }
        }

        // Retourner le prix par défaut s'il n'y a pas de correspondance
        return $channelPricing->getPrice();
    }
}
