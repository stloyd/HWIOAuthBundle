<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\HWI\Bundle\OAuthBundle\OAuth\RequestDataStorage;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionStorageSpec extends ObjectBehavior
{
    function let(SessionInterface $session)
    {
        $this->beConstructedWith($session);
    }

    function it_is_a_request_data_storage()
    {
        $this->shouldHaveType('HWI\Bundle\OAuthBundle\OAuth\RequestDataStorageInterface');
    }

    function it_should_return_array_while_fetching(SessionInterface $session, ResourceOwnerInterface $resourceOwner)
    {
        $resourceOwner->getName()->willReturn('name');
        $resourceOwner->getOption('client_id')->willReturn('client_id');

        $session->get('_hwi_oauth.name.client_id.token.oauth_token')->willReturn(array('oauth_token' => 'oauth_token'));
        $session->remove('_hwi_oauth.name.client_id.token.oauth_token')->shouldBeCalled();

        $this->fetch($resourceOwner, 'oauth_token')->shouldReturn(array('oauth_token' => 'oauth_token'));
    }

    function it_should_not_allow_using_unknown_key(SessionInterface $session, ResourceOwnerInterface $resourceOwner)
    {
        $resourceOwner->getName()->willReturn('name');
        $resourceOwner->getOption('client_id')->willReturn('client_id');

        $session->get('_hwi_oauth.name.client_id.token.unknown_key')->willReturn(null);

        $this->shouldThrow('\InvalidArgumentException')->duringFetch($resourceOwner, 'unknown_key');
    }

    function it_should_save_given_value(SessionInterface $session, ResourceOwnerInterface $resourceOwner)
    {
        $resourceOwner->getName()->willReturn('name');
        $resourceOwner->getOption('client_id')->willReturn('client_id');

        $session->set('_hwi_oauth.name.client_id.token.oauth_token', array('oauth_token' => 'oauth_token'))->shouldBeCalled();

        $this->save($resourceOwner, array('oauth_token' => 'oauth_token'));
    }

    function it_should_not_allow_saving_empty_value_as_token(SessionInterface $session, ResourceOwnerInterface $resourceOwner)
    {
        $resourceOwner->getName()->willReturn('name');
        $resourceOwner->getOption('client_id')->willReturn('client_id');

        $session->set('_hwi_oauth.name.client_id.token.unknown', 'unknown')->shouldNotBeCalled();

        $this->shouldThrow('\InvalidArgumentException')->duringSave($resourceOwner, 'unknown');
    }

    function it_should_allow_saving_empty_value_as_not_token(SessionInterface $session, ResourceOwnerInterface $resourceOwner)
    {
        $resourceOwner->getName()->willReturn('name');
        $resourceOwner->getOption('client_id')->willReturn('client_id');

        $session->set('_hwi_oauth.name.client_id.data.empty', 'empty')->shouldBeCalled();

        $this->save($resourceOwner, 'empty', 'data');
    }
}
