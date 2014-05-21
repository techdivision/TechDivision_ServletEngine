<?php

/**
 * TechDivision\ServletEngine\Authentication\AuthenticationManager
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
 * @subpackage Authentication
 * @author     Florian Sydekum <fs@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletEngine\Authentication;

use TechDivision\Servlet\ServletRequest;
use TechDivision\Servlet\ServletResponse;

/**
 * The authentication manager handles request which need Http authentication.
 *
 * @category   Appserver
 * @package    TechDivision_ServletEngine
 * @subpackage Authentication
 * @author     Florian Sydekum <fs@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class StandardAuthenticationManager implements AuthenticationManager
{
    
    /**
     * Handles request in order to authenticate.
     *
     * @param \TechDivision\Servlet\ServletRequest  $servletRequest  The request instance
     * @param \TechDivision\Servlet\ServletResponse $servletResponse The response instance
     *
     * @return boolean TRUE if the authentication has been successfull, else FALSE
     */
    public function handleRequest(ServletRequest $servletRequest, ServletResponse $servletResponse)
    {
        
        // load the actual context instance
        $context = $servletRequest->getContext();
        
        // iterate over all servlets and return the matching one
        foreach ($context->getServletContext()->getSecuredUrlConfigs() as $securedUrlConfig) {
        
            // extract URL pattern and authentication configuration
            list ($urlPattern, $auth) = array_values($securedUrlConfig);
        
            if (fnmatch($urlPattern, $servletRequest->getServletPath())) {

                // load security configuration
                $configuredAuthType = $securedUrlConfig['auth']['auth_type'];
                
                // check the authentication type
                switch ($configuredAuthType) {
                    case "Basic":
                        $authImplementation =  'TechDivision\ServletEngine\Authentication\BasicAuthentication';
                        break;
                    case "Digest":
                        $authImplementation =  'TechDivision\ServletEngine\Authentication\DigestAuthentication';
                        break;
                    default:
                        throw new \Exception(sprintf('Unknown authentication type %s', $configuredAuthType));
                }
                
                // initialize the authentication manager
                $auth = new $authImplementation($securedUrlConfig);
                $auth->init($servletRequest, $servletResponse);
                
                // try to authenticate the request
                return $auth->authenticate();
            }
        }
    }
}
