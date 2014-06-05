<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
interface OAuthTokenInterface extends TokenInterface
{
    /**
     * @param string $accessToken The OAuth access token
     */
    public function setAccessToken($accessToken);

    /**
     * @return string
     */
    public function getAccessToken();

    /**
     * @param array|string $token The OAuth token
     */
    public function setRawToken($token);

    /**
     * @return array
     */
    public function getRawToken();

    /**
     * @param string $refreshToken The OAuth refresh token
     */
    public function setRefreshToken($refreshToken);

    /**
     * @return string
     */
    public function getRefreshToken();

    /**
     * @param integer $expiresIn The duration in seconds of the access token lifetime
     */
    public function setExpiresIn($expiresIn);

    /**
     * @return integer
     */
    public function getExpiresIn();

    /**
     * @return integer
     */
    public function getExpiresAt();

    /**
     * @param string $tokenSecret
     */
    public function setTokenSecret($tokenSecret);

    /**
     * @return null|string
     */
    public function getTokenSecret();

    /**
     * Returns if the `access_token` is expired.
     *
     * @return boolean True if the `access_token` is expired.
     */
    public function isExpired();

    /**
     * Get the resource owner name.
     *
     * @return string
     */
    public function getResourceOwnerName();

    /**
     * Set the resource owner name.
     *
     * @param string $resourceOwnerName
     */
    public function setResourceOwnerName($resourceOwnerName);
}
