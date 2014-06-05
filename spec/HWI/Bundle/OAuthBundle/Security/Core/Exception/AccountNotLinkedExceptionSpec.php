<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\HWI\Bundle\OAuthBundle\Security\Core\Exception;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthTokenInterface;
use PhpSpec\ObjectBehavior;

class AccountNotLinkedExceptionSpec extends ObjectBehavior
{
    function it_is_a_security_exception()
    {
        $this->shouldHaveType('HWI\Bundle\OAuthBundle\Security\Core\Exception\OAuthAwareExceptionInterface');
    }

    function it_should_return_data_from_token_if_set(OAuthTokenInterface $token)
    {
        $this->setToken($token);

        $token->getAccessToken()->willReturn('token');
        $this->getAccessToken()->shouldReturn('token');

        $token->getRawToken()->willReturn(array('access_token' =>'token'));
        $this->getRawToken()->shouldReturn(array('access_token' =>'token'));

        $token->getRefreshToken()->willReturn('refresh_token');
        $this->getRefreshToken()->shouldReturn('refresh_token');

        $token->getExpiresIn()->willReturn(123456);
        $this->getExpiresIn()->shouldReturn(123456);

        $token->getTokenSecret()->willReturn('secret_token');
        $this->getTokenSecret()->shouldReturn('secret_token');
    }

    function it_should_return_resource_owner_name_if_set()
    {
        $this->setResourceOwnerName('resource');

        $this->getResourceOwnerName()->shouldReturn('resource');
    }
}
