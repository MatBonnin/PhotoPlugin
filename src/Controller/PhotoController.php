<?php

namespace Sylius\Plugin\PhotoPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sylius\Plugin\PhotoPlugin\Form\Type\MassPhotoUploadType;
use App\Entity\Product\Product;
use App\Entity\Product\ProductImage;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class PhotoController extends AbstractController
{
    public function upload(Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(MassPhotoUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->get('event')->getData();
            /** @var UploadedFile[] $photos */
            $photos = $form->get('photos')->getData();

            // Créer un slugifier pour transformer les noms en slugs
            $slugify = new Slugify();
            $eventSlug = $slugify->slugify($event->getName());

            // Créer le dossier de destination si nécessaire en utilisant Filesystem
            $uploadDir = sprintf('%s/public/media/image/%s', $this->getParameter('kernel.project_dir'), $eventSlug);
            $filesystem = new Filesystem();
            if (!$filesystem->exists($uploadDir)) {
                $filesystem->mkdir($uploadDir, 0755);
            }

            foreach ($photos as $photo) {
                // Créer un nouveau produit
                $product = new Product();
                $product->setCurrentLocale('fr_FR'); // Adapter à votre configuration
                $product->setName(pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME));
                $product->setCode(uniqid('PHOTO_'));
                $product->setPhotographer($this->getUser());
                $product->setEvent($event);
                $product->setEnabled(true);

                // Générer un slug unique
                $slug = $slugify->slugify(pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME) . '-' . uniqid());
                $product->setSlug($slug);

                // Déplacer le fichier vers le dossier approprié
                $newFileName = bin2hex(random_bytes(8)) . '.' . $photo->guessExtension();
                $photo->move($uploadDir, $newFileName);

                // Créer un ProductImage et définir le chemin relatif pour Sylius
                $productImage = new ProductImage();
                $productImage->setPath(sprintf('%s/%s', $eventSlug, $newFileName)); // Mettre uniquement le chemin relatif à "media/image"
                $productImage->setType('main');

                // Ajouter l'image au produit
                $product->addImage($productImage);

                // Persister le produit
                $em->persist($product);
            }

            // Flush final après avoir tout persisté
            $em->flush();

            $this->addFlash('success', 'Photos uploadées avec succès !');

            return $this->redirectToRoute('photographer_dashboard');
        }

        return $this->render('@PhotoPlugin/photographer/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
