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
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * uses FOSUserBundle which itself contains lots of deprecations.
 *
 * @group legacy
 */
final class ConnectControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        static::$class = AppKernel::class;
        if (!class_exists(\FOS\UserBundle\Model\User::class)) {
            $this->markTestSkipped('FOSUserBundle not installed.');
        }
    }

    public static function getKernelClass(): string
    {
        return AppKernel::class;
    }

    public function testRegistration(): void
    {
        $httpClient = new MockHttpClient(
            [
                new MockResponse(json_encode(['access_token' => 'valid-access-token'])),
            ]
        );

        $client = static::createClient();
        $client->disableReboot();
        $client->getContainer()->set(HttpClientInterface::class, $httpClient);

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

        $form['fos_user_registration_form[email]']->setValue('test@example.com');
        $form['fos_user_registration_form[username]']->setValue('username');
        $form['fos_user_registration_form[plainPassword][first]']->setValue('bar');
        $form['fos_user_registration_form[plainPassword][second]']->setValue('bar');

        $crawler = $client->submit($form);
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame('Successfully registered and connected the account "foo"!', $crawler->filter('h3')->text(), $response->getContent());
    }

    public function testConnectService(): void
    {
        $httpClient = new MockHttpClient(
            [
                new MockResponse(json_encode(['name' => 'foo'])),
            ]
        );

        $client = static::createClient();
        $client->disableReboot();
        $client->getContainer()->set(HttpClientInterface::class, $httpClient);

        $this->createDatabase($client);

        $session = $client->getContainer()->get('session');
        $key = 1;
        $session->set('_hwi_oauth.connect_confirmation.'.$key, ['access_token' => 'valid-access-token']);
        $this->logIn($client, $session);

        $crawler = $client->request('GET', '/connect/service/google', [
            'key' => $key,
        ]);

        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame(1, $crawler->filter('.fos_user_registration_register')->count(), $response->getContent());

        $form = $crawler->filter('form')->form();

        $crawler = $client->submit($form);
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame('Successfully connected the account "foo"!', $crawler->filter('h3')->text(), $response->getContent());
    }

    private function logIn(KernelBrowser $client, SessionInterface $session): void
    {
        $firewallContext = 'hwi_context';
        $token = new CustomOAuthToken();
        $session->set('_security_'.$firewallContext, serialize($token));
        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    private function createDatabase(KernelBrowser $client): void
    {
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($metadata);
    }
}
