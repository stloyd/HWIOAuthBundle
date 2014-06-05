<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\HWI\Bundle\OAuthBundle\Security\Core\User;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\User\UserInterface;

class OAuthUserProviderSpec extends ObjectBehavior
{
    function it_is_a_oauth_user_provider()
    {
        $this->shouldHaveType('Symfony\Component\Security\Core\User\UserProviderInterface');
        $this->shouldHaveType('HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface');
    }

    function it_should_load_oauth_user_with_given_username()
    {
        $this->loadUserByUsername('username')->shouldReturn(new OAuthUser('username'));
    }

    function it_should_load_oauth_user_with_nickname_from_response(UserResponseInterface $response)
    {
        $response->getNickname()->willReturn('username');

        $this->loadUserByOAuthUserResponse($response)->shouldReturn(Argument::type('\HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser'));
    }

    function it_should_reload_oauth_user_with_identifier(OAuthUser $user)
    {
        $user->getUsername()->willReturn('nickname');

        $this->refreshUser($user)->shouldReturn(Argument::type('\HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser'));
    }

    function it_should_not_reload_symfony_user(UserInterface $user)
    {
        $user->getUsername()->shouldNotBeCalled();

        $this->shouldThrow('Symfony\Component\Security\Core\Exception\UnsupportedUserException')->duringRefreshUser($user);
    }
}
