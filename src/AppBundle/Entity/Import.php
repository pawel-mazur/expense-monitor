<?php

namespace AppBundle\Entity;

use AppBundle\Validator\ImportFile;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="imports", uniqueConstraints={
 *     @UniqueConstraint(name="uniq_import_name", columns={"name", "id_user"}),
 *     @UniqueConstraint(name="uniq_import_hash", columns={"hash", "id_user"})
 * })
 * @ORM\HasLifecycleCallbacks()
 *
 * @UniqueEntity("name")
 */
class Import
{
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
     * @ORM\Column(name="date", type="datetimetz", nullable=true)
     *
     * @Assert\DateTime()
     *
     * @var DateTime
     */
    protected $date;

    /**
     * @ORM\Column(name="name", type="string", nullable=false)
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(name="hash", type="string", nullable=false)
     *
     * @var string
     */
    protected $hash;

    /**
     * @Assert\NotBlank()
     * @ImportFile()
     *
     * @var UploadedFile
     */
    protected $file;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Operation", mappedBy="import")
     *
     * @var ArrayCollection
     */
    protected $operations;

    public function __construct()
    {
        $this->operations = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @ORM\PrePersist()
     *
     * @return $this
     */
    public function setHash()
    {
        $this->hash = md5($this->getName());

        return $this;
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param UploadedFile $file
     *
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return ArrayCollection|Operation[]
     */
    public function getOperations()
    {
        return $this->operations;
    }

    /**
     * @param ArrayCollection|Operation[] $operations
     *
     * @return $this
     */
    public function setOperations(ArrayCollection $operations)
    {
        $this->operations = $operations;

        return $this;
    }
}
