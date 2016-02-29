<?php

/**
 * VinariCore
 *
 * @link      https://github.com/TwoWholeWorms/VinariCore
 * @copyright Copyright (c) 2015 Vinari Ltd. (http://vinari.co.uk)
 * @license   BSD-3-Clause
 */

namespace VinariCore\Entity;

use BjyAuthorize\Provider\Role\ProviderInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ZfcUser\Entity\UserInterface;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractUser extends AbstractEntity implements UserInterface, ProviderInterface
{

    /**
     * @var string
     * @ORM\Column(name="username", type="string", length=255, unique=true, nullable=true)
     */
    protected $username;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", unique=true,  length=255)
     */
    protected $email;

    /**
     * @var string
     * @ORM\Column(name="display_name", type="string", length=50, nullable=true)
     */
    protected $displayName;

    /**
     * @var string
     *
     * @ORM\Column(name="given_name", type="string", length=64, nullable=true)
     */
    protected $givenName;

    /**
     * @var string
     *
     * @ORM\Column(name="family_name", type="string", length=64, nullable=true)
     */
    protected $familyName;

    /**
     * @var string
     * @ORM\Column(name="password_hash", type="string", length=128)
     */
    protected $password;

    /**
     * @var int
     */
    protected $state;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\ManyToMany(targetEntity="VinariCore\Entity\Role")
     * @ORM\JoinTable(name="user_role_linker",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     * )
     */
    protected $roles;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="activated_at", type="datetime", nullable=true)
     */
    protected $activatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="first_logged_in_at", type="datetime", nullable=true)
     */
    protected $firstLoggedInAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_logged_in_at", type="datetime", nullable=true)
     */
    protected $lastLoggedInAt;


    /**
     * Initialies the roles variable.
     */
    public function __construct()
    {
        parent::__construct();

        $this->roles       = new ArrayCollection();
    }


    /**
     * Get username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set username.
     *
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get displayName.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Set displayName.
     *
     * @param string $displayName
     *
     * @return $this
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get state.
     *
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set state.
     *
     * @param int $state
     *
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get role.
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles->getValues();
    }

    /**
     * Add a role to the user.
     *
     * @param Role $role
     *
     * @return $this
     */
    public function addRole($role)
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    /**
     * Sets the roles
     *
     * @param ArrayCollection $roles the roles
     *
     * @return $this
     */
    public function setRoles(ArrayCollection $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Gets the value of givenName.
     *
     * @return string
     */
    public function getGivenName()
    {
        return $this->givenName;
    }

    /**
     * Sets the value of givenName.
     *
     * @param string $givenName the given name
     *
     * @return $this
     */
    public function setGivenName($givenName)
    {
        $this->givenName = $givenName;

        return $this;
    }

    /**
     * Gets the value of familyName.
     *
     * @return string
     */
    public function getFamilyName()
    {
        return $this->familyName;
    }

    /**
     * Sets the value of familyName.
     *
     * @param string $familyName the family name
     *
     * @return $this
     */
    public function setFamilyName($familyName)
    {
        $this->familyName = $familyName;

        return $this;
    }

    /**
     * Gets the value of activatedAt.
     *
     * @return \DateTime
     */
    public function getActivatedAt()
    {
        return $this->activatedAt;
    }

    /**
     * Sets the value of activatedAt.
     *
     * @param \DateTime $activatedAt the activated at
     *
     * @return $this
     */
    public function setActivatedAt(\DateTime $activatedAt)
    {
        $this->activatedAt = $activatedAt;

        return $this;
    }

    /**
     * Gets the value of firstLoggedInAt.
     *
     * @return \DateTime
     */
    public function getFirstLoggedInAt()
    {
        return $this->firstLoggedInAt;
    }

    /**
     * Sets the value of firstLoggedInAt.
     *
     * @param \DateTime $firstLoggedInAt the first logged in at
     *
     * @return $this
     */
    public function setFirstLoggedInAt(\DateTime $firstLoggedInAt)
    {
        $this->firstLoggedInAt = $firstLoggedInAt;

        return $this;
    }

    /**
     * Gets the value of lastLoggedInAt.
     *
     * @return \DateTime
     */
    public function getLastLoggedInAt()
    {
        return $this->lastLoggedInAt;
    }

    /**
     * Sets the value of lastLoggedInAt.
     *
     * @param \DateTime $lastLoggedInAt the last logged in at
     *
     * @return $this
     */
    public function setLastLoggedInAt(\DateTime $lastLoggedInAt)
    {
        $this->lastLoggedInAt = $lastLoggedInAt;

        return $this;
    }

}
