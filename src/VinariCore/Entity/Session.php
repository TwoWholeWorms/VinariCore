<?php

/**
 * VinariCore
 *
 * @link      https://github.com/TwoWholeWorms/VinariCore
 * @copyright Copyright (c) 2015 Vinari Ltd. (http://vinari.co.uk)
 * @license   BSD-3-Clause
 */

namespace VinariCore\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use VinariCore\Exception\InvalidArgumentException;

/**
 * @ORM\Entity
 * @ORM\Table(name="Session", options={"charset"="utf8mb4", "collate"="utf8mb4_unicode_ci", "row_format"="DYNAMIC"}, indexes={
 *     @ORM\Index(name="IDX_Session_Get", columns={"id", "name"}),
 *     @ORM\Index(name="IDX_Session_GetList1", columns={"is_active"}),
 *     @ORM\Index(name="IDX_Session_GetList2", columns={"is_deleted"}),
 *     @ORM\Index(name="IDX_Session_GetList3", columns={"is_active", "is_deleted"}),
 *     @ORM\Index(name="IDX_Session_GetList4", columns={"id", "is_active", "is_deleted"})
 * })
 */
class Session extends AbstractEntity
{

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="string", length=64, nullable=false)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=32, nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="modified", type="bigint", nullable=false)
     */
    protected $modified;

    /**
     * @var string
     *
     * @ORM\Column(name="lifetime", type="bigint", nullable=false)
     */
    protected $lifetime;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="text", nullable=false)
     */
    protected $data;


    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Gets the value of id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the value of id.
     *
     * @param string $id the id
     *
     * @return self
     */
    public function setId($id)
    {
        if (!is_null($id) && (!is_string($id) || !preg_match('/^[0-9a-z]+$/i', $id))) {
            throw new InvalidArgumentException('$id must be a string matching /^[0-9a-z]+$/i; `' . gettype($id) . '` passed.');
        }
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of Name
     *
     * @param string name
     *
     * @return self
     */
    public function setName($value)
    {
        $this->name = $value;

        return $this;
    }

    /**
     * Get the value of Modified
     *
     * @return string
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set the value of Modified
     *
     * @param string modified
     *
     * @return self
     */
    public function setModified($value)
    {
        $this->modified = $value;

        return $this;
    }

    /**
     * Get the value of Lifetime
     *
     * @return string
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * Set the value of Lifetime
     *
     * @param string lifetime
     *
     * @return self
     */
    public function setLifetime($value)
    {
        $this->lifetime = $value;

        return $this;
    }

    /**
     * Get the value of Data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the value of Data
     *
     * @param string data
     *
     * @return self
     */
    public function setData($value)
    {
        $this->data = $value;

        return $this;
    }

}
