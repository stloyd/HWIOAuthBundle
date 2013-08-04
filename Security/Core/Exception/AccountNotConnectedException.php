<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Core\Exception;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AccountNotConnectedException extends AuthenticationException implements OAuthAwareExceptionInterface
{
    /**
     * @var string
     */
    protected $resourceOwnerName;
    /**
     * @var OAuthToken
     */
    protected $token;

    /**
     * {@inheritdoc}
     */
    public function getAccessToken()
    {
        return $this->token->getAccessToken();
    }

    /**
     * @return array
     */
    public function getRawToken()
    {
        return $this->token->getRawToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getRefreshToken()
    {
        return $this->token->getRefreshToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiresIn()
    {
        return $this->token->getExpiresIn();
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenSecret()
    {
        return $this->token->getTokenSecret();
    }

    /**
     * {@inheritdoc}
     */
    public function setToken(TokenInterface $token)
    {
        $this->token = $token;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerName()
    {
        return $this->resourceOwnerName;
    }

    /**
     * {@inheritdoc}
     */
    public function setResourceOwnerName($resourceOwnerName)
    {
        $this->resourceOwnerName = $resourceOwnerName;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->token,
            $this->resourceOwnerName,
            parent::serialize(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($str)
    {
        list(
            $this->token,
            $this->resourceOwnerName,
            $parentData
        ) = unserialize($str);
        parent::unserialize($parentData);
    }
}
