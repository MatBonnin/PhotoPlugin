<?php

namespace Sylius\Plugin\PhotoPlugin\Controller;

use App\Entity\Product\ProductTaxon;
use App\Entity\Product\Product;
use App\Entity\Product\ProductImage;
use App\Entity\Product\ProductVariant;
use App\Entity\Product\ProductOptionValue;
use App\Entity\Channel\ChannelPricing;
use Sylius\Plugin\PhotoPlugin\Entity\Event;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\Component\Product\Repository\ProductOptionRepositoryInterface;
use Sylius\Component\Taxonomy\Model\TaxonInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sylius\Plugin\PhotoPlugin\Form\Type\MassPhotoUploadType;

class PhotoController extends AbstractController
{
    /**
     * Gère l'upload de photos et la création des produits avec variantes.
     */
    public function upload(
        Request $request,
        EntityManagerInterface $em,
        ChannelRepositoryInterface $channelRepository,
        ProductOptionRepositoryInterface $productOptionRepository
    ): Response {
        $form = $this->createForm(MassPhotoUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Event $event */
            $event = $form->get('event')->getData();
            /** @var UploadedFile[] $photos */
            $photos = $form->get('photos')->getData();

            // Génération du slug pour l'événement
            $slugify = new Slugify();
            $eventSlug = $slugify->slugify($event->getName());

            // Gestion du répertoire d'upload
            $uploadDir = $this->getUploadDir($eventSlug);
            $this->createDirectoryIfNotExists($uploadDir);

            // Récupération des entités nécessaires
            $photosTaxon = $this->getTaxon($em, 'PHOTOS');
            $channel = $this->getChannel($channelRepository, 'FASHION_WEB');
            $productOptions = $this->getProductOptions($productOptionRepository);

            // Assurer que les valeurs d'options par défaut existent
            $this->ensureDefaultOptionValues($em, $productOptions);

            // Récupérer les valeurs d'options par défaut
            $defaultOptionValues = $this->getDefaultOptionValues($em, $productOptions);

            // Définir les données des variantes
            $variantsData = [
                // Photos Digitales / Numériques
                [
                    'name_suffix' => 'Fichier numérique basse résolution 2000PX',
                    'price' => 1100, // 11,00 € en centimes
                    'options' => [
                        'product_type' => 'digital',
                        'digital_option' => 'low_res',
                    ],
                ],
                [
                    'name_suffix' => 'Fichier numérique HD ≥ 5000PX',
                    'price' => 1800, // 18,00 € en centimes
                    'options' => [
                        'product_type' => 'digital',
                        'digital_option' => 'high_res',
                    ],
                ],
                [
                    'name_suffix' => 'Fichier numérique HD ≥ 5000PX avec incrustation de la borne du Col',
                    'price' => 2200, // 22,00 € en centimes
                    'options' => [
                        'product_type' => 'digital',
                        'digital_option' => 'high_res_with_incrustation_prin',
                    ],
                ],

                // Tirages Papiers (250gr/m² Lustré)
                [
                    'name_suffix' => 'Tirage Papier 15x20 cm',
                    'price' => 1300, // 13,00 € en centimes
                    'options' => [
                        'product_type' => 'print',
                        'print_size' => '15x20'
                    ],
                ],
                [
                    'name_suffix' => 'Tirage Papier 15x20 cm avec incrustation de la borne du Col',
                    'price' => 1700, // 17,00 € en centimes
                    'options' => [
                        'product_type' => 'print',
                        'print_size' => '15x20',
                        'print_option' => 'with_incrustation_print',
                    ],
                ],
                [
                    'name_suffix' => 'Tirage Papier 15x21 cm présenté dans un cartonnage',
                    'price' => 1700, // 17,00 € en centimes
                    'options' => [
                        'product_type' => 'print',
                        'print_size' => '15x21',
                        'print_option' => 'with_cartonnage',
                    ],
                ],
                [
                    'name_suffix' => 'Tirage Papier 18x24 cm',
                    'price' => 1500, // 15,00 € en centimes
                    'options' => [
                        'product_type' => 'print',
                        'print_size' => '18x24'
                    ],
                ],
                [
                    'name_suffix' => 'Tirage Papier 18x24 cm avec incrustation de la borne du Col',
                    'price' => 1900, // 19,00 € en centimes
                    'options' => [
                        'product_type' => 'print',
                        'print_size' => '18x24',
                        'print_option' => 'with_incrustation_print'
                    ],
                ],
                [
                    'name_suffix' => 'Tirage Papier 18x24 cm présenté dans un cartonnage',
                    'price' => 1900, // 19,00 € en centimes
                    'options' => [
                        'product_type' => 'print',
                        'print_size' => '18x24',
                        'print_option' => 'with_cartonnage'
                    ],
                ],
                [
                    'name_suffix' => 'Tirage Papier 20x30 cm',
                    'price' => 1800, // 18,00 € en centimes
                    'options' => [
                        'product_type' => 'print',
                        'print_size' => '20x30'
                    ],
                ],
                [
                    'name_suffix' => 'Tirage Papier 20X30 cm titré avec incrustation de la borne du Col',
                    'price' => 2200, // 22,00 € en centimes
                    'options' => [
                        'product_type' => 'print',
                        'print_size' => '20x30',
                        'print_option' => 'with_incrustation_print',
                    ],
                ],
                [
                    'name_suffix' => 'Tirage Papier 30x40 cm (Fichier HD offert)',
                    'price' => 3900, // 39,00 € en centimes
                    'options' => [
                        'product_type' => 'print',
                        'print_size' => '30x40',
                    ],
                ],
                [
                    'name_suffix' => 'Tirage Papier 30x40 cm titré avec incrustation de la borne du Col (Fichier HD offert)',
                    'price' => 4300, // 43,00 € en centimes
                    'options' => [
                        'product_type' => 'print',
                        'print_size' => '30x40',
                        'print_option' => 'with_incrustation_print',
                    ],
                ],
                [
                    'name_suffix' => 'Tirage Papier 40x60 cm (Fichier HD offert)',
                    'price' => 4500, // 45,00 € en centimes
                    'options' => [
                        'product_type' => 'print',
                        'print_size' => '40x60',
                    ],
                ],
                [
                    'name_suffix' => 'Tirage Papier 40x60 cm titré avec incrustation de la borne du Col (Fichier HD offert)',
                    'price' => 4900, // 49,00 € en centimes
                    'options' => [
                        'product_type' => 'print',
                        'print_size' => '40x60',
                        'print_option' => 'with_incrustation_print',
                    ],
                ],
                [
                    'name_suffix' => 'Tirage Papier 50x75 cm (Fichier HD offert)',
                    'price' => 5500, // 55,00 € en centimes
                    'options' => [
                        'product_type' => 'print',
                        'print_size' => '50x75',
                    ],
                ],
                [
                    'name_suffix' => 'Tirage Papier 50x75 cm titré avec incrustation de la borne du Col (Fichier HD offert)',
                    'price' => 5900, // 59,00 € en centimes
                    'options' => [
                        'product_type' => 'print',
                        'print_size' => '50x75',
                        'print_option' => 'with_incrustation_print',
                    ],
                ],
                [
                    'name_suffix' => 'Tirage Papier 60x90 cm (Fichier HD offert)',
                    'price' => 7500, // 75,00 € en centimes
                    'options' => [
                        'product_type' => 'print',
                        'print_size' => '60x90',
                    ],
                ],
                [
                    'name_suffix' => 'Tirage Papier 60x90 cm avec incrustation de la borne du Col (Fichier HD offert)',
                    'price' => 7900, // 79,00 € en centimes
                    'options' => [
                        'product_type' => 'print',
                        'print_size' => '60x90',
                        'print_option' => 'with_incrustation_print',
                    ],
                ],

                // Photos Supports Rigides (Aluminium 2 mm)(Produit fini Prêt à mettre au mur)
                [
                    'name_suffix' => 'Photo sur Plaque 15x20 cm en Aluminium',
                    'price' => 2500, // 25,00 € en centimes
                    'options' => [
                        'product_type' => 'rigid_support',
                        'rigid_size' => '15x20r',
                        'rigid_option' => 'standard',
                    ],
                ],
                [
                    'name_suffix' => 'Photo sur Plaque 18x24 cm en Aluminium',
                    'price' => 2900, // 29,00 € en centimes
                    'options' => [
                        'product_type' => 'rigid_support',
                        'rigid_size' => '18x24r',
                        'rigid_option' => 'standard',
                    ],
                ],
                [
                    'name_suffix' => 'Photo sur Plaque 20x30 cm en Aluminium',
                    'price' => 3500, // 35,00 € en centimes
                    'options' => [
                        'product_type' => 'rigid_support',
                        'rigid_size' => '20x30r',
                        'rigid_option' => 'standard',
                    ],
                ],
                [
                    'name_suffix' => 'Photo personnalisée Magazine sur Plaque 20X30 cm en Aluminium',
                    'price' => 4000, // 40,00 € en centimes
                    'options' => [
                        'product_type' => 'rigid_support',
                        'rigid_size' => '20x30r',
                        'rigid_option' => 'magazine',
                    ],
                ],
                [
                    'name_suffix' => 'Photo sur Plaque 30X40 cm en Aluminium',
                    'price' => 5900, // 59,00 € en centimes
                    'options' => [
                        'product_type' => 'rigid_support',
                        'rigid_size' => '30x40r',
                        'rigid_option' => 'standard',
                    ],
                ],
                [
                    'name_suffix' => 'Photo personnalisée Magazine sur Plaque 30X40 cm en Aluminium (nouveau !!)',
                    'price' => 6400, // 64,00 € en centimes
                    'options' => [
                        'product_type' => 'rigid_support',
                        'rigid_size' => '30x40r',
                        'rigid_option' => 'magazine',
                    ],
                ],
                [
                    'name_suffix' => 'Photo sur Plaque 40X60 cm en Aluminium',
                    'price' => 7500, // 75,00 € en centimes
                    'options' => [
                        'product_type' => 'rigid_support',
                        'rigid_size' => '40x60r',
                        'rigid_option' => 'standard',
                    ],
                ],
                [
                    'name_suffix' => 'Photo personnalisée Magazine sur Plaque 40x60 cm en Aluminium (nouveau !!)',
                    'price' => 7900, // 79,00 € en centimes
                    'options' => [
                        'product_type' => 'rigid_support',
                        'rigid_size' => '40x60r',
                        'rigid_option' => 'magazine',
                    ],
                ],

                // Photos sur Goodies
                [
                    'name_suffix' => 'Mug céramique avec incrustation de la borne du Col',
                    'price' => 2200, // 22,00 € en centimes
                    'options' => [
                        'product_type' => 'goodies',
                        'goodie_type' => 'mug',
                    ],
                ],
                [
                    'name_suffix' => 'Tapis de souris en néoprène 197x235 mm',
                    'price' => 2200, // 22,00 € en centimes
                    'options' => [
                        'product_type' => 'goodies',
                        'goodie_type' => 'mouse_pad',
                    ],
                ],
                [
                    'name_suffix' => 'Puzzle 120 pièces 190x280 mm',
                    'price' => 2200, // 22,00 € en centimes
                    'options' => [
                        'product_type' => 'goodies',
                        'goodie_type' => 'puzzle',
                    ],
                ],
                [
                    'name_suffix' => 'T-Shirt Taille S/M/L/XL 100% Polyester',
                    'price' => 2400, // 24,00 € en centimes
                    'options' => [
                        'product_type' => 'goodies',
                        'goodie_type' => 't_shirt',
                    ],
                ],
            ];

            foreach ($photos as $photo) {
                // Création du produit
                $product = $this->createProduct($event, $photo, $eventSlug);

                // Association au taxon et au canal
                $this->associateTaxonAndChannel($product, $photosTaxon, $channel);

                // Association des options au produit
                $this->associateOptionsToProduct($product, $productOptions);

                // Génération des variantes
                $this->generateVariants($product, $variantsData, $productOptions, $defaultOptionValues, $channel, $em);

                // Persistance du produit
                $em->persist($product);
            }

            // Flush final pour sauvegarder toutes les entités
            $em->flush();

            $this->addFlash('success', 'Photos uploadées avec succès !');

            return $this->redirectToRoute('photographer_dashboard');
        }

        return $this->render('@PhotoPlugin/photographer/upload.html.twig', [
            'form' => $form->createView(),
        ]);
        // ---------------------- Méthodes Privées ----------------------



    }
    /**
     * Obtient le répertoire d'upload basé sur le slug de l'événement.
     */
    private function getUploadDir(string $eventSlug): string
    {
        return sprintf('%s/public/media/image/%s', $this->getParameter('kernel.project_dir'), $eventSlug);
    }

    /**
     * Crée le répertoire d'upload s'il n'existe pas.
     */
    private function createDirectoryIfNotExists(string $directory): void
    {
        $filesystem = new Filesystem();
        if (!$filesystem->exists($directory)) {
            $filesystem->mkdir($directory, 0755);
        }
    }

    /**
     * Récupère le taxon spécifié.
     */
    private function getTaxon(EntityManagerInterface $em, string $code): TaxonInterface
    {
        $taxon = $em->getRepository(TaxonInterface::class)->findOneBy(['code' => $code]);
        if (!$taxon) {
            throw new \Exception(sprintf('Le taxon "%s" n\'a pas été trouvé.', $code));
        }
        return $taxon;
    }

    /**
     * Récupère le canal spécifié.
     */
    private function getChannel(ChannelRepositoryInterface $channelRepository, string $code): ChannelInterface
    {
        $channel = $channelRepository->findOneByCode($code);
        if (!$channel) {
            throw new \Exception(sprintf('Le canal "%s" n\'a pas été trouvé.', $code));
        }
        return $channel;
    }

    /**
     * Récupère les options de produit nécessaires.
     */
    private function getProductOptions(ProductOptionRepositoryInterface $productOptionRepository): array
    {
        $optionCodes = [
            'product_type',
            'digital_option',
            'print_size',
            'print_option',
            'rigid_size',
            'rigid_option',
            'goodie_type',
        ];

        $productOptions = [];
        foreach ($optionCodes as $code) {
            $option = $productOptionRepository->findOneBy(['code' => $code]);
            if (!$option) {
                throw new \Exception(sprintf('L\'option de produit "%s" n\'a pas été trouvée.', $code));
            }
            $productOptions[$code] = $option;
        }

        return $productOptions;
    }

    /**
     * S'assure que chaque option de produit possède une valeur par défaut.
     */
    private function ensureDefaultOptionValues(EntityManagerInterface $em, array $productOptions): void
    {
        foreach ($productOptions as $optionCode => $option) {
            $defaultValueCode = 'default_' . $optionCode;
            $defaultValueLabel = '-- Veuillez sélectionner --';

            $defaultOptionValue = $this->getOptionValueByCode($option, $defaultValueCode);
            if (!$defaultOptionValue) {
                // Créer la valeur d'option par défaut
                $defaultOptionValue = new ProductOptionValue();
                $defaultOptionValue->setCurrentLocale('fr_FR');
                $defaultOptionValue->setFallbackLocale('fr_FR');
                $defaultOptionValue->setCode($defaultValueCode);
                $defaultOptionValue->setValue($defaultValueLabel);
                $defaultOptionValue->setOption($option);

                $option->addValue($defaultOptionValue);
                $em->persist($defaultOptionValue);
                $em->persist($option);
            }
        }
    }

    /**
     * Récupère les valeurs par défaut pour chaque option de produit.
     */
    private function getDefaultOptionValues(EntityManagerInterface $em, array $productOptions): array
    {
        $defaultOptionValues = [];
        foreach ($productOptions as $optionCode => $option) {
            $defaultValueCode = 'default_' . $optionCode;
            $defaultOptionValue = $this->getOptionValueByCode($option, $defaultValueCode);
            if (!$defaultOptionValue) {
                throw new \Exception("La valeur d'option par défaut pour l'option '$optionCode' n'a pas été trouvée.");
            }
            $defaultOptionValues[$optionCode] = $defaultOptionValue;
        }
        return $defaultOptionValues;
    }

    /**
     * Génère les variantes du produit en fonction des données fournies.
     *
     * @param Product $product
     * @param array $variantsData
     * @param array $productOptions
     * @param array $defaultOptionValues
     * @param ChannelInterface $channel
     * @param EntityManagerInterface $em
     */
    private function generateVariants(
        Product $product,
        array $variantsData,
        array $productOptions,
        array $defaultOptionValues,
        ChannelInterface $channel,
        EntityManagerInterface $em
    ): void {
        foreach ($variantsData as $variantData) {
            $variant = new ProductVariant();
            $variant->setCurrentLocale('fr_FR');
            $variant->setName($product->getName() . ' - ' . $variantData['name_suffix']);
            $variant->setCode(uniqid('VARIANT_'));
            $variant->setOnHand(9999); // Quantité disponible

            // Récupérer les options spécifiées pour cette variante
            $specifiedOptions = $variantData['options'];

            foreach ($specifiedOptions as $optionCode => $valueCode) {
                if (!isset($productOptions[$optionCode])) {
                    throw new \Exception("L'option de produit '$optionCode' n'existe pas.");
                }

                $option = $productOptions[$optionCode];
                $optionValue = $this->getOptionValueByCode($option, $valueCode);

                if (!$optionValue) {
                    throw new \Exception("La valeur d'option '$valueCode' pour l'option '$optionCode' n'a pas été trouvée.");
                }

                $variant->addOptionValue($optionValue);
            }

            // Déterminer les options non spécifiées et leur attribuer les valeurs par défaut
            $allOptionCodes = array_keys($productOptions);
            $unspecifiedOptions = array_diff($allOptionCodes, array_keys($specifiedOptions));

            foreach ($unspecifiedOptions as $optionCode) {
                if (!isset($defaultOptionValues[$optionCode])) {
                    throw new \Exception("La valeur d'option par défaut pour l'option '$optionCode' n'est pas définie.");
                }
                $defaultOptionValue = $defaultOptionValues[$optionCode];
                $variant->addOptionValue($defaultOptionValue);
            }

            // Définir le prix
            $price = $variantData['price'];
            $channelPricing = new ChannelPricing();
            $channelPricing->setChannelCode($channel->getCode());
            $channelPricing->setPrice($price); // Prix en centimes
            $variant->addChannelPricing($channelPricing);

            // Ajouter la variante au produit
            $product->addVariant($variant);
        }
    }

    /**
     * Crée un produit avec ses images.
     *
     * @param Event $event
     * @param UploadedFile $photo
     * @param string $eventSlug
     * @return Product
     */
    private function createProduct(Event $event, UploadedFile $photo, string $eventSlug): Product
    {
        $slugify = new Slugify();
        $product = new Product();
        $product->setCurrentLocale('fr_FR');
        $product->setFallbackLocale('fr_FR');

        $productName = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
        $product->setName($productName);
        $product->setCode(uniqid('PHOTO_'));
        $product->setPhotographer($this->getUser());
        $product->setEvent($event);
        $product->setEnabled(true);

        // Définir la méthode de sélection de variante sur 'match'
        $product->setVariantSelectionMethod(ProductInterface::VARIANT_SELECTION_MATCH);

        // Descriptions
        $product->setShortDescription('Photo de l’événement ' . $event->getName());
        $product->setDescription('Cette photo a été prise lors de l’événement ' . $event->getName() . '.');

        // Générer un slug unique
        $slug = $slugify->slugify($productName . '-' . uniqid());
        $product->setSlug($slug);

        // Déplacer le fichier
        $newFileName = bin2hex(random_bytes(8)) . '.' . $photo->guessExtension();
        $photo->move($this->getUploadDir($eventSlug), $newFileName);

        // Créer l'image du produit
        $productImage = new ProductImage();
        $productImage->setPath(sprintf('%s/%s', $eventSlug, $newFileName)); // Chemin relatif à "media/image"
        $productImage->setType('main');
        $product->addImage($productImage);

        return $product;
    }

    /**
     * Associe le produit au taxon et au canal.
     *
     * @param Product $product
     * @param TaxonInterface $taxon
     * @param ChannelInterface $channel
     */
    private function associateTaxonAndChannel(Product $product, TaxonInterface $taxon, ChannelInterface $channel): void
    {
        // Associer le produit au taxon
        $productTaxon = new ProductTaxon();
        $productTaxon->setProduct($product);
        $productTaxon->setTaxon($taxon);
        $productTaxon->setPosition(0); // Optionnel
        $product->addProductTaxon($productTaxon);

        // Associer le produit au canal
        $product->addChannel($channel);
    }

    /**
     * Associe les options de produit au produit.
     *
     * @param Product $product
     * @param array $productOptions
     */
    private function associateOptionsToProduct(Product $product, array $productOptions): void
    {
        foreach ($productOptions as $option) {
            $product->addOption($option);
        }
    }

    /**
     * Récupère une valeur d'option de produit par son code.
     *
     * @param ProductOptionInterface $option
     * @param string $valueCode
     * @return ProductOptionValueInterface|null
     */
    private function getOptionValueByCode(ProductOptionInterface $option, string $valueCode): ?ProductOptionValueInterface
    {
        foreach ($option->getValues() as $value) {
            if ($value->getCode() === $valueCode) {
                return $value;
            }
        }
        return null;
    }
}
