<?php

/**
 * VinariCore
 *
 * @link      https://github.com/TwoWholeWorms/VinariCore
 * @copyright Copyright (c) 2015 Vinari Ltd. (http://vinari.co.uk)
 * @license   BSD-3-Clause
 */

namespace VinariCore\Mvc\Controller;

use VinariCore\Entity\Error;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\Controller\AbstractActionController as ZendAbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

abstract class AbstractActionController extends ZendAbstractActionController
{

    // Makes life easier when building console routes :)
    const COLOUR_CLEAR = "\033[2Jm";

    const COLOUR_RESET = "\033[0m";
    const COLOUR_RED = "\033[0;31m";
    const COLOUR_GREEN = "\033[0;32m";
    const COLOUR_YELLOW = "\033[0;33m";
    const COLOUR_BLUE = "\033[0;34m";
    const COLOUR_MAGENTA = "\033[0;35m";
    const COLOUR_CYAN = "\033[0;36m";
    const COLOUR_WHITE = "\033[0;37m";
    const COLOUR_BOLDRED = "\033[1;31m";
    const COLOUR_BOLDGREEN = "\033[1;32m";
    const COLOUR_BOLDYELLOW = "\033[1;33m";
    const COLOUR_BOLDBLUE = "\033[1;34m";
    const COLOUR_BOLDMAGENTA = "\033[1;35m";
    const COLOUR_BOLDCYAN = "\033[1;36m";
    const COLOUR_BOLDWHITE = "\033[1;37m";

    protected $viewModel;
    protected $entityManager;
    protected $session;

    public function __construct()
    {

        $this->viewModel = new ViewModel();
        $this->viewModel->success = false;

    }

    public function getViewModel()
    {
        return $this->viewModel;
    }

    public function doErrorOutput($errors)
    {

        $this->viewModel->success = false;
        $this->viewModel->errors = new \stdClass();

        foreach ($errors as $k => $e) {
            if (!is_string($k)) {
                throw new \Exception('Invalid error key provided to doErrorOutput. Must be string, `' . gettype($k) . '` provided.');
            }
            if (!is_string($e)) {
                throw new \Exception('Invalid error value provided to doErrorOutput. Must be string, `' . gettype($e) . '` provided.');
            }
            $this->viewModel->errors->$k = $e;
        }

        return $this->viewModel;

    }

    public function doSuccessOutput($data)
    {

        $this->viewModel->success = true;
        $this->viewModel->data = $data;

        return $this->viewModel;

    }

    public function setEventManager(EventManagerInterface $events)
    {
        parent::setEventManager($events);

        $this->entityManager = $this->getServiceLocator()->get('Doctrine\\ORM\\EntityManager');

        $controller = $this;
        $events->attach('dispatch', function ($e) use ($controller) {
            $config = $controller->getServiceLocator()->get('Config');
            $this->session   = new Container($config['session']['container_name']);

            if (!preg_match('/^cli/', php_sapi_name())) {
                $controller->getViewModel()->webServerName = $_SERVER['SERVER_ADDR'];
                $controller->getViewModel()->webServerEnvironment = APPLICATION_ENV;
            }
        }, 100); // execute before executing action logic

    }

    public function logError($code, $message, $file, $line, $stackTrace = [])
    {

        $objectManager = $this->getServiceLocator()->get('Doctrine\\ORM\\EntityManager');

        $error = new Error();
        $error->setCode($code);
        $error->setMessage($message);
        $error->setFile($file);
        $error->setLine($line);
        $error->setStackTrace($stackTrace);
        $error->setContext(isset($context) && is_array($context) ? $context : []);
        $headers = $this->getRequest()->getHeaders()->toArray();
        $error->setHeaders($headers && is_array($headers) ? $headers : []);
        $error->setBody(@file_get_contents('php://input'));
        $error->setServerParams(isset($_SERVER) && is_array($_SERVER) ? $_SERVER : []);
        $error->setGetParams(isset($_GET) && is_array($_GET) ? $_GET : []);
        $error->setPostParams(isset($_POST) && is_array($_POST) ? $_POST : []);
        $error->setFilesParams(isset($_FILES) && is_array($_FILES) ? $_FILES : []);
        $error->setRequestParams(isset($_REQUEST) && is_array($_REQUEST) ? $_REQUEST : []);
        $error->setSessionParams(isset($_SESSION) && is_array($_SESSION) ? $_SESSION : []);
        $error->setEnvParams(isset($_ENV) && is_array($_ENV) ? $_ENV : []);
        $error->setCookieParams(isset($_COOKIE) && is_array($_COOKIE) ? $_COOKIE : []);
        $error->setRawHttpPostData(isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : null);
        $error->setLastPhpErrorMessage(isset($php_errormsg) ? $php_errormsg : null);
        $error->setArgc(isset($argc) ? $argc : null);
        $error->setArgv(isset($argv) ? $argv : []);

        $objectManager->persist($error);
        $objectManager->flush();

        return $error;

    }

    public function generateRandomString($length = 8, $includeSymbols = false)
    {
        $symbols = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        if ($includeSymbols) {
            $symbols .= '!@$£&*()_+-+=:;".><,`~§|';
        }

        $output = '';
        $numSymbols = strlen($symbols) - 1;
        for ($i = 0; $i < $length; $i++) {
            $output .= $symbols[rand(0, $numSymbols)];
        }
        return $output;
    }

}
