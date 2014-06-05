<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\HWI\Bundle\OAuthBundle\Security\Core\Authentication\Provider;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthTokenInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class OAuthProviderSpec extends ObjectBehavior
{
    function let(OAuthAwareUserProviderInterface $userProvider, ResourceOwnerMap $resourceOwnerMap, UserCheckerInterface $userChecker)
    {
        $this->beConstructedWith($userProvider, $resourceOwnerMap, $userChecker);
    }

    function it_is_a_oauth_response()
    {
        $this->shouldHaveType('Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface');
    }

    function it_should_return_true_if_given_token_is_correct_instance(OAuthTokenInterface $token)
    {
        $this->supports($token)->shouldReturn(true);
    }

    function it_should_not_return_true_if_given_token_is_not_correct_instance(TokenInterface $token)
    {
        $this->supports($token)->shouldReturn(false);
    }

    function it_should_return_new_token_if_user_is_found(
        ResourceOwnerMap $resourceOwnerMap,
        ResourceOwnerInterface $resourceOwner,
        UserResponseInterface $response,
        OAuthAwareUserProviderInterface $userProvider,
        UserCheckerInterface $userChecker,
        OAuthTokenInterface $oldToken,
        UserInterface $user,
        OAuthTokenInterface $newToken
    )
    {
        $oldToken->getResourceOwnerName()->willReturn('resource');
        $oldToken->getRawToken()->willReturn(array('access_token' => 'token'));

        $resourceOwner->getUserInformation(array('access_token' => 'token'))->willReturn($response);
        $resourceOwner->getName()->willReturn('resource');

        $resourceOwnerMap->getResourceOwnerByName('resource')->willReturn($resourceOwner);
        $resourceOwnerMap->createOAuthToken($resourceOwner, $oldToken, $user)->willReturn($newToken);

        $userProvider->loadUserByOAuthUserResponse($response)->willReturn($user);

        $userChecker->checkPostAuth($user)->shouldBeCalled();

        $this->authenticate($oldToken)->shouldReturn($newToken);
    }

    function it_should_throw_exception_if_user_was_not_found(
        ResourceOwnerMap $resourceOwnerMap,
        ResourceOwnerInterface $resourceOwner,
        UserResponseInterface $response,
        OAuthAwareUserProviderInterface $userProvider,
        UserCheckerInterface $userChecker,
        OAuthTokenInterface $oldToken,
        UserInterface $user
    )
    {
        $oldToken->getResourceOwnerName()->willReturn('resource');
        $oldToken->getRawToken()->willReturn(array('access_token' => 'token'));

        $resourceOwner->getUserInformation(array('access_token' => 'token'))->willReturn($response);

        $resourceOwnerMap->getResourceOwnerByName('resource')->willReturn($resourceOwner);
        $resourceOwnerMap->createOAuthToken($resourceOwner, $oldToken, $user)->shouldNotBeCalled();

        $userProvider->loadUserByOAuthUserResponse($response)->willThrow('\HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException');

        $userChecker->checkPostAuth($user)->shouldNotBeCalled();

        $this
            ->shouldThrow('\HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException')
            ->duringAuthenticate($oldToken)
        ;
    }
}
