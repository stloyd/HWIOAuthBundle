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

use Doctrine\ORM\Tools\SchemaTool;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Tests\App\AppKernel;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomOAuthToken;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class ConnectControllerTest extends WebTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        static::$class = AppKernel::class;
    }

    public static function getKernelClass(): string
    {
        return AppKernel::class;
    }

    public function testRegistration(): void
    {
        $mockResponse = $this->prophesize(ResponseInterface::class);
        $mockResponse->getBody()
            ->willReturn(json_encode(['access_token' => 'valid-access-token']));

        $httpClient = $this->prophesize(ClientInterface::class);
        $httpClient->sendRequest(Argument::type(RequestInterface::class))
            ->shouldBeCalled()
            ->willReturn($mockResponse->reveal());

        $client = static::createClient();
        $client->disableReboot();

        $client->getContainer()->set(ClientInterface::class, $httpClient->reveal());

        $key = 1;
        $exception = new AccountNotLinkedException();
        $exception->setResourceOwnerName('google');
        $exception->setToken(new CustomOAuthToken());

        $session = $client->getContainer()->get('session');
        $session->set('_hwi_oauth.registration_error.'.$key, $exception);

        $this->createDatabase($client);

        $crawler = $client->request('GET', '/connect/registration/'.$key);

        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame(1, $crawler->filter('.hwi_oauth_registration_register')->count(), $response->getContent());

        $form = $crawler->filter('form')->form();

        $form['registration[email]']->setValue('test@example.com');
        $form['registration[username]']->setValue('foo');
        $form['registration[plainPassword][first]']->setValue('bar');
        $form['registration[plainPassword][second]']->setValue('bar');

        $crawler = $client->submit($form);
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame('Successfully registered and connected the account "foo"!', $crawler->filter('h3')->text(), $response->getContent());
    }

    public function testConnectService(): void
    {
        $client = static::createClient();
        $client->disableReboot();

        $mockResponse = $this->prophesize(ResponseInterface::class);
        $httpClient = $this->prophesize(ClientInterface::class);
        $mockResponse->getBody()
            ->willReturn(json_encode(['name' => 'foo']));

        $httpClient->sendRequest(Argument::any())
            ->shouldBeCalled()
            ->willReturn($mockResponse->reveal());
        self::$container->set(ClientInterface::class, $httpClient->reveal());

        $this->createDatabase($client);

        $session = self::$container->get('session');
        $key = 1;
        $session->set('_hwi_oauth.connect_confirmation.'.$key, ['access_token' => 'valid-access-token']);
        $this->logIn($client, $session);

        $crawler = $client->request('GET', '/connect/service/google', [
            'key' => $key,
        ]);

        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame(1, $crawler->filter('.registration_register')->count(), $response->getContent());

        $form = $crawler->filter('form')->form();

        $crawler = $client->submit($form);
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame('Successfully connected the account "foo"!', $crawler->filter('h3')->text(), $response->getContent());
    }

    /**
     * @param Client|KernelBrowser $client
     */
    private function logIn($client, SessionInterface $session): void
    {
        $firewallContext = 'hwi_context';
        $token = new CustomOAuthToken();
        $session->set('_security_'.$firewallContext, serialize($token));
        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    /**
     * @param Client|KernelBrowser $client
     */
    private function createDatabase($client): void
    {
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($metadata);
    }
}
