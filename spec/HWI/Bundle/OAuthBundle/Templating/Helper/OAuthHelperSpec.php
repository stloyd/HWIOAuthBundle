<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\HWI\Bundle\OAuthBundle\Templating\Helper;

use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use Symfony\Component\HttpFoundation\Request;
use PhpSpec\ObjectBehavior;

class OAuthHelperSpec extends ObjectBehavior
{
    function let(OAuthUtils $oauthUtils)
    {
        $this->beConstructedWith($oauthUtils);
    }

    function it_is_a_templating_helper()
    {
        $this->shouldHaveType('Symfony\Component\Templating\Helper\Helper');
    }

    function it_should_return_array_of_resource_owners(OAuthUtils $oauthUtils)
    {
        $oauthUtils->getResourceOwners()->willReturn(array());

        $this->getResourceOwners()->shouldReturn(array());
    }

    function it_should_return_login_url(OAuthUtils $oauthUtils, Request $request)
    {
        $this->setRequest($request);

        $oauthUtils->getLoginUrl($request, 'url')->willReturn('login_url');

        $this->getLoginUrl('url')->shouldReturn('login_url');
    }

    function it_should_return_authorization_url(OAuthUtils $oauthUtils, Request $request)
    {
        $this->setRequest($request);

        $oauthUtils->getAuthorizationUrl($request, 'url', 'redirect_url', array())->willReturn('authorization_url');

        $this->getAuthorizationUrl('url', 'redirect_url')->shouldReturn('authorization_url');
    }

    function it_should_return_helper_name()
    {
        $this->getName()->shouldReturn('hwi_oauth');
    }
}
