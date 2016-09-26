<?php

/**
 * VinariCore
 *
 * @link      https://github.com/TwoWholeWorms/VinariCore
 * @copyright Copyright (c) 2015 Vinari Ltd. (http://vinari.co.uk)
 * @license   BSD-3-Clause
 */

namespace VinariCore\Session;

use Zend\Session\SessionManager as BaseSessionManager;
use Zend\Session\Storage;
use Zend\Session\SaveHandler;
use Zend\Session\Exception;
use Zend\Stdlib\ArrayUtils;

/**
 * Session ManagerInterface implementation utilizing ext/session
 */
class SessionManager extends BaseSessionManager
{

    /**
     * Start session
     *
     * if No session currently exists, attempt to start it. Calls
     * {@link isValid()} once session_start() is called, and raises an
     * exception if validation fails.
     *
     * @param bool $preserveStorage        If set to true, current session storage will not be overwritten by the
     *                                     contents of $_SESSION.
     * @return void
     * @throws Exception\RuntimeException
     */
    public function start($preserveStorage = false)
    {
        if ($this->sessionExists()) {
            return;
        }

        $saveHandler = $this->getSaveHandler();
        if ($saveHandler instanceof SaveHandler\SaveHandlerInterface) {
            // register the session handler with ext/session
            $this->registerSaveHandler($saveHandler);
        }

        $oldSessionData = array();
        if (null !== $_SESSION) {
            $oldSessionData = $_SESSION;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($oldSessionData instanceof \Traversable
            || (is_array($oldSessionData) && 0 === count($oldSessionData))
        ) {
            $_SESSION = ArrayUtils::merge($oldSessionData, $_SESSION, true);
        }

        $storage = $this->getStorage();

        // Since session is starting, we need to potentially repopulate our
        // session storage
        if (($storage instanceof Storage\SessionStorage) && $_SESSION !== $storage) {
            if (!$preserveStorage) {
                $storage->fromArray($_SESSION);
            }
            $_SESSION = $storage;
        } elseif ($storage instanceof Storage\StorageInitializationInterface) {
            $storage->init($_SESSION);
        }

        if (!$this->isValid()) {
            throw new Exception\RuntimeException('Session validation failed');
        }
    }

}
