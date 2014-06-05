<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use Buzz\Client\ClientInterface;
use Buzz\Message\Request as HttpRequest;
use Buzz\Message\Response;
use HWI\Bundle\OAuthBundle\OAuth\RequestDataStorageInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Http\HttpUtils;

class GenericOAuth2ResourceOwnerSpec extends ObjectBehavior
{
    function let(ClientInterface $httpClient, HttpUtils $httpUtils, OptionsResolverInterface $resolver, RequestDataStorageInterface $storage)
    {
        $options = array(
            'csrf'              => false,
            'client_id'         => 'client_id',
            'client_secret'     => 'client_secret',
            'scope'             => null,
            'authorization_url' => 'authorization_url',
            'access_token_url'  => 'access_token_url',
            'infos_url'         => 'infos_url',
            'revoke_token_url'  => 'revoke_token_url',
        );

        $resolver->resolve($options)->willReturn($options);

        $this->beConstructedWith($httpClient, $httpUtils, $options, 'oauth2', $storage);
    }

    function it_is_a_generic_oauth2_resource_owner()
    {
        $this->shouldHaveType('HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\AbstractResourceOwner');
    }

    function it_should_return_authorization_url($redirectUri = 'http://localhost')
    {
        $this->getAuthorizationUrl($redirectUri)->shouldReturn('authorization_url?response_type=code&client_id=client_id&redirect_uri=http%3A%2F%2Flocalhost');
    }

    function it_should_return_true_if_code_param_is_sent(Request $request, ParameterBag $query)
    {
        $query->has('code')->willReturn(true);

        $request->query = $query;

        $this->handles($request)->shouldReturn(true);
    }

    function it_should_return_false_if_code_param_is_not_sent(Request $request, ParameterBag $query)
    {
        $query->has('code')->willReturn(false);

        $request->query = $query;

        $this->handles($request)->shouldReturn(false);
    }

    function it_should_return_access_token(
        Request $request, ParameterBag $query, ClientInterface $httpClient, HttpRequest $httpRequest, Response $httpResponse
    )
    {
        $query->get('code')->willReturn('code');

        $request->query = $query;

        $httpResponse->getContent()->willReturn(array('access_token' => 'access_token'));

        $parameters = array(
            'code'          => 'code',
            'grant_type'    => 'authorization_code',
            'client_id'     => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_uri'  => 'http://localhost',
        );

        $httpRequest->setHeaders(Argument::any())->shouldBeCalled();
        $httpRequest->setContent($parameters)->shouldBeCalled();

        $httpClient->send(Argument::type('\Buzz\Message\Request'), Argument::type('\Buzz\Message\Response'))->shouldBeCalled();

        $this->getAccessToken($request, 'http://localhost')->shouldReturn(array('access_token' => 'access_token'));
    }

    function it_should_return_refresh_token(ClientInterface $httpClient, HttpRequest $httpRequest, Response $httpResponse)
    {
        $httpResponse->getContent()->willReturn(array('access_token' => 'access_token'));

        $parameters = array(
            'refresh_token' => 'refresh_token',
            'grant_type'    => 'refresh_token',
            'client_id'     => 'client_id',
            'client_secret' => 'client_secret',
        );

        $httpRequest->setHeaders(Argument::any())->shouldBeCalled();
        $httpRequest->setContent($parameters)->shouldBeCalled();

        $httpClient->send(Argument::type('\Buzz\Message\Request'), Argument::type('\Buzz\Message\Response'))->shouldBeCalled();

        $this->refreshAccessToken('refresh_token')->shouldReturn(array('access_token' => 'access_token'));
    }

    function it_should_revoke_correct_token(ClientInterface $httpClient, HttpRequest $httpRequest, Response $httpResponse)
    {
        $httpResponse->getStatusCode()->willReturn(200);

        $parameters = array(
            'client_id'     => 'client_id',
            'client_secret' => 'client_secret',
        );

        $httpRequest->setHeaders(Argument::any())->shouldBeCalled();
        $httpRequest->setContent($parameters)->shouldBeCalled();

        $httpClient->send(Argument::type('\Buzz\Message\Request'), Argument::type('\Buzz\Message\Response'))->shouldBeCalled();

        $this->revokeToken('token')->shouldReturn(true);
    }

    function it_should_not_revoke_unknown_token(ClientInterface $httpClient, HttpRequest $httpRequest, Response $httpResponse)
    {
        $httpResponse->getStatusCode()->willReturn(404);

        $parameters = array(
            'client_id'     => 'client_id',
            'client_secret' => 'client_secret',
        );

        $httpRequest->setHeaders(Argument::any())->shouldBeCalled();
        $httpRequest->setContent($parameters)->shouldBeCalled();

        $httpClient->send(Argument::type('\Buzz\Message\Request'), Argument::type('\Buzz\Message\Response'))->shouldBeCalled();

        $this->revokeToken('unknown')->shouldReturn(false);
    }

    function it_should_mark_csrf_token_as_valid_when_csrf_is_disabled()
    {
        $this->isCsrfTokenValid('token')->shouldReturn(true);
    }

    function it_should_return_resource_owner_name()
    {
        $this->setName('generic_oauth2');

        $this->getName()->shouldReturn('generic_oauth2');
    }
}
