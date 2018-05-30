<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TagRepository")
 * @ORM\Table(name="tags", uniqueConstraints={
 *     @UniqueConstraint(name="uniq_tag_name", columns={"name", "id_user"})
 * })
 *
 * @UniqueEntity(fields={"name", "user"})
 */
class Tag
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
     * @ManyToMany(targetEntity="AppBundle\Entity\Contact", mappedBy="tags", cascade={"all"})
     * @JoinTable(name="contacts_tags")
     *
     * @var Contact[]|ArrayCollection
     */
    protected $contacts;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
    }

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
     * @return Contact[]
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @param Contact[] $contacts
     *
     * @return $this
     */
    public function setContacts($contacts)
    {
        $this->contacts = $contacts;

        return $this;
    }

    /**
     * @param Contact $contact
     *
     * @return $this
     */
    public function addContact(Contact $contact)
    {
        if ($this->contacts->contains($contact)) {
            return $this;
        }

        $this->contacts->add($contact);
        $contact->addTag($this);

        return $this;
    }

    /**
     * @param Contact $contact
     *
     * @return $this
     */
    public function removeContact(Contact $contact)
    {
        if (false === $this->contacts->contains($contact)) {
            return $this;
        }

        $this->contacts->removeElement($contact);
        $contact->removeTag($this);

        return $this;
    }
}
