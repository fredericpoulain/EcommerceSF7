<?php

namespace App\Service;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class ImageService
{
    public function __construct(private ParameterBagInterface $params)
    {
    }

    public function addImagesCategories(UploadedFile $image): string
    {
        $path = $this->params->get('images_directory_categories');

        // On crée le dossier de destination s'il n'existe pas
        if (!file_exists($path . '/')) {
            if (!mkdir($concurrentDirectory = $path . '/', 0755, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Le dossier "%s" n\'a pas pu être créé', $concurrentDirectory));
            }
        }
        //On récupère l'extension du type mime
        $extension = explode("/", $image->getMimeType())[1];
        //On génère un nom unique
        $imageRename = uniqid(mt_rand(), true) . '.' . $extension;
        //on déplace le fichier
        $image->move($path . '/', $imageRename);

        return $imageRename;
    }

    public function delete(object $imageToDeleted, string $pathProjects, $entityManager): array
    {

        if ($imageToDeleted && unlink($pathProjects . '/' . $imageToDeleted->getName())) {
            $entityManager->remove($imageToDeleted);
            $entityManager->flush();
            return [true, 'Image supprimée avec succès'];
        }
        return [false, "Erreur : Impossible de supprimer l'image " . $imageToDeleted->getName()];

    }
    public function checkTypeMime(UploadedFile $image): bool
    {
        $typeMimeArray = [
            "jpg" => "image/jpeg",
            "jpeg" => "image/jpeg",
            "webp" => "image/webp",
            "png" => "image/png"
        ];
        $typeMime = $image->getMimeType();

        if (!in_array($typeMime, $typeMimeArray, true)) return false;
        return true;
    }
    public function checkSize(UploadedFile $image): bool
    {
        $size = $image->getSize();
        return $size <= 1000 * 1024;
    }
}