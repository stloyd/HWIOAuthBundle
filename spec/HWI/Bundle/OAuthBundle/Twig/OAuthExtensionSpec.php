<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\HWI\Bundle\OAuthBundle\Twig\Extension;

use HWI\Bundle\OAuthBundle\Templating\Helper\OAuthHelper;
use PhpSpec\ObjectBehavior;

class OAuthExtensionSpec extends ObjectBehavior
{
    function let(OAuthHelper $helper)
    {
        $this->beConstructedWith($helper);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldHaveType('\Twig_Extension');
    }

    function it_should_return_array_of_declared_functions()
    {
        $this->getFunctions()->shouldHaveCount(3);
    }

    function it_should_return_array_of_resource_owners(OAuthHelper $helper)
    {
        $helper->getResourceOwners()->willReturn(array());

        $this->getResourceOwners()->shouldReturn(array());
    }

    function it_should_return_login_url(OAuthHelper $helper)
    {
        $helper->getLoginUrl('url')->willReturn('login_url');

        $this->getLoginUrl('url')->shouldReturn('login_url');
    }

    function it_should_return_authorization_url(OAuthHelper $helper)
    {
        $helper->getAuthorizationUrl('url', 'redirect_url', array())->willReturn('authorization_url');

        $this->getAuthorizationUrl('url', 'redirect_url')->shouldReturn('authorization_url');
    }

    function it_should_return_extension_name()
    {
        $this->getName()->shouldReturn('hwi_oauth');
    }
}
