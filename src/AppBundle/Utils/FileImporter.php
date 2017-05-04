<?php

namespace AppBundle\Utils;

use AppBundle\Entity\Import;
use AppBundle\Entity\Operation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FileImporter
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var string
     */
    protected $importDir;

    /**
     * @var ArrayCollection|Operation[]
     */
    protected $operations;

    /**
     * @var ConstraintViolationList
     */
    protected $constraintViolationList;

    /**
     * FileImporter constructor.
     *
     * @param EntityManager         $entityManager
     * @param ValidatorInterface    $validator
     * @param TokenStorageInterface $tokenStorage
     * @param $importDir
     */
    public function __construct(EntityManager $entityManager, ValidatorInterface $validator, TokenStorageInterface $tokenStorage, $importDir)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->tokenStorage = $tokenStorage;
        $this->importDir = $importDir;

        $this->operations = new ArrayCollection();
    }

    public function load(Import $import)
    {
        $handle = fopen(sprintf('%s/%s', $this->importDir, $import->getFileName()), 'r');

        $first = true;
        while (false !== $data = fgetcsv($handle)) {
            if (true === $first) {
                $first = false;
                continue;
            }

            $errors = $this->validator->validate($data[1], new DateTime());

            $operation = new Operation();
            $operation->setImport($import);
            $operation->setDate(new \DateTime($data[1]));
            $operation->setName(iconv('windows-1250', 'utf-8', $data[6]));
            $operation->setAmount($data[3]);
            $operation->setUser($this->tokenStorage->getToken()->getUser());
            $operation->setStatus(1);

            $errors = $this->validator->validate($operation);

            $this->operations->add($operation);
        }

        fclose($handle);
    }

    public function import()
    {
        foreach ($this->getOperations() as $operation) {
            $this->entityManager->persist($operation);
        }

        $this->entityManager->flush();
    }

    /**
     * @return Operation[]|ArrayCollection
     */
    public function getOperations()
    {
        return $this->operations;
    }
}
