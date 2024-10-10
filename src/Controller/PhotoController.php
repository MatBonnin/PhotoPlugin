<?php

namespace Sylius\Plugin\PhotoPlugin\Controller;

use App\Entity\Product\ProductTaxon;
use Sylius\Component\Taxonomy\Model\TaxonInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sylius\Plugin\PhotoPlugin\Form\Type\MassPhotoUploadType;
use App\Entity\Product\Product;
use App\Entity\Product\ProductImage;
use App\Entity\Product\ProductVariant;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

// Importations nécessaires
use Sylius\Component\Core\Model\ChannelInterface;
use App\Entity\Channel\ChannelPricing;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Plugin\PhotoPlugin\Entity\QuantityPrice;

class PhotoController extends AbstractController
{
    public function upload(Request $request, EntityManagerInterface $em, ChannelRepositoryInterface $channelRepository)
    {
        $form = $this->createForm(MassPhotoUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->get('event')->getData();
            /** @var UploadedFile[] $photos */
            $photos = $form->get('photos')->getData();

            $slugify = new Slugify();
            $eventSlug = $slugify->slugify($event->getName());

            // Utilisation du Filesystem de Symfony pour gérer les fichiers
            $uploadDir = sprintf('%s/public/media/image/%s', $this->getParameter('kernel.project_dir'), $eventSlug);
            $filesystem = new Filesystem();
            if (!$filesystem->exists($uploadDir)) {
                $filesystem->mkdir($uploadDir, 0755);
            }

            // Récupérer le taxon 'PHOTOS'
            /** @var TaxonInterface|null $photosTaxon */
            $photosTaxon = $em->getRepository(TaxonInterface::class)->findOneBy(['code' => 'PHOTOS']);

            if (!$photosTaxon) {
                throw new \Exception('Le taxon "PHOTOS" n\'a pas été trouvé.');
            }

            // Récupérer le canal 'FASHION_WEB'
            $channel = $channelRepository->findOneByCode('FASHION_WEB');

            if (!$channel) {
                throw new \Exception('Le canal "FASHION_WEB" n\'a pas été trouvé.');
            }

            foreach ($photos as $photo) {
                // Créer un nouveau produit
                $product = new Product();
                $product->setCurrentLocale('fr_FR');
                $product->setFallbackLocale('fr_FR');
                $productName = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                $product->setName($productName);
                $product->setCode(uniqid('PHOTO_'));
                $product->setPhotographer($this->getUser());
                $product->setEvent($event);
                $product->setEnabled(true);

                // Descriptions
                $product->setShortDescription('Photo de l’événement ' . $event->getName());
                $product->setDescription('Cette photo a été prise lors de l’événement ' . $event->getName() . '.');

                // Générer un slug unique
                $slug = $slugify->slugify($productName . '-' . uniqid());
                $product->setSlug($slug);

                // Déplacer le fichier
                $newFileName = bin2hex(random_bytes(8)) . '.' . $photo->guessExtension();
                $photo->move($uploadDir, $newFileName);

                // Créer l'image du produit
                $productImage = new ProductImage();
                $productImage->setPath(sprintf('%s/%s', $eventSlug, $newFileName)); // Chemin relatif à "media/image"
                $productImage->setType('main');
                $product->addImage($productImage);

                // Associer le produit au taxon 'PHOTOS'
                $productTaxon = new ProductTaxon();
                $productTaxon->setProduct($product);
                $productTaxon->setTaxon($photosTaxon);
                $productTaxon->setPosition(0); // Optionnel

                $product->addProductTaxon($productTaxon);

                // Associer le produit au canal
                $product->addChannel($channel);

                // Définir les variantes avec les prix
                $variantsData = [
                    // Photos Digitales / Numériques
                    [
                        'name' => 'Fichier numérique basse résolution 2000PX en téléchargement automatisé et instantané.',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 1100], // 11.00 €
                            ['min' => 2, 'max' => 2, 'price' => 1000], // 10.00 €
                            ['min' => 3, 'max' => 5, 'price' => 900],  // 9.00 €
                            ['min' => 6, 'max' => 8, 'price' => 800],  // 8.00 €
                            ['min' => 9, 'max' => null, 'price' => 700], // 7.00 €
                        ],
                    ],
                    [
                        'name' => 'Fichier numérique HD taille ≥ 5000PX, idéal pour tirages tous formats. Livraison via Wetransfert. Délai 1 à 3 Jours.',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 1800], // 18.00 €
                            ['min' => 2, 'max' => 2, 'price' => 1700], // 17.00 €
                            ['min' => 3, 'max' => 5, 'price' => 1600], // 16.00 €
                            ['min' => 6, 'max' => 8, 'price' => 1500], // 15.00 €
                            ['min' => 9, 'max' => null, 'price' => 1400], // 14.00 €
                        ],
                    ],
                    [
                        'name' => 'Fichier numérique HD taille ≥ 5000PX, avec incrustation de la borne du Col, idéal pour tirages tous formats. Livraison via Wetransfert. Délai 1 à 3 Jours.',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 2200], // 22.00 €
                            ['min' => 2, 'max' => 2, 'price' => 2100], // 21.00 €
                            ['min' => 3, 'max' => 5, 'price' => 2000], // 20.00 €
                            ['min' => 6, 'max' => 8, 'price' => 1900], // 19.00 €
                            ['min' => 9, 'max' => null, 'price' => 1800], // 18.00 €
                        ],
                    ],

                    // Tirages Papiers (250gr/m² Lustré)
                    [
                        'name' => 'Format 15x20 cm',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 1300], // 13.00 €
                            ['min' => 2, 'max' => 2, 'price' => 1200], // 12.00 €
                            ['min' => 3, 'max' => 5, 'price' => 1100], // 11.00 €
                            ['min' => 6, 'max' => 8, 'price' => 1000], // 10.00 €
                            // Pas de prix pour 'Pour 9 et +'
                        ],
                    ],
                    [
                        'name' => 'Format 15X20 cm avec incrustation de la borne du Col.',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 1700], // 17.00 €
                            ['min' => 2, 'max' => 2, 'price' => 1600], // 16.00 €
                            ['min' => 3, 'max' => 5, 'price' => 1500], // 15.00 €
                            ['min' => 6, 'max' => 8, 'price' => 1400], // 14.00 €
                            // Pas de prix pour 'Pour 9 et +'
                        ],
                    ],
                    [
                        'name' => 'Format 15x21 cm présenté dans un cartonnage',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 1700], // 17.00 €
                            ['min' => 2, 'max' => 2, 'price' => 1600], // 16.00 €
                            ['min' => 3, 'max' => 5, 'price' => 1500], // 15.00 €
                            ['min' => 6, 'max' => 8, 'price' => 1400], // 14.00 €
                            // Pas de prix pour 'Pour 9 et +'
                        ],
                    ],
                    [
                        'name' => 'Format 18x24 cm',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 1500], // 15.00 €
                            ['min' => 2, 'max' => 2, 'price' => 1400], // 14.00 €
                            ['min' => 3, 'max' => 5, 'price' => 1300], // 13.00 €
                            ['min' => 6, 'max' => 8, 'price' => 1200], // 12.00 €
                            // Pas de prix pour 'Pour 9 et +'
                        ],
                    ],
                    [
                        'name' => 'Format 18X24 cm avec incrustation de la borne du Col.',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 1900], // 19.00 €
                            ['min' => 2, 'max' => 2, 'price' => 1800], // 18.00 €
                            ['min' => 3, 'max' => 5, 'price' => 1700], // 17.00 €
                            ['min' => 6, 'max' => 8, 'price' => 1600], // 16.00 €
                            // Pas de prix pour 'Pour 9 et +'
                        ],
                    ],
                    [
                        'name' => 'Format 18x24 cm présenté dans un cartonnage',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 1900], // 19.00 €
                            ['min' => 2, 'max' => 2, 'price' => 1800], // 18.00 €
                            ['min' => 3, 'max' => 5, 'price' => 1700], // 17.00 €
                            ['min' => 6, 'max' => 8, 'price' => 1600], // 16.00 €
                            // Pas de prix pour 'Pour 9 et +'
                        ],
                    ],
                    [
                        'name' => 'Format 20x30 cm',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 1800], // 18.00 €
                            ['min' => 2, 'max' => 2, 'price' => 1700], // 17.00 €
                            ['min' => 3, 'max' => 5, 'price' => 1600], // 16.00 €
                            ['min' => 6, 'max' => 8, 'price' => 1500], // 15.00 €
                            // Pas de prix pour 'Pour 9 et +'
                        ],
                    ],
                    [
                        'name' => 'Format 20X30 cm titré avec incrustation de la borne du Col.',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 2200], // 22.00 €
                            ['min' => 2, 'max' => 2, 'price' => 2100], // 21.00 €
                            ['min' => 3, 'max' => 5, 'price' => 2000], // 20.00 €
                            ['min' => 6, 'max' => 8, 'price' => 1900], // 19.00 €
                            ['min' => 9, 'max' => null, 'price' => 1800], // 18.00 €
                        ],
                    ],
                    [
                        'name' => 'Format 30x40 cm (Fichier HD offert)',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 3900], // 39.00 €
                            ['min' => 2, 'max' => 2, 'price' => 3600], // 36.00 €
                            ['min' => 3, 'max' => 5, 'price' => 3300], // 33.00 €
                            // Pas de prix pour 'Pour 6 à 8' et 'Pour 9 et +'
                        ],
                    ],
                    [
                        'name' => 'Format 30x40 cm titré avec incrustation de la borne du Col. (Fichier HD offert)',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 4300], // 43.00 €
                            ['min' => 2, 'max' => 2, 'price' => 4000], // 40.00 €
                            ['min' => 3, 'max' => 5, 'price' => 3700], // 37.00 €
                            // Pas de prix pour 'Pour 6 à 8' et 'Pour 9 et +'
                        ],
                    ],
                    [
                        'name' => 'Format 40x60 cm (Fichier HD offert)',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 4500], // 45.00 €
                            ['min' => 2, 'max' => 2, 'price' => 4200], // 42.00 €
                            ['min' => 3, 'max' => 5, 'price' => 4000], // 40.00 €
                            // Pas de prix pour 'Pour 6 à 8' et 'Pour 9 et +'
                        ],
                    ],
                    [
                        'name' => 'Format 40x60 cm titré avec incrustation de la borne du Col. (Fichier HD offert)',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 4900], // 49.00 €
                            ['min' => 2, 'max' => 2, 'price' => 5900], // 59.00 €
                            ['min' => 3, 'max' => 5, 'price' => 4400], // 44.00 €
                            // Pas de prix pour 'Pour 6 à 8' et 'Pour 9 et +'
                        ],
                    ],
                    [
                        'name' => 'Format 50x75 cm (Fichier HD offert)',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 5500], // 55.00 €
                            ['min' => 2, 'max' => 2, 'price' => 5200], // 52.00 €
                            ['min' => 3, 'max' => 5, 'price' => 5000], // 50.00 €
                            // Pas de prix pour 'Pour 6 à 8' et 'Pour 9 et +'
                        ],
                    ],
                    [
                        'name' => 'Format 50x75 cm titré avec incrustation de la borne du Col. (Fichier HD offert)',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 5900], // 59.00 €
                            ['min' => 2, 'max' => 2, 'price' => 5600], // 56.00 €
                            ['min' => 3, 'max' => 5, 'price' => 5400], // 54.00 €
                            // Pas de prix pour 'Pour 6 à 8' et 'Pour 9 et +'
                        ],
                    ],
                    [
                        'name' => 'Format ﻿60x90 cm (Fichier HD offert)',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 7500], // 75.00 €
                            ['min' => 2, 'max' => 2, 'price' => 7200], // 72.00 €
                            ['min' => 3, 'max' => 5, 'price' => 7000], // 70.00 €
                            // Pas de prix pour 'Pour 6 à 8' et 'Pour 9 et +'
                        ],
                    ],
                    [
                        'name' => 'Format 60x90 cm (Fichier HD offert) avec incrustation de la borne du Col.',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 7900], // 79.00 €
                            ['min' => 2, 'max' => 2, 'price' => 7600], // 76.00 €
                            ['min' => 3, 'max' => 5, 'price' => 7300], // 73.00 €
                            // Pas de prix pour 'Pour 6 à 8' et 'Pour 9 et +'
                        ],
                    ],

                    // Photos Supports Rigides (Aluminium 2 mm)(Produit fini Prêt à mettre au mur)
                    [
                        'name' => 'Photo sur Plaque 15x20 cm en Aluminium',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 2500], // 25.00 €
                            ['min' => 2, 'max' => null, 'price' => 2300], // 23.00 € (Pour 2 et +)
                        ],
                    ],
                    [
                        'name' => 'Photo sur Plaque 18x24 cm en Aluminium',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 2900], // 29.00 €
                            ['min' => 2, 'max' => null, 'price' => 2600], // 26.00 € (Pour 2 et +)
                        ],
                    ],
                    [
                        'name' => 'Photo sur Plaque 20x30 cm en Aluminium',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 3500], // 35.00 €
                            ['min' => 2, 'max' => null, 'price' => 3200], // 32.00 € (Pour 2 et +)
                        ],
                    ],
                    [
                        'name' => 'Photo personnalisée Magazine sur Plaque 20X30 cm en Aluminium',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 4000], // 40.00 €
                            ['min' => 2, 'max' => null, 'price' => 3700], // 37.00 € (Pour 2 et +)
                        ],
                    ],
                    [
                        'name' => 'Photo sur Plaque 30X40 cm en Aluminium',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 5900], // 59.00 €
                            ['min' => 2, 'max' => 2, 'price' => 5500], // 55.00 €
                            // Pas de prix pour 'Pour 3 à 5' etc.
                        ],
                    ],
                    [
                        'name' => 'Photo personnalisée Magazine sur Plaque 30X40 cm en Aluminium (nouveau !!)',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 6400], // 64.00 €
                            ['min' => 2, 'max' => 2, 'price' => 5900], // 59.00 €
                            // Pas de prix pour 'Pour 3 à 5' etc.
                        ],
                    ],
                    [
                        'name' => 'Photo sur Plaque 40X60 cm en Aluminium',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 7500], // 75.00 €
                            ['min' => 2, 'max' => 2, 'price' => 6900], // 69.00 €
                            // Pas de prix pour 'Pour 3 à 5' etc.
                        ],
                    ],
                    [
                        'name' => 'Photo personnalisée Magazine sur Plaque 40x60 cm en Aluminium (nouveau !!)',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 7900], // 79.00 €
                            ['min' => 2, 'max' => 2, 'price' => 7400], // 74.00 €
                            // Pas de prix pour 'Pour 3 à 5' etc.
                        ],
                    ],

                    // Photos sur Goodies
                    [
                        'name' => 'Mug céramique avec incrustation de la borne du Col',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 2200], // 22.00 €
                            ['min' => 2, 'max' => null, 'price' => 1900], // 19.00 € (Pour 2 et +)
                        ],
                    ],
                    [
                        'name' => 'Tapis de souris en néoprène 197x235 mm',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 2200], // 22.00 €
                            ['min' => 2, 'max' => null, 'price' => 1900], // 19.00 € (Pour 2 et +)
                        ],
                    ],
                    [
                        'name' => 'Puzzles 120 pièces 190x280 mm',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 2200], // 22.00 €
                            ['min' => 2, 'max' => null, 'price' => 1900], // 19.00 € (Pour 2 et +)
                        ],
                    ],
                    [
                        'name' => 'T-Shirt Taille S/M/L/XL 100% Polyester',
                        'quantityPrices' => [
                            ['min' => 1, 'max' => 1, 'price' => 2400], // 24.00 €
                            ['min' => 2, 'max' => null, 'price' => 2200], // 22.00 € (Pour 2 et +)
                        ],
                    ],
                ];



                foreach ($variantsData as $variantData) {
                    $productVariant = new ProductVariant();
                    $productVariant->setCurrentLocale('fr_FR');
                    $productVariant->setName($variantData['name']);
                    $productVariant->setCode(uniqid('VARIANT_'));

                    // Stock
                    $productVariant->setOnHand(9999);

                    // Créer un ChannelPricing
                    $channelPricing = new ChannelPricing();
                    $channelPricing->setChannelCode($channel->getCode());
                    $channelPricing->setPrice($variantData['quantityPrices'][0]['price']); // Prix de base

                    $productVariant->addChannelPricing($channelPricing);

                    // Ajouter les QuantityPrices
                    foreach ($variantData['quantityPrices'] as $priceData) {
                        $quantityPrice = new QuantityPrice();
                        $quantityPrice->setMinQuantity($priceData['min']);
                        $quantityPrice->setMaxQuantity($priceData['max']);
                        $quantityPrice->setPrice($priceData['price']);
                        $productVariant->addQuantityPrice($quantityPrice);
                    }

                    // Associer la variante au produit
                    $product->addVariant($productVariant);
                }


                // Persister le produit
                $em->persist($product);
            }

            // Flush final
            $em->flush();

            $this->addFlash('success', 'Photos uploadées avec succès !');

            return $this->redirectToRoute('photographer_dashboard');
        }

        return $this->render('@PhotoPlugin/photographer/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
