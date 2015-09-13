<?php

/**
 * VinariCore
 *
 * @link      https://github.com/TwoWholeWorms/VinariCore
 * @copyright Copyright (c) 2015 Vinari Ltd. (http://vinari.co.uk)
 * @license   BSD-3-Clause
 */

namespace VinariCore\Mvc\Controller;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use UrbanAirship\Airship;
use UrbanAirship\AirshipException;
use UrbanAirship\Push as P;
use UrbanAirship\UALog;
use VinariCore\Entity\Error;
use VinariCore\Exception\InvalidArgumentException;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\Controller\AbstractRestfulController as ZendAbstractRestfulController;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;

abstract class AbstractRestfulController extends ZendAbstractRestfulController
{

    protected $viewModel;

    protected $collectionOptions = ['GET', 'POST'];
    protected $resourceOptions = ['GET', 'PUT', 'DELETE'];

    protected $config = null;
    protected $session = null;

    protected static $cache = null;

    public function __construct()
    {
        $this->viewModel = new JsonModel();
        $this->viewModel->success = false;
    }

    public function onDispatch(MvcEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();

        $this->config = $sm->get('Config');
        $this->session = new Container($this->config['session']['container_name']);

        // Pass it up to the parent
        parent::onDispatch($e);
    }

    public function getList()
    {
        return $this->notImplemented();
    }

    public function get($id)
    {
        return $this->notImplemented();
    }

    public function create($data)
    {
        return $this->notImplemented();
    }

    public function createList()
    {
        return $this->notImplemented();
    }

    public function update($id, $data)
    {
        return $this->notImplemented();
    }

    public function updateList()
    {
        return $this->notImplemented();
    }

    public function replace($id)
    {
        return $this->notImplemented();
    }

    public function replaceList($data)
    {
        return $this->notImplemented();
    }

    public function delete($id)
    {
        return $this->notImplemented();
    }

    public function deleteList($data)
    {
        return $this->notImplemented();
    }

    protected function methodNotAllowed($message = null)
    {
        $this->response->setStatusCode(405);
        $error = $this->logError(405, 'Method Not Allowed' . ($message ? '. ' . $message : ''), __FILE__, __LINE__);

        $this->viewModel->success = false;
        $this->viewModel->error_id = $error->getId();
        $this->viewModel->errors = (object)['405' => 'Method Not Allowed' . ($message ? '. ' . $message : '')];

        return $this->viewModel;
    }

    protected function notImplemented($message = null)
    {
        $this->response->setStatusCode(501);
        $error = $this->logError(501, 'Not Implemented' . ($message ? '. ' . $message : ''), __FILE__, __LINE__);

        $this->viewModel->success = false;
        $this->viewModel->error_id = $error->getId();
        $this->viewModel->errors = (object)['501' => 'Not Implemented' . ($message ? '. ' . $message : '')];

        return $this->viewModel;
    }

    public function checkOptions($e)
    {
        if (!in_array($e->getRequest()->getMethod(), $this->_getOptions())) {
            $response = $this->getResponse();
            $response->setStatusCode(405);
            return $response;
        }
    }

    protected function doGet($entity, $outputKey, $conditions = [])
    {
        if ($data = $this->getFromCache()) {
            return ($this->doSuccessOutput($data));
        } else {
            $objectManager = $this->getServiceLocator()->get('Doctrine\\ORM\\EntityManager');

            $conditions['id'] = $this->params('id');
            $entity = $objectManager->getRepository($entity)->findOneBy($conditions);
            if ($entity) {
                $data = [$outputKey => $entity->__toArray()];
                $this->saveToCache($data);
                return $this->doSuccessOutput($data);
            } else {
                return $this->doErrorOutput([
                    'id' => sprintf('%s id `%d` does not exist.', $entity, $this->params('id')),
                ]);
            }
        }
    }

    protected function doDelete($entity, $entityUri, $outputKey)
    {
        $id = $this->params('id');
        if (!$id) {
            return $this->doErrorOutput([
                'id' => 'Id in URI is required, `' . $id . '` (' . gettype($id) . ') passed.',
            ]);
        }

        $objectManager = $this->getServiceLocator()->get('Doctrine\\ORM\\EntityManager');
        $entity = $objectManager->getRepository($entity)->findOneBy(['id' => $id, 'isDeleted' => false,]);
        if ($entity) {
            $entity->setIsDeleted(true);
            $entity->setLastUpdatedAt(new \DateTime());

            $objectManager->persist($entity);
            $objectManager->flush();

            $this->deleteFromCache($entityUri);
            $this->deleteFromCache($entityUri . '/' . $entity->getId());

            $data = [$outputKey => $entity->__toArray()];
            $this->saveToCache($data);
            return $this->doSuccessOutput($data);
        } else {
            return $this->doErrorOutput([
                'id' => sprintf('%s id `%d` does not exist.', $entity, $id),
            ]);
        }
    }

    protected function doDeleteList($entity, $entityUri, $inputArrayKey, $outputKey)
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\\ORM\\EntityManager');

        $request = $this->getBodyData();
        $errors = [];
        $entities = [];
        if (!isset($request[$inputArrayKey])) {
            $errors[$inputArrayKey] = sprintf('%s ids array is required.', $entity);
        } else if (!is_array($request[$inputArrayKey])) {
            $errors[$inputArrayKey] = sprintf('%s ids must be an array.', $entity);
        } else if (!count($request[$inputArrayKey])) {
            $errors[$inputArrayKey] = sprintf('%s ids must be an array containing at least one id.', $entity);
        } else {
            foreach ($request[$inputArrayKey] as $id) {
                if (!$entities[] = $objectManager->getRepository($entity)->findOneBy(['id' => $id, 'isDeleted' => false])) {
                    $errors[$inputArrayKey] = sprintf('%s id `%d` does not exist.', $entity, $id);
                    break;
                }
            }
        }

        if (count($errors)) {
            return $this->doErrorOutput($errors);
        } else {
            $data = [];
            foreach ($entities as $entity) {
                $entity->setIsDeleted(true);
                $entity->setLastUpdatedAt(new \DateTime());

                $objectManager->persist($entity);
                $objectManager->flush();

                $this->deleteFromCache($entityUri . '/' . $entity->getId());

                $data[] = $entity->__toArray();
            }

            $this->deleteFromCache($entityUri);
            return $this->doSuccessOutput([$outputKey => $data]);
        }
    }

    protected function doGetList($entity, $outputKey, $orderBy = 'id', $validProperties = [], $allowRetrieveAll = false)
    {
        $data = $this->getFromCache();
        if (!$data) {
            if (!class_exists($entity)) {
                throw new \Exception('Invalid entity `' . $entity . '` provided; class could not be found or autoloaded.');
            }

            $objectManager = $this->getServiceLocator()->get('Doctrine\\ORM\\EntityManager');

            $errors = [];
            $warnings = [];
            if (isset($_GET['extended']) && in_array($_GET['extended'], ['1', 1, true, 'true', 'y', 'yes', 'on'])) {
                $warnings = ['Please note: ?extended=true is not available on this call. Extended output has been disabled for this query.'];
            }

            $page = 1;
            if (isset($_GET['page'])) {
                if ($allowRetrieveAll && $_GET['page'] == 'all') {
                    $page = 'all';
                } else if ($_GET['page'] == 'all') {
                    $page = 1;
                    $warnings[] = '$page=all is not allowed on this call. $page has been set to 1 for this query.';
                } else {
                    $page = (int)$_GET['page'];
                    if ($page < 1) {
                        $page = 1;
                        $warnings[] = '$page must be at least 1. $page has been increased to 1 for this query.';
                    }
                }
            }

            $start = 0;
            $numPerPage = 30;
            if ($page != 'all') {
                if (isset($_GET['num_per_page'])) {
                    $numPerPage = (int)$_GET['num_per_page'];
                }
                if ($numPerPage > 40) {
                    $numPerPage = 40;
                    $warnings[] = '$numPerPage must be between 10 and 40. $numPerPage has been decreased to 40 for this query.';
                }
                if ($numPerPage < 10) {
                    $numPerPage = 10;
                    $warnings[] = '$numPerPage must be between 10 and 40. $numPerPage has been increased to 10 for this query.';
                }

                $start = ($page - 1) * $numPerPage;
            }

            $propertyFragment = '';
            if ($validProperties) {
                $properties = $this->params()->fromRoute('property', false);
                if ($properties !== false) {
                    $property = explode('=', $properties);
                    if (!in_array($property[0], $validProperties)) {
                        $errors['property'] = 'Invalid property `' . $property[0] . '`. Must be one of: ' . implode(', ', $validProperties);
                    } else if ($property == 'platform') {
                        if (!in_array($property[1], ['web', 'mobile', 'both'])) {
                            $errors['platform'] = 'Invalid platform value `' . $property[1] . '`. Must be one of: web, mobile, both';
                        } else if ($property[1] != 'both') {
                            $propertyFragment = 'AND e.property IN (\'' . $property[1] . '\', \'both\')';
                        }
                    }
                }
            }

            if ($errors) {
                return $this->doErrorOutput($errors);
            } else {
                // Get the total number of entities which match the where clause
                $qb = $objectManager->createQueryBuilder();
                $qb->select('COUNT(e.id)');
                $qb->from($entity, 'e');
                $qb->where('e.isDeleted = false AND e.isActive = true ' . $propertyFragment);
                $totalEntities = $qb->getQuery()->getSingleScalarResult();

                $dql = "SELECT e FROM {$entity} e WHERE e.isDeleted = false AND e.isActive = true {$propertyFragment} ORDER BY e.{$orderBy} ASC";
                if ($page == 'all') {
                    $query = $objectManager->createQuery($dql);
                    $numPerPage = $totalEntities;
                } else {
                    $query = $objectManager->createQuery($dql)
                                           ->setFirstResult($start)
                                           ->setMaxResults($numPerPage);
                }
                $results = new Paginator($query, $fetchJoinCollection = true);

                $entities = [];
                foreach ($results as $result) {
                    $entities[] = $result->__toArray(false);
                }

                $totalPages = ceil($totalEntities / $numPerPage);
                if (!$totalPages) $totalPages = 1;

                $data = [
                    'pagination' => [
                        'total_results' => $totalEntities,
                        'total_pages' => $totalPages,
                        'current_page' => $page == 'all' ? 1 : $page,
                        'num_per_page' => $numPerPage,
                    ],
                    $outputKey => $entities,
                ];
                if (count($warnings)) {
                    $data['warnings'] = $warnings;
                }
                $this->saveToCache($data);
            }
        }

        return $this->doSuccessOutput($data);
    }

    public function doErrorOutput($errors)
    {
        $response = $this->getResponse();
        // $response->setStatusCode(400);
        $response->setStatusCode(200); // It used to be 400, but apparently this causes errors, so we send 200 now, which I *HATE*

        $this->viewModel->success = false;
        $this->viewModel->errors = new \stdClass();

        $backtrace = debug_backtrace();
        $stackTrace = [];
        foreach ($backtrace as $t) {
            $stackTrace[] = (isset($t['file']) ? $t['file'] : 'UNKNOWN')
                            . ':'
                            . (isset($t['line']) ? $t['line'] : 'UNKNOWN')
                            . ' '
                            . (isset($t['class']) ? $t['class'] : 'UNKNOWN')
                            . (isset($t['type']) ? $t['type'] : 'UNKNOWN')
                            . (isset($t['function']) ? $t['function'] : 'UNKNOWN');
        }
        $stackTrace = implode("\n", $stackTrace);

        $error = $this->logError(400, 'Errors occurred whilst responding to client request in ' . $backtrace[1]['class'] . $backtrace[1]['type'] . $backtrace[0]['function'] . "():\n\n" . print_r($errors, true), $backtrace[0]['file'], $backtrace[0]['line'], $stackTrace);
        $this->viewModel->error_id = (string)$error->getId(); // Fuck it. Stupid fucking PHP and its stupid fucking converting values back and forth. This is a bigint field, it's supposed to be a string, but the fucktarding cuntshit that is this arsehat language is converting the fucking ID to an int when it's < the 32-bit overflow.

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
        $response = $this->getResponse();
        $response->setStatusCode(201);

        $this->viewModel->success = true;
        $this->viewModel->data = $data;

        return $this->viewModel;
    }

    public function _getOptions()
    {
        if ($this->params()->fromRoute('id', false)) {
            return $this->collectionOptions;
        }
        return $this->resourceOptions;
    }

    public function options()
    {
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Allow ' . implode(',', $this->_getOptions()));

        return $response;
    }

    public function logError($code, $message, $file, $line, $stackTrace = [])
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\\ORM\\EntityManager');

        $error = new Error();
        $error->setCode($code);
        $error->setMessage($message);
        $error->setFile($file);
        $error->setLine($line);
        $error->setStackTrace(is_string($stackTrace) ? preg_split('/[\r\n]+/', $stackTrace) : (!is_array($stackTrace) ? [$stackTrace] : $stackTrace));
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

    protected function getCache()
    {
        if (!self::$cache) {
            $serviceLocator = $this->getServiceLocator();
            self::$cache = $serviceLocator->get('Vinari\\Common\\Cache\\Cache');
        }
        return self::$cache;
    }

    protected function getFromCache()
    {
        return null;
        return $this->getCache()->fetch($_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING']);
    }

    protected function saveToCache($data)
    {
        return true;
        return $this->getCache()->save($_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING'], $data);
    }

    protected function deleteFromCache($requestUri)
    {
        return true;
        // Prepend the base path onto the request URI so that it matches the
        $basePath = $this->getRequest()->getBasePath();
        $requestUri = $basePath . $requestUri;

        return $this->getCache()->delete($requestUri);
    }

    protected function getBodyData()
    {
        $content = $this->getRequest()->getContent();

        // Blech…
        $output = @json_decode($content);
        if (!$output) {
            throw new \Exception('Request body does not contain valid JSON; `' . $content . '` passed.');
        } else {
            return $output;
        }
    }

    public function sendPushNotification(Notification $notification)
    {
        $sm = $this->getServiceLocator();
        $objectManager = $sm->get('Doctrine\\ORM\\EntityManager');

        $config = $sm->get('Config');
        if (!isset($config['urban-airship'])) {
            throw new \Exception('Urban Airship configuration is missing. Please make sure `config/autoload/urban-airship.local.php` exists in installation path.');
        } else if (!isset($config['urban-airship']['api-key']) || $config['urban-airship']['api-key']) {
            throw new \Exception('Urban Airship API key is missing. Please make sure `config/autoload/urban-airship.local.php` exists in installation path and that all variables are set correctly.');
        } else if (!isset($config['urban-airship']['api-secret']) || $config['urban-airship']['api-secret']) {
            throw new \Exception('Urban Airship API secret is missing. Please make sure `config/autoload/urban-airship.local.php` exists in installation path and that all variables are set correctly.');
        }

        UALog::setLogHandlers(array(new StreamHandler('logs' . DIRECTORY_SEPARATOR . 'urban_airship.' . date('Y-m-d') . '.log', Logger::DEBUG)));

        $airship = new Airship($config['urban-airship']['api-key'], $config['urban-airship']['api-secret']);
        try {
            $response = $airship->push()
                ->setAudience(P\deviceToken($device->getDeviceToken()))
                ->setNotification(P\notification($message))
                ->setDeviceTypes(P\deviceTypes($device->getDeviceType()))
                ->send();

            if ($response->ok) {
                $notification->setStatus('sent');
                $notification->setOperationId($response->operation_id);
                $notification->setPushIds($response->push_ids);
            } else {
                $notification->setStatus('failed');
            }

            $objectManager->persist($notification);
            $objectManager->flush();
        } catch (AirshipException $e) {
            $error = $this->logError($e->getCode(), $e->getMessage(), __FILE__, __LINE__, $e->getTrace());

            $notification->setStatus('failed');
            $notification->setError($error);

            $objectManager->persist($notification);
            $objectManager->flush();

            throw $e;
        }
    }

    public function getViewModel()
    {
        return $this->viewModel;
    }

}
