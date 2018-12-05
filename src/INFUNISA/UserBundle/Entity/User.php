<?php

namespace INFUNISA\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface; //Clase UserInterface, para manejar el sistema de seguridad de Symfony y obligatoriamente hay que definir estos 5 métodos: getUsername(), getPassword() <que estos ya los tenemos dentro de nuestra entidad acá en User.php>, ahora los siguientes métodos hay que definirlos también aunque sea vacíos inicialmente <posteriormente si los necesitamos, podemos codificarlos internamente>: getRoles(), getSalt(), eraseCredentials()
use Symfony\Component\Validator\Constraints as Assert; //Componente de validación del lado del servidor con Symfony para los constraints y para acceder a este componente lo hacemos a través del alias definidio Assert
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity; //Componente de constraints de Doctrine que es el UniqueEntity para que no permita registros campos duplicados
use Doctrine\Common\Collections\ArrayCollection;    //esta línea es para importar el objeto ArrayCollection para Asociar las entidades User y Task
use Symfony\Component\Security\Core\User\AdvancedUserInterface;     //muy necesaria para trabajar en el módulo de seguridad para autenticación de usuarios

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="INFUNISA\UserBundle\Repository\UserRepository")
 * @UniqueEntity("username")
 * @UniqueEntity("email")
 * @ORM\HasLifecycleCallbacks() //Los métodos del lifecycleCallbacks() que nosotros creemos p.ej.: setCreatedAtValue() y setUpdatedAtValue() para que se vayan guardando automáticamente por ejemplo los campos createdAt, updatedAt con peventos previamente establecido como PrePersist y PreUpdate
 */

//esto era antes de agregar la clase use Symfony\Component\Security\Core\User\UserInterface;
//class User
//ahora despúes de agregar esa clase, ahora se agrega el implements implements UserInterface, así:
//y antes de agregar el módulo de seguridad de autenticación de usuarios esta primera línea era así:
//class User implements UserInterface
//ahora como se ha agregado la clase AdvancedUserInterface, entonces esta primera línea queda así:
class User implements AdvancedUserInterface, \Serializable
{
    
/**
 * @ORM\OneToMany(targetEntity="Task", mappedBy="user")
 */
protected $task;    
    
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=50, unique=true)
     * @Assert\NotBlank(message="user.username.not_blank")
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=100)
     * @Assert\NotBlank()
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=100)
     * @Assert\NotBlank()
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="role", type="string", columnDefinition="ENUM('ROLE_ADMIN', 'ROLE_USER')", length=50)
     * @Assert\NotBlank()
     * @Assert\Choice(choices = {"ROLE_ADMIN", "ROLE_USER"})
     */
    private $role;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;
    
    public function __construct() {
        $this->task = new ArrayCollection;
        $this->isActive = true;     //esta línea se agregó debido al móludo de seguridad de autenticación de usuarios, indicando que todos nuestros usuarios siempre y cuando estén seteados a 1, van a poder autenticarse
    }

        /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set role
     *
     * @param string $role
     *
     * @return User
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return User
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return User
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
    
    /**
     * @ORM\PrePersist //este es un evento del LifecycleCallbacks()
     */
    
    public function setCreatedAtValue() //este es el método que creamos que depende del evento PrePersist
    {
        $this->createdAt = new \DateTime();
    }
    
    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setUpdatedAtValue() //este es el método que creamos que depende del evento PrePersist y PreUpdate
    {
        $this->updatedAt = new \DateTime();
    }
    
        public function getRoles()
    {
        return array($this->role);      //esta línea se agregó debido al módulo de seguridad de autenticación de usuarios para manejar la autorización de usuarios
    }
    
    public function getSalt()
    {
        return null;        //esta línea se agregó debido al módulo de seguridad de autenticación de usuarios y se definió en null ya que se utilizará por el momento
    }
    
    public function eraseCredentials()
    {
        
    }
    

    /**
     * Add task
     *
     * @param \INFUNISA\UserBundle\Entity\Task $task
     *
     * @return User
     */
    public function addTask(\INFUNISA\UserBundle\Entity\Task $task)
    {
        $this->task[] = $task;

        return $this;
    }

    /**
     * Remove task
     *
     * @param \INFUNISA\UserBundle\Entity\Task $task
     */
    public function removeTask(\INFUNISA\UserBundle\Entity\Task $task)
    {
        $this->task->removeElement($task);
    }

    /**
     * Get task
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTask()
    {
        return $this->task;
    }
    
    //propiedad para concatenar firstName y lastName y mostrarlo en el select box dentro de TaskType.php
    public function getFullName()
    {
        return $this->firstName . " " . $this->lastName;
    }
    
    //estos dos métodos serialize() y  unserialize(), nos van a manejar la serialización de nuestros campos indicados en cada uno de estos métodos
    
    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->isActive
        ));
    }
    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->isActive
        ) = unserialize($serialized);
    } 
    
    //los siguientes métodos que Symfony usa internamente son para manejar a los usuarios que se encuentran activos:
    
    public function isAccountNonExpired()
    {
        return true;
    }
    public function isAccountNonLocked()
    {
        return true;
    }
    public function isCredentialsNonExpired()
    {
        return true;
    }
    public function isEnabled()
    {
        return $this->isActive;
    }
}
