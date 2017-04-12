<?php

namespace AppBundle\Utils;

use AppBundle\Entity\Operation;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileImporter
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * FileImporter constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function import(UploadedFile $uploadedFile)
    {
        $handle = fopen($uploadedFile->getRealPath(), 'r');

        $first = true;
        while (false !== $data = fgetcsv($handle)) {
            if (true === $first) {
                $first = false;
                continue;
            }

            $operation = new Operation();
            $operation->setDate(new \DateTime($data[1]));
            $operation->setName($data[6]);
            $operation->setAmount($data[3]);
            $operation->setStatus(1);

            $this->entityManager->persist($operation);
        }

        fclose($handle);
    }
}
