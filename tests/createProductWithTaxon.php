<?php

namespace App\Service;



use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Component\Taxonomy\Factory\TaxonFactoryInterface;
use Sylius\Component\Taxonomy\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Resource\Factory\FactoryInterface;

class ProductCreationService
{
    private ProductFactoryInterface $productFactory;
    private TaxonFactoryInterface $taxonFactory;
    private FactoryInterface $productTaxonFactory;
    private TaxonRepositoryInterface $taxonRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ProductFactoryInterface $productFactory,
        TaxonFactoryInterface $taxonFactory,
        FactoryInterface $productTaxonFactory,
        TaxonRepositoryInterface $taxonRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->productFactory = $productFactory;
        $this->taxonFactory = $taxonFactory;
        $this->productTaxonFactory = $productTaxonFactory;
        $this->taxonRepository = $taxonRepository;
        $this->entityManager = $entityManager;
    }

    public function createProductWithTaxon(): void
    {
        /** @var ProductInterface $product */
        $product = $this->productFactory->createNew();
        $product->setCode('product_test');
        $product->setName('Test');

        /** @var TaxonInterface $taxon */
        $taxon = $this->taxonFactory->createNew();
        $taxon->setCode('food');
        $taxon->setName('Food');

        $this->taxonRepository->add($taxon);

        /** @var ProductTaxonInterface $productTaxon */
        $productTaxon = $this->productTaxonFactory->createNew();
        $productTaxon->setTaxon($taxon);
        $productTaxon->setProduct($product);

        $product->addProductTaxon($productTaxon);

        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }
}
