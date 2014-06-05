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

use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\User\UserInterface;

class OAuthUserSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('username');
    }

    function it_is_a_user()
    {
        $this->shouldHaveType('Symfony\Component\Security\Core\User\UserInterface');
    }

    function it_should_return_array_of_roles()
    {
        $this->getRoles()->shouldReturn(array('ROLE_USER', 'ROLE_OAUTH_USER'));
    }

    function it_should_not_return_password_nor_salt()
    {
        $this->getPassword()->shouldReturn(null);
        $this->getSalt()->shouldReturn(null);
    }

    function it_should_always_return_true_while_erasing_credentials()
    {
        $this->eraseCredentials()->shouldReturn(true);
    }

    function it_should_return_username_used_in_constructor()
    {
        $this->getUsername()->shouldReturn('username');
    }

    function it_should_return_true_if_user_has_same_username(UserInterface $user)
    {
        $user->getUsername()->willReturn('username');

        $this->equals($user)->shouldReturn(true);
    }

    function it_should_not_return_true_if_user_has_not_same_username(UserInterface $user)
    {
        $user->getUsername()->willReturn('nickname');

        $this->equals($user)->shouldReturn(false);
    }
}
