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

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\User\UserInterface;

class EntityUserProviderSpec extends ObjectBehavior
{
    function let(ManagerRegistry $registry, ObjectManager $om, ObjectRepository $repository, $class = 'Symfony\Component\Security\Core\User\UserInterface')
    {
        $registry->getManager(null)->willReturn($om);

        $om->getRepository($class)->willReturn($repository);

        $this->beConstructedWith($registry, $class, array('github' => 'githubId'));
    }

    function it_is_a_entity_user_provider()
    {
        $this->shouldHaveType('Symfony\Component\Security\Core\User\UserProviderInterface');
        $this->shouldHaveType('HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface');
    }

    function it_should_load_user_with_given_username(ObjectRepository $repository, UserInterface $user)
    {
        $repository->findOneBy(array('username' => 'username'))->willReturn($user);

        $this->loadUserByUsername('username')->shouldReturn($user);
    }

    function it_should_not_load_user_with_given_username(ObjectRepository $repository)
    {
        $repository->findOneBy(array('username' => 'not_found'))->willReturn(null);

        $this->shouldThrow('Symfony\Component\Security\Core\Exception\UsernameNotFoundException')->duringLoadUserByUsername('not_found');
    }

    function it_should_load_user_with_nickname_from_response(
        UserResponseInterface $response, ResourceOwnerInterface $resourceOwner, ObjectRepository $repository, UserInterface $user
    )
    {
        $resourceOwner->getName()->willReturn('github');

        $response->getUsername()->willReturn('username');
        $response->getResourceOwner()->willReturn($resourceOwner);

        $repository->findOneBy(array('githubId' => 'username'))->willReturn($user);

        $this->loadUserByOAuthUserResponse($response)->shouldReturn($user);
    }

    function it_should_not_load_user_for_unknown_resource_owner(
        UserResponseInterface $response, ResourceOwnerInterface $resourceOwner
    )
    {
        $resourceOwner->getName()->willReturn('unknown');

        $response->getResourceOwner()->willReturn($resourceOwner);

        $this->shouldThrow('\RuntimeException')->duringLoadUserByOAuthUserResponse($response);
    }

    function it_should_not_load_non_existing_user(
        UserResponseInterface $response, ResourceOwnerInterface $resourceOwner, ObjectRepository $repository
    )
    {
        $resourceOwner->getName()->willReturn('github');

        $response->getUsername()->willReturn('username');
        $response->getResourceOwner()->willReturn($resourceOwner);

        $repository->findOneBy(array('githubId' => 'username'))->willReturn(null);

        $this->shouldThrow('Symfony\Component\Security\Core\Exception\UsernameNotFoundException')->duringLoadUserByOAuthUserResponse($response);
    }

    function it_should_reload_user_with_identifier_from_current_user(
        ObjectRepository $repository, UserInterface $oldUser, UserInterface $newUser, PropertyAccessor $accessor
    )
    {
        $accessor->isReadable($oldUser, 'id')->willReturn(true);
        $accessor->getValue($oldUser, 'id')->willReturn(1);

        $repository->findOneBy(array('id' => 1))->willReturn($newUser);

        $this->refreshUser($oldUser)->shouldReturn($newUser);
    }

    function it_should_not_reload_user_for_unknown_user(
        UserInterface $oldUser, PropertyAccessor $accessor
    )
    {
        $accessor->isReadable($oldUser, 'id')->willReturn(false);

        $this->shouldThrow('Symfony\Component\Security\Core\Exception\UnsupportedUserException')->duringRefreshUser($oldUser);
    }

    function it_should_not_reload_user_for_user_with_unknown_identifier(
        ObjectRepository $repository, UserInterface $oldUser, PropertyAccessor $accessor
    )
    {
        $accessor->isReadable($oldUser, 'id')->willReturn(true);
        $accessor->getValue($oldUser, 'id')->willReturn(2);

        $repository->findOneBy(array('id' => 2))->willReturn(null);

        $this->shouldThrow('Symfony\Component\Security\Core\Exception\UsernameNotFoundException')->duringRefreshUser($oldUser);
    }
}
