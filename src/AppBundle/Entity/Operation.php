<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OperationRepository")
 * @ORM\Table(name="operations")
 * @ORM\HasLifecycleCallbacks()
 */
class Operation
{
    const STATUS_CORRECT = 1;
    const STATUS_DUPLICATED = 2;
    const STATUS_INVALID = 3;

    use UserEntityTrait;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Contact", inversedBy="operations")
     * @ORM\JoinColumn(name="id_contact", nullable=false)
     *
     * @Assert\NotBlank()
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
     * @var DateTime
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
     * @return Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param Contact $contact
     *
     * @return $this
     */
    public function setContact(Contact $contact)
    {
        $this->contact = $contact;

        return $this;
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
     *
     * @return $this
     */
    public function setImport(Import $import)
    {
        $this->import = $import;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     *
     * @return $this
     */
    public function setDate(DateTime $date)
    {
        $this->date = $date;

        return $this;
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
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
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
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @ORM\PostPersist()
     *
     * @return $this
     */
    public function setHash()
    {
        $this->hash = hash('md5', sprintf('%s%s%s', $this->getDate()->format('Y-m-d'), $this->name, $this->amount));

        return $this;
    }
}
