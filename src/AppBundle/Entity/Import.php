<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="imports")
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
     * @ORM\Column(name="file_name", type="string", nullable=false)
     *
     * @var string
     */
    protected $fileName;

    /**
     * @Assert\NotBlank()
     * @Assert\File()
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
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     *
     * @return $this
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

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
