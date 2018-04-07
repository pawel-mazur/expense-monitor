<?php

namespace AppBundle\Utils;

use AppBundle\Entity\Contact;
use AppBundle\Entity\Import;
use AppBundle\Entity\Operation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Validator\Constraints\Date;
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
     * @var Stopwatch
     */
    private $stopwatch;

    /**
     * FileImporter constructor.
     *
     * @param EntityManager         $entityManager
     * @param ValidatorInterface    $validator
     * @param TokenStorageInterface $tokenStorage
     * @param Stopwatch             $stopwatch
     * @param $importDir
     */
    public function __construct(EntityManager $entityManager, ValidatorInterface $validator, TokenStorageInterface $tokenStorage, Stopwatch $stopwatch, $importDir)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->tokenStorage = $tokenStorage;
        $this->stopwatch = $stopwatch;
        $this->importDir = $importDir;

        $this->operations = new ArrayCollection();
    }

    public function load(Import $import, $ignoreExisting)
    {
        $this->stopwatch->start('load');

        $handle = fopen(sprintf('%s/%s', $this->importDir, $import->getHash()), 'r');

        $user = $this->tokenStorage->getToken()->getUser();
        $operations = $this->entityManager->getRepository(Operation::class)->getOperationsQB($user)
            ->indexBy('operation', 'operation.hash')
            ->getQuery()
            ->getResult();

        $first = true;
        while (false !== $data = fgetcsv($handle)) {
            if (true === $first) {
                $first = false;
                continue;
            }

            $name = (preg_replace('/.*: /', '', iconv('windows-1250', 'utf-8', $data[7])));
            $contact = new Contact();
            $contact->setName($name);
            $contact->setUser($this->tokenStorage->getToken()->getUser());

            $operation = new Operation();
            $operation->setImport($import);

            $errors = $this->validator->validate($data[1], new Date());

            if (false == count($errors)) {
                $operation->setDate(new \DateTime($data[1]));
            } else {
                $operation->setDate(new \DateTime());
            }

            $operation->setStatus(Operation::STATUS_CORRECT);
            $operation->setName(iconv('windows-1250', 'utf-8', $data[6]));
            $operation->setAmount($data[3]);
            $operation->setUser($user);
            $operation->setContact($contact);
            $operation->setHash();

            $errors = $this->validator->validate($operation);

            if (isset($operations[$operation->getHash()])) {
                $operation->setStatus(Operation::STATUS_DUPLICATED);
            }

            if (count($errors)) {
                $operation->setStatus(Operation::STATUS_INVALID);
            }

            if (Operation::STATUS_DUPLICATED !== $operation->getStatus() || false === $ignoreExisting) {
                $this->operations->add($operation);
            }
        }

        fclose($handle);

        $this->stopwatch->stop('load');
    }

    public function import()
    {
        $this->stopwatch->start('import');

        $user = $this->tokenStorage->getToken()->getUser();
        $contacts = $this->entityManager->getRepository(Contact::class)->createContactQB($user)
            ->indexBy('contact', 'contact.name')
            ->getQuery()
            ->getResult();

        foreach ($this->getOperations() as $operation) {
            if (Operation::STATUS_CORRECT === $operation->getStatus()) {
                if (isset($contacts[$operation->getContact()->getName()]) && $contact = $contacts[$operation->getContact()->getName()]) {
                    $operation->setContact($contact);
                } else {
                    $contacts[$operation->getContact()->getName()] = $operation->getContact();
                    $this->entityManager->persist($operation->getContact());
                }

                $this->entityManager->persist($operation);
            }
        }

        $this->entityManager->flush();

        $this->stopwatch->stop('import');
    }

    /**
     * @return Operation[]|ArrayCollection
     */
    public function getOperations()
    {
        return $this->operations;
    }
}
