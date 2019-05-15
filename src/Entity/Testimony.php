<?php
// src/Entity/Testimony.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="testimony")
 * @ORM\Entity(repositoryClass="App\Repository\TestimonyRepository")
 */
class Testimony
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    private $address;

    /**
     * @ORM\Column(type="string")
     */
    private $testimony;


    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getTestimony()
    {
        return $this->testimony;
    }

    /**
     * @param mixed $testimony
     */
    public function setTestimony($testimony)
    {
        $this->testimony = $testimony;
    }

    public function __toString()
    {
        $format = "Testimony (id: %s, name: %s, address: %s, testimony: %s)\n";
        return sprintf($format, $this->id, $this->name, $this->address, $this->testimony);

    }


}