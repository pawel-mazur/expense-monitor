<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContactRepository")
 * @ORM\Table(name="contacts", uniqueConstraints={
 *     @UniqueConstraint(name="uniq_name", columns={"name"})
 * })
 *
 * @UniqueEntity("name")
 */
class Contact
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
     * @ORM\Column(name="name", type="text", nullable=false)
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Operation", mappedBy="contact")
     *
     * @var Operation[]
     */
    protected $operations;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return Operation[]
     */
    public function getOperations()
    {
        return $this->operations;
    }

    /**
     * @param Operation[] $operations
     *
     * @return $this
     */
    public function setOperations($operations)
    {
        $this->operations = $operations;

        return $this;
    }
}
