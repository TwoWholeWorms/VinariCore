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
 * @ORM\Table(name="Error", options={"charset"="utf8mb4", "collate"="utf8mb4_unicode_ci", "row_format"="DYNAMIC"}, indexes={
 *     @ORM\Index(name="IDX_Error_GetList1", columns={"is_active"}),
 *     @ORM\Index(name="IDX_Error_GetList2", columns={"is_deleted"}),
 *     @ORM\Index(name="IDX_Error_GetList3", columns={"is_active", "is_deleted"}),
 *     @ORM\Index(name="IDX_Error_GetList4", columns={"id", "is_active", "is_deleted"})
 * })
 */
class Error extends AbstractEntity
{

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="string", length=4096, nullable=false)
     */
    protected $message;

    /**
     * @var int
     *
     * @ORM\Column(name="code", type="integer", nullable=false)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="file", type="string", length=1024, nullable=false)
     */
    protected $file;

    /**
     * @var int
     *
     * @ORM\Column(name="line", type="integer", nullable=false)
     */
    protected $line;

    /**
     * @var array
     *
     * @ORM\Column(name="stack_trace", type="json_array", nullable=true)
     */
    protected $stackTrace;

    /**
     * @var array
     *
     * @ORM\Column(name="context", type="json_array", nullable=false)
     */
    protected $context;

    /**
     * @var array
     *
     * @ORM\Column(name="headers", type="json_array", nullable=false)
     */
    protected $headers;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text", nullable=false)
     */
    protected $body;

    /**
     * @var array
     *
     * @ORM\Column(name="server_params", type="json_array", nullable=false)
     */
    protected $serverParams;

    /**
     * @var array
     *
     * @ORM\Column(name="get_params", type="json_array", nullable=false)
     */
    protected $getParams;

    /**
     * @var array
     *
     * @ORM\Column(name="post_params", type="json_array", nullable=false)
     */
    protected $postParams;

    /**
     * @var array
     *
     * @ORM\Column(name="files_params", type="json_array", nullable=false)
     */
    protected $filesParams;

    /**
     * @var array
     *
     * @ORM\Column(name="request_params", type="json_array", nullable=false)
     */
    protected $requestParams;

    /**
     * @var array
     *
     * @ORM\Column(name="session_params", type="json_array", nullable=false)
     */
    protected $sessionParams;

    /**
     * @var array
     *
     * @ORM\Column(name="env_params", type="json_array", nullable=false)
     */
    protected $envParams;

    /**
     * @var array
     *
     * @ORM\Column(name="cookie_params", type="json_array", nullable=false)
     */
    protected $cookieParams;

    /**
     * @var string
     *
     * @ORM\Column(name="http_status_code", type="integer", nullable=true)
     */
    protected $httpStatusCode;

    /**
     * @var string
     *
     * @ORM\Column(name="raw_http_post_data", type="text", nullable=true)
     */
    protected $rawHttpPostData;

    /**
     * @var string
     *
     * @ORM\Column(name="last_php_error_message", type="string", length=2048, nullable=true)
     */
    protected $lastPhpErrorMessage;

    /**
     * @var int
     *
     * @ORM\Column(name="argc", type="integer", nullable=true)
     */
    protected $argc;

    /**
     * @var array
     *
     * @ORM\Column(name="argv", type="json_array", nullable=true)
     */
    protected $argv;


    public function __construct()
    {
        $this->serverParams = [];
        $this->getParams = [];
        $this->postParams = [];
        $this->filesParams = [];
        $this->requestParams = [];
        $this->sessionParams = [];
        $this->envParams = [];
        $this->cookieParams = [];
        $this->headers = [];
        $this->body = '';
        $this->httpStatusCode = 0;
        $this->argc = 0;
        $this->argv = [];

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
     * Gets the value of code.
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Sets the value of code.
     *
     * @param int $code the code
     *
     * @return self
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Gets the value of file.
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Sets the value of file.
     *
     * @param string $file the file
     *
     * @return self
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Gets the value of line.
     *
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Sets the value of line.
     *
     * @param int $line the line
     *
     * @return self
     */
    public function setLine($line)
    {
        $this->line = $line;

        return $this;
    }

    /**
     * Gets the value of stackTrace.
     *
     * @return string
     */
    public function getStackTrace()
    {
        return $this->stackTrace;
    }

    /**
     * Sets the value of stackTrace.
     *
     * @param array $stackTrace the stack trace
     *
     * @return self
     */
    public function setStackTrace(array $stackTrace)
    {
        $this->stackTrace = $stackTrace;

        return $this;
    }

    /**
     * Gets the value of serverParams.
     *
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * Sets the value of serverParams.
     *
     * @param array $serverParams the server params
     *
     * @return self
     */
    public function setServerParams(array $serverParams)
    {
        $this->serverParams = $serverParams;

        return $this;
    }

    /**
     * Gets the value of getParams.
     *
     * @return array
     */
    public function getGetParams()
    {
        return $this->getParams;
    }

    /**
     * Sets the value of getParams.
     *
     * @param array $getParams the get params
     *
     * @return self
     */
    public function setGetParams(array $getParams)
    {
        $this->getParams = $getParams;

        return $this;
    }

    /**
     * Gets the value of postParams.
     *
     * @return array
     */
    public function getPostParams()
    {
        return $this->postParams;
    }

    /**
     * Sets the value of postParams.
     *
     * @param array $postParams the post params
     *
     * @return self
     */
    public function setPostParams(array $postParams)
    {
        $this->postParams = $postParams;

        return $this;
    }

    /**
     * Gets the value of filesParams.
     *
     * @return array
     */
    public function getFilesParams()
    {
        return $this->filesParams;
    }

    /**
     * Sets the value of filesParams.
     *
     * @param array $filesParams the files params
     *
     * @return self
     */
    public function setFilesParams(array $filesParams)
    {
        $this->filesParams = $filesParams;

        return $this;
    }

    /**
     * Gets the value of requestParams.
     *
     * @return array
     */
    public function getRequestParams()
    {
        return $this->requestParams;
    }

    /**
     * Sets the value of requestParams.
     *
     * @param array $requestParams the request params
     *
     * @return self
     */
    public function setRequestParams(array $requestParams)
    {
        $this->requestParams = $requestParams;

        return $this;
    }

    /**
     * Gets the value of sessionParams.
     *
     * @return array
     */
    public function getSessionParams()
    {
        return $this->sessionParams;
    }

    /**
     * Sets the value of sessionParams.
     *
     * @param array $sessionParams the session params
     *
     * @return self
     */
    public function setSessionParams(array $sessionParams)
    {
        $this->sessionParams = $sessionParams;

        return $this;
    }

    /**
     * Gets the value of envParams.
     *
     * @return array
     */
    public function getEnvParams()
    {
        return $this->envParams;
    }

    /**
     * Sets the value of envParams.
     *
     * @param array $envParams the env params
     *
     * @return self
     */
    public function setEnvParams(array $envParams)
    {
        $this->envParams = $envParams;

        return $this;
    }

    /**
     * Gets the value of cookieParams.
     *
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * Sets the value of cookieParams.
     *
     * @param array $cookieParams the cookie params
     *
     * @return self
     */
    public function setCookieParams(array $cookieParams)
    {
        $this->cookieParams = $cookieParams;

        return $this;
    }

    /**
     * Gets the value of httpStatusCode.
     *
     * @return string
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * Sets the value of httpStatusCode.
     *
     * @param string $httpStatusCode the http status code
     *
     * @return self
     */
    public function setHttpStatusCode($httpStatusCode)
    {
        $this->httpStatusCode = $httpStatusCode;

        return $this;
    }

    /**
     * Gets the value of rawHttpPostData.
     *
     * @return string
     */
    public function getRawHttpPostData()
    {
        return $this->rawHttpPostData;
    }

    /**
     * Sets the value of rawHttpPostData.
     *
     * @param string $rawHttpPostData the raw http post data
     *
     * @return self
     */
    public function setRawHttpPostData($rawHttpPostData)
    {
        $this->rawHttpPostData = $rawHttpPostData;

        return $this;
    }

    /**
     * Gets the value of lastPhpErrorMessage.
     *
     * @return string
     */
    public function getLastPhpErrorMessage()
    {
        return $this->lastPhpErrorMessage;
    }

    /**
     * Sets the value of lastPhpErrorMessage.
     *
     * @param string $lastPhpErrorMessage the last php error message
     *
     * @return self
     */
    public function setLastPhpErrorMessage($lastPhpErrorMessage)
    {
        $this->lastPhpErrorMessage = $lastPhpErrorMessage;

        return $this;
    }

    /**
     * Gets the value of argc.
     *
     * @return int
     */
    public function getArgc()
    {
        return $this->argc;
    }

    /**
     * Sets the value of argc.
     *
     * @param int $argc the argc
     *
     * @return self
     */
    public function setArgc($argc)
    {
        $this->argc = $argc;

        return $this;
    }

    /**
     * Gets the value of argv.
     *
     * @return array
     */
    public function getArgv()
    {
        return $this->argv;
    }

    /**
     * Sets the value of argv.
     *
     * @param array $argv the argv
     *
     * @return self
     */
    public function setArgv(array $argv)
    {
        $this->argv = $argv;

        return $this;
    }

    /**
     * Gets the value of context.
     *
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Sets the value of context.
     *
     * @param array $context the context
     *
     * @return self
     */
    public function setContext(array $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Gets the value of headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Sets the value of headers.
     *
     * @param array $headers the headers
     *
     * @return self
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Gets the value of body.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Sets the value of body.
     *
     * @param string $body the body
     *
     * @return self
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }
}
