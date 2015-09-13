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
 * @ORM\Table(name="AuditLog", options={"charset"="utf8mb4", "collate"="utf8mb4_unicode_ci", "row_format"="DYNAMIC"}, indexes={
 *     @ORM\Index(name="IDX_AuditLog_GetList1", columns={"is_active"}),
 *     @ORM\Index(name="IDX_AuditLog_GetList2", columns={"is_deleted"}),
 *     @ORM\Index(name="IDX_AuditLog_GetList3", columns={"is_active", "is_deleted"}),
 *     @ORM\Index(name="IDX_AuditLog_GetList4", columns={"id", "is_active", "is_deleted"})
 * })
 */
class AuditLog extends AbstractEntity
{

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=false)
     */
    protected $message;

    /**
     * @var string
     *
     * @ORM\Column(name="entity", type="string", length=32, nullable=true)
     */
    protected $entity;

    /**
     * @var string
     *
     * @ORM\Column(name="entity_id", type="integer", nullable=true)
     */
    protected $entityId;

    /**
     * @var array
     *
     * @ORM\Column(name="additional_data", type="json_array", nullable=true)
     */
    protected $additionalData;


    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Gets the value of message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets the value of message.
     *
     * @param string $message the message
     *
     * @return self
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Gets the value of entity.
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Sets the value of entity.
     *
     * @param string $entity the entity
     *
     * @return self
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Gets the value of entityId.
     *
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Sets the value of entityId.
     *
     * @param string $entityId the entity id
     *
     * @return self
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Gets the value of additionalData.
     *
     * @return array
     */
    public function getAdditionalData()
    {
        return $this->additionalData;
    }

    /**
     * Sets the value of additionalData.
     *
     * @param array $additionalData the additional data
     *
     * @return self
     */
    public function setAdditionalData(array $additionalData)
    {
        $this->additionalData = $additionalData;

        return $this;
    }
}
