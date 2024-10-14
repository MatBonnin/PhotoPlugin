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
use Sylius\Component\Core\Model\ChannelInterface;
use App\Entity\Channel\ChannelPricing;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\Component\Product\Repository\ProductOptionRepositoryInterface;

class PhotoController extends AbstractController
{
    public function upload(
        Request $request,
        EntityManagerInterface $em,
        ChannelRepositoryInterface $channelRepository,
        ProductOptionRepositoryInterface $productOptionRepository
    ) {
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

            // Récupérer les options de produit
            $productTypeOption = $productOptionRepository->findOneBy(['code' => 'product_type']);
            $digitalOption = $productOptionRepository->findOneBy(['code' => 'digital_option']);
            $printSizeOption = $productOptionRepository->findOneBy(['code' => 'print_size']);
            $printOption = $productOptionRepository->findOneBy(['code' => 'print_option']);
            $rigidSizeOption = $productOptionRepository->findOneBy(['code' => 'rigid_size']);
            $rigidOption = $productOptionRepository->findOneBy(['code' => 'rigid_option']);
            $goodieTypeOption = $productOptionRepository->findOneBy(['code' => 'goodie_type']);

            // Vérifier que les options existent
            if (!$productTypeOption || !$digitalOption || !$printSizeOption || !$printOption || !$rigidSizeOption || !$rigidOption || !$goodieTypeOption) {
                throw new \Exception('Une ou plusieurs options de produit n\'ont pas été trouvées.');
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

                // Associer les options au produit
                $product->addOption($productTypeOption);
                $product->addOption($digitalOption);
                $product->addOption($printSizeOption);
                $product->addOption($printOption);
                $product->addOption($rigidSizeOption);
                $product->addOption($rigidOption);
                $product->addOption($goodieTypeOption);

                // Créer une variante par défaut
                $productVariant = new ProductVariant();
                $productVariant->setCurrentLocale('fr_FR');
                $productVariant->setName($productName . ' - Variante par défaut');
                $productVariant->setCode(uniqid('VARIANT_'));
                $productVariant->setOnHand(9999);

                // Associer la variante au produit
                $product->addVariant($productVariant);

                // Créer un ChannelPricing pour la variante par défaut
                $channelPricing = new ChannelPricing();
                $channelPricing->setChannelCode($channel->getCode());
                $channelPricing->setPrice(0); // Le prix sera calculé dynamiquement

                $productVariant->addChannelPricing($channelPricing);

                // Persister le produit
                $em->persist($product);
            }

            // Flush final
            $em->flush();

            $this->addFlash('success', 'Photos uploadées avec succès !');

            return $this->redirectToRoute('photographer_dashboard');
        }
    }
}
