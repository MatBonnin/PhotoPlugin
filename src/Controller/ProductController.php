<?php
namespace Sylius\Plugin\PhotoPlugin\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductImage;
use Sylius\Component\Core\Model\ProductVariant;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Plugin\PhotoPlugin\Entity\Photographer;
use Sylius\Plugin\PhotoPlugin\Entity\Event;
use Sylius\Plugin\PhotoPlugin\Form\Type\PhotoProductType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function upload(Request $request): Response
    {
        /** @var Photographer $photographer */
        $photographer = $this->getUser();

        $product = new Product();
        $product->setCurrentLocale('fr_FR'); // Ajustez selon votre locale
        $product->setFallbackLocale('fr_FR');
        $product->setEnabled(true);

        $form = $this->createForm(PhotoProductType::class, $product, [
            'events' => $photographer->getEvents(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'event
            $event = $form->get('event')->getData();
            if ($event instanceof Event) {
                // Associer l'événement au produit ou autre logique
                // Peut-être l'ajouter à une entité intermédiaire
            }

            // Gestion du fichier image
            $file = $form->get('imageFile')->getData();
            if ($file) {
                $filename = uniqid() . '.' . $file->guessExtension();

                // Déplace le fichier dans le répertoire des photos
                $file->move(
                    $this->getParameter('photos_directory'),
                    $filename
                );

                // Création de l'image Sylius
                $image = new ProductImage();
                $image->setType('main'); // Type d'image (main, thumbnail, etc.)
                $image->setPath($filename);
                $product->addImage($image);
            }

            // Génération d'un code unique pour le produit
            $product->setCode('PHOTO_' . uniqid());

            // Création de la variante du produit
            $variant = new ProductVariant();
            $variant->setCode('VARIANT_' . uniqid());
            $variant->setCurrentLocale('fr_FR');
            $variant->setName('Default');
            $variant->setProduct($product);
//            $variant->setPrice(1000); // Prix en centimes (10.00 €)
            $product->addVariant($variant);

            // Associer le produit au canal principal
            $channel = $this->entityManager->getRepository(ChannelInterface::class)->findOneByCode('FASHION_WEB');
            $product->addChannel($channel);

            // Associer le produit à un taxon (catégorie)
            $taxon = $this->entityManager->getRepository(TaxonInterface::class)->findOneBy(['code' => 'PHOTOS']);
            $product->hasTaxon($taxon);

            // Persister le produit
            $this->entityManager->persist($product);
            $this->entityManager->flush();

            $this->addFlash('success', 'Photo téléchargée et produit créé avec succès !');

            return $this->redirectToRoute('photographer_dashboard');
        }

        return $this->render('@PhotoPlugin/photographer/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
