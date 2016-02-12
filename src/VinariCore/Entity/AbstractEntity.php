<?php

/**
 * VinariCore
 *
 * @link      https://github.com/TwoWholeWorms/VinariCore
 * @copyright Copyright (c) 2015 Vinari Ltd. (http://vinari.co.uk)
 * @license   BSD-3-Clause
 */

namespace VinariCore\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use VinariCore\Entity\SoftDeleteInterface;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractEntity implements SoftDeleteInterface
{

    // This property is a hack used in __toArray() below which helps make sure that we always send entity_name_id when a relationship is null, instead of entity_name = null and a missing _id column.
    protected static $foreignObjectDecamelisedKeys = [];

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=false, options={"default":true})
     */
    protected $isActive = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_deleted", type="boolean", nullable=false, options={"default":false})
     */
    protected $isDeleted = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false, options={"default":"CURRENT_TIMESTAMP"})
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_updated_at", type="datetime", nullable=false, options={"default":"CURRENT_TIMESTAMP"})
     */
    protected $lastUpdatedAt;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->lastUpdatedAt = new \DateTime();
    }


    /**
     * Gets the value of id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the value of id.
     *
     * @param int $id the id
     *
     * @return self
     */
    public function setId($id)
    {
        if (!is_string($id) && !preg_match('/^[1-9]\d*$/', $id) && !is_integer($id) && !is_null($id)) {
            throw new InvalidArgumentException('$id must be a string matching /^[1-9]\d*$/ or an integer; `' . gettype($id) . '` passed.');
        }
        $this->id = (string)$id;

        return $this;
    }

    /**
     * Gets the value of isActive.
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Sets the value of isActive.
     *
     * @param bool $isActive the is active
     *
     * @return self
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Gets the value of isDeleted.
     *
     * @return bool
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Sets the value of isDeleted.
     *
     * @param bool $isDeleted the is deleted
     *
     * @return self
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get the value of Created At
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the value of Created At
     *
     * @param \DateTime createdAt
     *
     * @return self
     */
    public function setCreatedAt(\DateTime $value)
    {
        $this->createdAt = $value;

        return $this;
    }

    /**
     * Get the value of Last Updated At
     *
     * @return \DateTime
     */
    public function getLastUpdatedAt()
    {
        return $this->lastUpdatedAt;
    }

    /**
     * Set the value of Last Updated At
     *
     * @param \DateTime lastUpdatedAt
     *
     * @return self
     */
    public function setLastUpdatedAt(\DateTime $value)
    {
        $this->lastUpdatedAt = $value;

        return $this;
    }

    /**
     * Horribly-written generic function to convert an Entity into an array, auto-expanding things if necessary.
     *
     * @param  boolean $allowExpand         Tells the function to include related objects in the output (eg, if the entity has a $user property, include the actual object alongside the user_id)
     * @param  array   $skipDecamelisedKeys A list of stuff to omit from the final output
     *
     * @return array                        The converted array
     */
    public function __toArray($allowExpand = true, $skipDecamelisedKeys = [])
    {
        $expand = isset($_REQUEST['expand']) && in_array($_REQUEST['expand'], ['on', 'yes', 'true', '1']);
        $vars = get_object_vars($this);

        $c = get_called_class();

        $array = [];
        foreach ($vars as $key => $value) {
            $decamelisedKey = preg_replace_callback('/(^|[a-z])([A-Z])/', function ($matches) {
                return strtolower(strlen($matches[1]) ? $matches[1] . '_' . $matches[2] : $matches[2]);
            }, trim($key, '_'));

            // If the decamelised property is in the list of stuff to skip then… skip it o.o
            if (in_array($decamelisedKey, $skipDecamelisedKeys)) continue;

            if (!is_object($value) && !is_array($value)) {
                $array[$decamelisedKey] = $value;
            } else if (is_array($value) && $expand && $allowExpand) {
                $data = [];
                $ids = [];
                foreach ($value as $v) {
                    if (method_exists($v, '__toArray')) {
                        $data[] = $v->__toArray(false, $skipDecamelisedKeys);
                    } else {
                        $data[] = $v;
                    }
                    if (method_exists($v, 'getId')) {
                        $ids[] = $v->getId();
                    }
                }
                $array[$decamelisedKey] = $data;

                $idsArrayKey = preg_replace('/s$/', '', preg_replace('/ies$/', 'y', $decamelisedKey)) . '_ids';
                $array[$idsArrayKey] = $ids;
            } else if (is_array($value) && (!$expand || !$allowExpand)) {
                $ids = [];
                foreach ($value as $v) {
                    if (method_exists($v, 'getId')) {
                        $ids[] = $v->getId();
                    }
                }

                $idsArrayKey = preg_replace('/s$/', '', preg_replace('/ies$/', 'y', $decamelisedKey)) . '_ids';
                $array[$idsArrayKey] = $ids;
            } else if (is_object($value) && get_class($value) == 'DateTime') {
                $array[$decamelisedKey] = $value->format('Y-m-d H:i:s');
            } else if (is_object($value) && get_class($value) != 'Doctrine\\ORM\\PersistentCollection' && $expand && $allowExpand) {
                try {
                    $array[$decamelisedKey] = $value->__toArray(false, $skipDecamelisedKeys);
                    $array[$decamelisedKey . '_id'] = $value->getId();
                } catch (\Exception $e) {
                    $array[$decamelisedKey] = null;
                    $array[$decamelisedKey . '_id'] = null;
                }
            } else if (is_object($value) && get_class($value) != 'Doctrine\\ORM\\PersistentCollection' && !($value instanceof ArrayCollection) && (!$expand || !$allowExpand)) {
                $array[$decamelisedKey . '_id'] = $value->getId();
            } else if (is_object($value) && ((get_class($value) == 'Doctrine\\ORM\\PersistentCollection') || ($value instanceof ArrayCollection)) && $expand && $allowExpand) {
                $data = [];
                $ids = [];
                foreach ($value as $v) {
                    if (method_exists($v, '__toArray')) {
                        $data[] = $v->__toArray(false, $skipDecamelisedKeys);
                    } else {
                        $data[] = $v;
                    }
                    if (method_exists($v, 'getId')) {
                        $ids[] = $v->getId();
                    }
                }
                $array[$decamelisedKey] = $data;

                $idsArrayKey = preg_replace('/s$/', '', preg_replace('/ies$/', 'y', $decamelisedKey)) . '_ids';
                $array[$idsArrayKey] = $ids;
            } else if (is_object($value) && ((get_class($value) == 'Doctrine\\ORM\\PersistentCollection') || ($value instanceof ArrayCollection)) && (!$expand || !$allowExpand)) {
                $ids = [];
                foreach ($value as $v) {
                    if (method_exists($v, 'getId')) {
                        $ids[] = $v->getId();
                    }
                }

                $idsArrayKey = preg_replace('/s$/', '', preg_replace('/ies$/', 'y', $decamelisedKey)) . '_ids';
                $array[$idsArrayKey] = $ids;
            } else {
                // Don't do anything
            }

            if (preg_match('/^is_/', $decamelisedKey)) {
                $array[$decamelisedKey] = (bool)$array[$decamelisedKey];
            }

            // Makes the result a bit more consistent
            if (in_array($decamelisedKey, $c::$foreignObjectDecamelisedKeys) && array_key_exists($decamelisedKey, $array) && !array_key_exists($decamelisedKey . '_id', $array)) {
                if (!$expand) unset($array[$decamelisedKey]);
                $array[$decamelisedKey . '_id'] = null;
            }
        }

        if (array_key_exists('initializer', $array)) {
            unset($array['initializer']);
        }
        if (array_key_exists('cloner', $array)) {
            unset($array['cloner']);
        }
        if (array_key_exists('is_initialized', $array)) {
            unset($array['is_initialized']);
        }

        return $array;
    }

    protected function isInConstantsList($prefix, $value)
    {
        $c = get_called_class();
        $oClass = new \ReflectionClass($c);
        foreach ($oClass->getConstants() as $const) {
            if (substr($const, 0, strlen($prefix)) !== $prefix) {
                continue;
            }

            if ($value === constant("{$c}::{$const}")) {
                return true;
            }
        }

        return false;
    }

}
