<?php

declare(strict_types=1);

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Functional\Controller;

use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomOAuthToken;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Client\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;

final class LoginControllerTest extends WebTestCase
{
    use ProphecyTrait;

    public function testLoginPage(): void
    {
        $client = self::createClient();
        $httpClient = $this->prophesize(ClientInterface::class);
        self::$container->set(ClientInterface::class, $httpClient->reveal());

        $crawler = $client->request('GET', '/login_hwi/');

        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame('google', $crawler->filter('a')->text(), $response->getContent());
    }

    public function testRedirectingToRegistrationFormWithError(): void
    {
        $client = self::createClient();
        $session = $client->getContainer()->get('session');
        $session->set(Security::AUTHENTICATION_ERROR, new AccountNotLinkedException());

        $client->request('GET', '/login_hwi/');

        $response = $client->getResponse();

        $this->assertSame(302, $response->getStatusCode(), $response->getContent());
        $this->assertSame(0, strpos($response->headers->get('Location'), '/connect/registration/'), $response->headers->get('Location'));
    }

    public function testLoginPageWithError(): void
    {
        $client = self::createClient();

        $httpClient = $this->prophesize(ClientInterface::class);
        self::$container->set(ClientInterface::class, $httpClient->reveal());

        $session = self::$container->get('session');

        $this->logIn($client, $session);
        $exception = new UsernameNotFoundException();
        $session->set(Security::AUTHENTICATION_ERROR, $exception);

        $crawler = $client->request('GET', '/login_hwi/');

        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame($exception->getMessageKey(), $crawler->filter('span')->text(), $response->getContent());
    }

    private function logIn($client, SessionInterface $session): void
    {
        $firewallContext = 'hwi_context';

        $token = new CustomOAuthToken();
        $session->set('_security_'.$firewallContext, serialize($token));

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }
}
