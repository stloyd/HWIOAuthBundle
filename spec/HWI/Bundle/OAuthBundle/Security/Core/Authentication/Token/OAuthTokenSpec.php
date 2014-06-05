<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token;

use PhpSpec\ObjectBehavior;

class OAuthTokenSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('access_token', array());
    }

    function it_is_a_security_authentication_token()
    {
        $this->shouldHaveType('HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthTokenInterface');
    }

    function it_should_return_empty_credentials()
    {
        $this->getCredentials()->shouldReturn('');
    }

    function it_should_return_new_access_token_if_set()
    {
        $this->setAccessToken('token');

        $this->getAccessToken()->shouldReturn('token');
    }

    function it_should_return_new_access_token_if_raw_token_was_changed_with_array()
    {
        $this->setRawToken(array('access_token' => 'new_token'));

        $this->getRawToken()->shouldReturn(array('access_token' => 'new_token'));
        $this->getAccessToken()->shouldReturn('new_token');
    }

    function it_should_change_access_token_if_raw_token_was_changed_with_string()
    {
        $this->setRawToken('brand_new_token');

        $this->getRawToken()->shouldReturn(array('access_token' => 'brand_new_token'));
        $this->getAccessToken()->shouldReturn('brand_new_token');
    }

    function it_should_return_new_refresh_token_if_set()
    {
        $this->setRefreshToken('refresh_token');

        $this->getRefreshToken()->shouldReturn('refresh_token');
    }

    function it_should_return_new_token_secret_if_set()
    {
        $this->setTokenSecret('secret');

        $this->getTokenSecret()->shouldReturn('secret');
    }

    function it_should_return_false_if_no_expiration_was_set()
    {
        $this->isExpired()->shouldReturn(false);
    }

    function it_should_return_true_if_expiration_was_set_and_is_smaller_than_30_sec()
    {
        $this->setExpiresIn(10);

        $this->isExpired()->shouldReturn(true);
    }

    function it_should_return_new_resource_owner_name_if_set()
    {
        $this->setResourceOwnerName('resource');

        $this->getResourceOwnerName()->shouldReturn('resource');
    }
}
