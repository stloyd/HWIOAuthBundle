<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\HWI\Bundle\OAuthBundle\OAuth\Response;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthTokenInterface;
use PhpSpec\ObjectBehavior;

class PathUserResponseSpec extends ObjectBehavior
{
    function it_is_a_oauth_response()
    {
        $this->shouldHaveType('HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface');
    }

    function it_should_support_array_as_a_response()
    {
        $this->setResponse(array('foo' => 'bar'));

        $this->getResponse()->shouldReturn(array('foo' => 'bar'));
    }

    function it_should_support_json_as_a_response()
    {
        $this->setResponse(json_encode(array('foo' => 'bar')));

        $this->getResponse()->shouldReturn(array('foo' => 'bar'));
    }

    function it_should_return_resource_owner(ResourceOwnerInterface $resourceOwner)
    {
        $this->setResourceOwner($resourceOwner);

        $this->getResourceOwner()->shouldReturn($resourceOwner);
    }

    function it_should_return_data_from_oauth_token_object(OAuthTokenInterface $token)
    {
        $this->setOAuthToken($token);

        $token->getAccessToken()->willReturn('access_token');
        $this->getAccessToken()->shouldReturn('access_token');

        $token->getRefreshToken()->willReturn('refresh_token');
        $this->getRefreshToken()->shouldReturn('refresh_token');

        $token->getTokenSecret()->willReturn('top_secret');
        $this->getTokenSecret()->shouldReturn('top_secret');

        $token->getExpiresIn()->willReturn(123456);
        $this->getExpiresIn()->shouldReturn(123456);
    }

    function it_should_return_null_if_no_response_was_set()
    {
        $this->getUsername()->shouldReturn(null);
        $this->getNickname()->shouldReturn(null);
        $this->getRealName()->shouldReturn(null);
        $this->getEmail()->shouldReturn(null);
    }

    function it_should_return_null_if_path_was_not_found()
    {
        $this->setResponse(array());

        $this->getUsername()->shouldReturn(null);
        $this->getNickname()->shouldReturn(null);
        $this->getRealName()->shouldReturn(null);
        $this->getEmail()->shouldReturn(null);
    }

    function it_should_merge_given_paths_with_default_ones()
    {
        $this->setPaths(array('foo' => 'bar'));

        $this->getPaths()->shouldReturn(array(
            'identifier'     => null,
            'nickname'       => null,
            'realname'       => null,
            'email'          => null,
            'profilepicture' => null,
            'foo'            => 'bar'
        ));
    }

    function it_should_support_path_merging_for_single_field()
    {
        $this->setPaths(array('realname' => array('first_name', 'last_name')));

        $this->setResponse(array('first_name' => 'foo', 'last_name' => 'bar'));

        $this->getRealName()->shouldReturn('foo bar');

        $this->setResponse(array('first_name' => null, 'last_name' => 'bar'));

        $this->getRealName()->shouldReturn('bar');
    }

    function it_should_support_path_nesting_for_single_field()
    {
        $this->setPaths(array('nickname' => 'foo.bar'));
        $this->setResponse(array('foo' => array('bar' => 'qux')));

        $this->getNickname()->shouldReturn('qux');
    }
}
