<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Http;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthTokenInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * ResourceOwnerMap. Holds several resource owners for a firewall. Lazy
 * loads the appropriate resource owner when requested.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class ResourceOwnerMap extends ContainerAware
{
    /**
     * @var HttpUtils
     */
    protected $httpUtils;

    /**
     * @var array
     */
    protected $resourceOwners;

    /**
     * @var array
     */
    protected $possibleResourceOwners;

    /**
     * Constructor.
     *
     * @param HttpUtils $httpUtils              HttpUtils
     * @param array     $possibleResourceOwners Array with possible resource owners names.
     * @param array     $resourceOwners         Array with configured resource owners.
     */
    public function __construct(HttpUtils $httpUtils, array $possibleResourceOwners, $resourceOwners)
    {
        $this->httpUtils              = $httpUtils;
        $this->possibleResourceOwners = $possibleResourceOwners;
        $this->resourceOwners         = $resourceOwners;
    }

    /**
     * Gets the appropriate resource owner given the name.
     *
     * @param string $name
     *
     * @return null|ResourceOwnerInterface
     */
    public function getResourceOwnerByName($name)
    {
        if (!isset($this->resourceOwners[$name])) {
            return null;
        }
        if (!in_array($name, $this->possibleResourceOwners)) {
            return null;
        }

        $service = $this->container->get('hwi_oauth.resource_owner.'.$name);

        return $service;
    }

    /**
     * Gets the appropriate resource owner for a request.
     *
     * @param Request $request
     *
     * @return null|array
     */
    public function getResourceOwnerByRequest(Request $request)
    {
        foreach ($this->resourceOwners as $name => $checkPath) {
            if ($this->httpUtils->checkRequestPath($request, $checkPath)) {
                return array($this->getResourceOwnerByName($name), $checkPath);
            }
        }

        return null;
    }

    /**
     * Gets the check path for given resource name.
     *
     * @param string $name
     *
     * @return null|string
     */
    public function getResourceOwnerCheckPath($name)
    {
        if (isset($this->resourceOwners[$name])) {
            return $this->resourceOwners[$name];
        }

        return null;
    }

    /**
     * Get all the resource owners.
     *
     * @return array
     */
    public function getResourceOwners()
    {
        return $this->resourceOwners;
    }

    /**
     * @param ResourceOwnerInterface $resourceOwner
     * @param OAuthTokenInterface    $token
     * @param UserInterface          $user
     *
     * @return OAuthTokenInterface
     */
    public function createOAuthToken(ResourceOwnerInterface $resourceOwner, OAuthTokenInterface $token, UserInterface $user)
    {
        $token = new OAuthToken($token->getRawToken(), $user->getRoles());
        $token->setResourceOwnerName($resourceOwner->getName());
        $token->setUser($user);
        $token->setAuthenticated(true);

        return $token;
    }
}
