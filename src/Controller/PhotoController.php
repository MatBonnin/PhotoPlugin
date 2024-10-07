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

            // Créer le dossier de destination si nécessaire
            $uploadDir = sprintf('%s/public/media/image/%s', $this->getParameter('kernel.project_dir'), $eventSlug);
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($photos as $photo) {
                // Créer un nouveau produit
                $product = new Product();
                $product->setCurrentLocale('en_US'); // Adapter à votre configuration
                $product->setName($photo->getClientOriginalName());
                $product->setCode(uniqid('PHOTO_'));
                $product->setPhotographer($this->getUser());
                $product->setEvent($event);
                $product->setEnabled(true);

                // Générer un slug à partir du nom du fichier
                $slug = $slugify->slugify($photo->getClientOriginalName());
                $product->setSlug($slug);

                // Déplacer le fichier vers le dossier approprié
                $newFileName = uniqid() . '.' . $photo->guessExtension();
                $photo->move($uploadDir, $newFileName);

                // Créer un ProductImage et définir manuellement le chemin
                $productImage = new ProductImage();
                $productImage->setPath(sprintf('media/image/%s/%s', $eventSlug, $newFileName));
                $productImage->setType('main');

                // Ajouter l'image au produit
                $product->addImage($productImage);

                // Persister le produit et l'image
                $em->persist($productImage);
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
