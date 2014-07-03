<?php

/**
 * TechDivision\ServletEngine\Http\Response
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletEngine
 * @subpackage Http
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletEngine\Http;

use TechDivision\Http\HttpResponseInterface;
use TechDivision\Http\HttpProtocol;
use TechDivision\Servlet\Http\Cookie;
use TechDivision\Servlet\Http\HttpServletResponse;
use TechDivision\Storage\GenericStackable;

/**
 * A servlet request implementation.
 *
 * @category   Appserver
 * @package    TechDivision_ServletEngine
 * @subpackage Http
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class Response extends GenericStackable implements HttpServletResponse
{

    /**
     * The cookies stored in the response.
     *
     * @var \TechDivision\Storage\GenericStackable
     */
    protected $cookies;

    /**
     * The Http response instance.
     *
     * @var \TechDivision\Http\HttpResponseInteface
     */
    protected $httpResponse;

    /**
     * The body stream, a string because we can't serialize memory stream here.
     *
     * @var string
     */
    protected $bodyStream;


    /**
     * Initialize the servlet response.
     *
     * @return void
     */
    public function __construct()
    {
        $this->cookies = new GenericStackable();
    }

    /**
     * Injects the Http response instance.
     *
     * @param \TechDivision\Http\HttpResponseInterface $httpResponse The Http response instance
     *
     * @return void
     */
    public function injectHttpResponse(HttpResponseInterface $httpResponse)
    {
        $this->httpResponse = $httpResponse;
        $this->initDefaultHeaders();
    }

    /**
     * Initializes the response with the default headers.
     *
     * @return void
     */
    protected function initDefaultHeaders()
    {
        // add this header to prevent .php request to be cached
        $this->addHeader(HttpProtocol::HEADER_EXPIRES, '19 Nov 1981 08:52:00 GMT');
        $this->addHeader(HttpProtocol::HEADER_CACHE_CONTROL, 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->addHeader(HttpProtocol::HEADER_PRAGMA, 'no-cache');

        // set per default text/html mimetype
        $this->addHeader(HttpProtocol::HEADER_CONTENT_TYPE, 'text/html');
    }

    /**
     * Returns the Http response instance.
     *
     * @return \TechDivision\Http\HttpResponseInterface The Http response instance
     */
    public function getHttpResponse()
    {
        return $this->httpResponse;
    }

    /**
     * Adds a cookie
     *
     * @param \TechDivision\Servlet\Http\Cookie $cookie The cookie instance to add
     *
     * @return void
     */
    public function addCookie(Cookie $cookie)
    {
        $this->cookies[] = $cookie;
    }

    /**
     * Returns TRUE if the response already has a cookie with the passed
     * name, else FALSE.
     *
     * @param string $cookieName Name of the cookie to be checked
     *
     * @return boolean TRUE if the response already has the cookie, else FALSE
     */
    public function hasCookie($cookieName)
    {
        foreach ($this->getCookies() as $cookie) {
            if ($cookie->getName() === $cookieName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the cookie with the  a cookie
     *
     * @param string $cookieName Name of the cookie to be checked
     *
     * @return \TechDivision\Servlet\Http\Cookie $cookie The cookie instance
     */
    public function getCookie($cookieName)
    {
        foreach ($this->getCookies() as $cookie) {
            if ($cookie->getName() === $cookieName) {
                return $cookie;
            }
        }
    }

    /**
     * Returns the cookies array.
     *
     * @return array
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * Return content
     *
     * @return string $content
     */
    public function getBodyContent()
    {
        return $this->bodyStream;
    }

    /**
     * Reset the body stream
     *
     * @return void
     */
    public function resetBodyStream()
    {
        $this->bodyStream = '';
    }

    /**
     * Returns the body stream as a resource.
     *
     * @return resource The body stream
     */
    public function getBodyStream()
    {
        return $this->bodyStream;
    }

    /**
     * Appends the content.
     *
     * @param string $content The content to append
     *
     * @return void
     */
    public function appendBodyStream($content)
    {
        $this->bodyStream .= $content;
    }

    /**
     * Copies a source stream to body stream.
     *
     * @param resource $sourceStream The file pointer to source stream
     * @param integer  $maxlength    The max length to read from source stream
     * @param integer  $offset       The offset from source stream to read
     *
     * @return integer The total number of bytes copied
     */
    public function copyBodyStream($sourceStream, $maxlength = null, $offset = 0)
    {

        // check if a stream has been passed
        if (is_resource($sourceStream)) {
            if ($offset && $maxlength) {
                $this->bodyStream = stream_get_contents($sourceStream, $maxlength, $offset);
            }
            if (!$offset && $maxlength) {
                $this->bodyStream = stream_get_contents($sourceStream, $maxlength);
            }
            if (!$offset && !$maxlength) {
                $this->bodyStream = stream_get_contents($sourceStream);
            }
        } else { // if not, copy the string
            $this->bodyStream = substr($sourceStream, $offset, $maxlength);

        }

        // return the sring length
        return strlen($this->bodyStream);
    }

    /**
     * Resetss the stream resource pointing to body content.
     *
     * @param resource $bodyStream The body content stream resource
     *
     * @return void
     */
    public function setBodyStream($bodyStream)
    {
        $this->copyBodyStream($bodyStream);
    }

    /**
     * Sets the headers.
     *
     * @param array $headers The headers array
     *
     * @return void
     */
    public function setHeaders(array $headers)
    {
        $this->getHttpResponse()->setHeaders($headers);
    }

    /**
     * Returns the headers array.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->getHttpResponse()->getHeaders();
    }

    /**
     * Adds a header to the internal array.
     *
     * @param string     $name   The header label e.g. Accept or Content-Length
     * @param string|int $value  The header value
     * @param boolean    $append If TRUE and a header with the passed name already exists, the value will be appended
     *
     * @return void
     */
    public function addHeader($name, $value, $append = false)
    {
        $this->getHttpResponse()->addHeader($name, $value, $append);
    }

    /**
     * Returns header info by given name
     *
     * @param string $name The headers name to return
     *
     * @return string|null
     */
    public function getHeader($name)
    {
        return $this->getHttpResponse()->getHeader($name);
    }

    /**
     * Returns response http version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->getHttpResponse()->getVersion();
    }

    /**
     * Removes one single header from the headers array.
     *
     * @param string $name The header to remove
     *
     * @return void
     */
    public function removeHeader($name)
    {
        $this->getHttpResponse()->removeHeader($name);
    }

    /**
     * Checks if header exists by given name.
     *
     * @param string $name The header name to check
     *
     * @return boolean TRUE if the header is available, else FALSE
     */
    public function hasHeader($name)
    {
        return $this->getHttpResponse()->hasHeader($name);
    }

    /**
     * Set's the http response status code
     *
     * @param int $code The status code to set
     *
     * @return void
     */
    public function setStatusCode($code)
    {
        $this->getHttpResponse()->setStatusCode($code);
    }

    /**
     * Return's the response status code
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->getHttpResponse()->getStatusCode();
    }

    /**
     * Sets the status reason phrase
     *
     * @param string $statusReasonPhrase The reason phrase
     *
     * @return void
     */
    public function setStatusReasonPhrase($statusReasonPhrase)
    {
        $this->getHttpResponse()->setStatusReasonPhrase($statusReasonPhrase);
    }

    /**
     * Returns the status phrase based on the status code
     *
     * @return string
     */
    public function getStatusReasonPhrase()
    {
        return $this->getHttpResponse()->getStatusReasonPhrase();
    }

    /**
     * Set's state of response
     *
     * @param int $state The state value
     *
     * @return void
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * Return's the current state
     *
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Compares current state with given state
     *
     * @param int $state The state to compare with
     *
     * @return bool Wheater state is equal (true) or not (false)
     */
    public function hasState($state)
    {
        return ($this->state === $state);
    }
}
