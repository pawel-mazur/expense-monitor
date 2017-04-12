<?php

namespace AppBundle\Entity;

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
     * @ORM\JoinColumn(name="id_user", referencedColumnName="id", nullable=false)
     *
     * @var User
     */
    protected $user;

    /**
     * @ORM\Column(name="date", type="datetimetz", nullable=true)
     *
     * @Assert\DateTime()
     *
     * @var \DateTime
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
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
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
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
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
     */
    public function setFile($file)
    {
        $this->file = $file;
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
     */
    public function setOperations(ArrayCollection $operations)
    {
        $this->operations = $operations;
    }
}
