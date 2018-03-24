<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OperationRepository")
 * @ORM\Table(name="operations")
 */
class Operation
{
    const STATUS_CORRECT = 1;
    const STATUS_DUPLICATED = 2;
    const STATUS_INVALID = 3;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="id_user", nullable=false)
     *
     * @var User
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Contact", inversedBy="operations")
     * @ORM\JoinColumn(name="id_contact", nullable=false)
     *
     * @var Contact
     */
    protected $contact;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Import", inversedBy="operations")
     * @ORM\JoinColumn(name="id_import", nullable=true)
     *
     * @var Import
     */
    protected $import;

    /**
     * @ORM\Column(name="date", type="date", nullable=false)
     *
     * @Assert\Date()
     * @Assert\NotBlank()
     *
     * @var \DateTime
     */
    protected $date;

    /**
     * @ORM\Column(name="name", type="text", nullable=false)
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2, nullable=false)
     *
     * @Assert\Range(min="-1000000", max="1000000")
     *
     * @var float
     */
    protected $amount;

    /**
     * @ORM\Column(name="status", type="integer", nullable=false)
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $status;

    /**
     * @ORM\Column(name="hash", type="string", nullable=false)
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $hash;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param Contact $contact
     */
    public function setContact(Contact $contact)
    {
        $this->contact = $contact;
    }

    /**
     * @return Import
     */
    public function getImport()
    {
        return $this->import;
    }

    /**
     * @param Import $import
     */
    public function setImport(Import $import)
    {
        $this->import = $import;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    public function setHash()
    {
        $this->hash = hash('md5', sprintf('%s%s%s', $this->getDate()->format('Y-m-d'), $this->name, $this->amount));
    }
}
