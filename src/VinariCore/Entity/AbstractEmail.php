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
use VinariCore\Exception\InvalidArgumentException;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractEmail extends AbstractEntity
{

    protected static $foreignObjectDecamelisedKeys = ['user', 'sender', 'error'];

    /**
     * @var AbstractUser
     *
     * @ORM\JoinColumn(name="to_user_id", referencedColumnName="id", nullable=true)
     * @ORM\ManyToOne(targetEntity="AbstractUser", cascade={"all"})
     */
    protected $toUser;

    /**
     * @var AbstractUser
     *
     * @ORM\JoinColumn(name="from_user_id", referencedColumnName="id", nullable=true)
     * @ORM\ManyToOne(targetEntity="AbstractUser", cascade={"all"})
     */
    protected $fromUser;

    /**
     * @var string
     *
     * @ORM\Column(name="to_addresses", type="string", length=1024, nullable=true)
     */
    protected $toAddresses;

    /**
     * @var string
     *
     * @ORM\Column(name="cc_addresses", type="string", length=1024, nullable=true)
     */
    protected $ccAddresses;

    /**
     * @var string
     *
     * @ORM\Column(name="bcc_addresses", type="string", length=1024, nullable=true)
     */
    protected $bccAddresses;

    /**
     * @var string
     *
     * @ORM\Column(name="from_address", type="string", length=64, nullable=true)
     */
    protected $fromAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=256, nullable=true)
     */
    protected $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="text_content", type="text", nullable=true)
     */
    protected $textContent;

    /**
     * @var string
     *
     * @ORM\Column(name="html_content", type="text", nullable=true)
     */
    protected $htmlContent;

    /**
     * @var string
     *
     * @ORM\Column(name="to_name", type="string", length=128, nullable=false)
     */
    protected $toName;

    /**
     * @var string
     *
     * @ORM\Column(name="from_name", type="string", length=128, nullable=false)
     */
    protected $fromName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="processed_at", type="datetime", nullable=true)
     */
    protected $processedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sent_at", type="datetime", nullable=true)
     */
    protected $sentAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="read_at", type="datetime", nullable=true)
     */
    protected $readAt;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=16, nullable=false)
     */
    protected $status = 'pending';

    /**
     * @var Error
     *
     * @ORM\JoinColumn(name="error_id", referencedColumnName="id", nullable=true)
     * @ORM\ManyToOne(targetEntity="\VinariCore\Entity\Error", cascade={"all"})
     */
    protected $error;


    /**
     * Get the value of To User
     *
     * @return AbstractUser
     */
    public function getToUser()
    {
        return $this->toUser;
    }

    /**
     * Set the value of To User
     *
     * @param AbstractUser $toUser
     *
     * @return $this
     */
    public function setToUser(AbstractUser $toUser)
    {
        $this->toUser = $toUser;

        return $this;
    }

    /**
     * Get the value of From User
     *
     * @return AbstractUser
     */
    public function getFromUser()
    {
        return $this->fromUser;
    }

    /**
     * Set the value of From User
     *
     * @param AbstractUser $fromUser
     *
     * @return $this
     */
    public function setFromUser(AbstractUser $fromUser)
    {
        $this->fromUser = $fromUser;

        return $this;
    }

    /**
     * Get the value of To Addresses
     *
     * @return string
     */
    public function getToAddresses()
    {
        return $this->toAddresses;
    }

    /**
     * Set the value of To Addresses
     *
     * @param string $toAddresses
     *
     * @return $this
     */
    public function setToAddresses($toAddresses)
    {
        $this->toAddresses = $toAddresses;

        return $this;
    }

    /**
     * Get the value of Cc Addresses
     *
     * @return string
     */
    public function getCcAddresses()
    {
        return $this->ccAddresses;
    }

    /**
     * Set the value of Cc Addresses
     *
     * @param string $ccAddresses
     *
     * @return $this
     */
    public function setCcAddresses($ccAddresses)
    {
        $this->ccAddresses = $ccAddresses;

        return $this;
    }

    /**
     * Get the value of Bcc Addresses
     *
     * @return string
     */
    public function getBccAddresses()
    {
        return $this->bccAddresses;
    }

    /**
     * Set the value of Bcc Addresses
     *
     * @param string $bccAddresses
     *
     * @return $this
     */
    public function setBccAddresses($bccAddresses)
    {
        $this->bccAddresses = $bccAddresses;

        return $this;
    }

    /**
     * Get the value of From Address
     *
     * @return string
     */
    public function getFromAddress()
    {
        return $this->fromAddress;
    }

    /**
     * Set the value of From Address
     *
     * @param string $fromAddress
     *
     * @return $this
     */
    public function setFromAddress($fromAddress)
    {
        $this->fromAddress = $fromAddress;

        return $this;
    }

    /**
     * Get the value of Subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set the value of Subject
     *
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get the value of Text Content
     *
     * @return string
     */
    public function getTextContent()
    {
        return $this->textContent;
    }

    /**
     * Set the value of Text Content
     *
     * @param string $textContent
     *
     * @return $this
     */
    public function setTextContent($textContent)
    {
        $this->textContent = $textContent;

        return $this;
    }

    /**
     * Get the value of Html Content
     *
     * @return string
     */
    public function getHtmlContent()
    {
        return $this->htmlContent;
    }

    /**
     * Set the value of Html Content
     *
     * @param string $htmlContent
     *
     * @return $this
     */
    public function setHtmlContent($htmlContent)
    {
        $this->htmlContent = $htmlContent;

        return $this;
    }

    /**
     * Get the value of Processed At
     *
     * @return \DateTime
     */
    public function getProcessedAt()
    {
        return $this->processedAt;
    }

    /**
     * Set the value of Processed At
     *
     * @param \DateTime processedAt
     *
     * @return $this
     */
    public function setProcessedAt(\DateTime $processedAt)
    {
        $this->processedAt = $processedAt;

        return $this;
    }

    /**
     * Gets the value of status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the value of status.
     *
     * @param string $status the status
     *
     * @throws InvalidArgumentException
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $statuses = ['queued', 'sending', 'sent', 'bounced', 'retry', 'failed'];
        if (!is_string($status) || !in_array($status, $statuses, true)) {
            throw new InvalidArgumentException('Invalid status `' . print_r($status, true) . '` provided. Must be one of: ' . implode(', ', $statuses));
        }
        $this->status = $status;

        return $this;
    }

    /**
     * Gets the value of sentAt.
     *
     * @return \DateTime
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * Sets the value of sentAt.
     *
     * @param \DateTime $sentAt the sent at
     *
     * @return $this
     */
    public function setSentAt(\DateTime $sentAt)
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    /**
     * Gets the value of toName.
     *
     * @return string
     */
    public function getToName()
    {
        return $this->toName;
    }

    /**
     * Sets the value of toName.
     *
     * @param string $toName the to name
     *
     * @return $this
     */
    public function setToName($toName)
    {
        $this->toName = $toName;

        return $this;
    }

    /**
     * Gets the value of fromName.
     *
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * Sets the value of fromName.
     *
     * @param string $fromName the from name
     *
     * @return $this
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;

        return $this;
    }

    /**
     * Gets the value of error.
     *
     * @return Error
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Sets the value of error.
     *
     * @param Error $error the error
     *
     * @throws InvalidArgumentException
     *
     * @return $this
     */
    public function setError($error)
    {
        if (null !== $error && !($error instanceof Error)) {
            throw new InvalidArgumentException('$error must be an instance of VinariCore\\Entity\\Error or null; `' . gettype($error) . '` passed.');
        }
        $this->error = $error;

        return $this;
    }


    /**
     * Gets the value of readAt.
     *
     * @return \DateTime
     */
    public function getReadAt()
    {
        return $this->readAt;
    }

    /**
     * Sets the value of readAt.
     *
     * @param \DateTime $readAt the read at
     *
     * @return $this
     */
    public function setReadAt(\DateTime $readAt)
    {
        $this->readAt = $readAt;

        return $this;
    }
}
