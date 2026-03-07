<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureJwtKeysExist();
        $this->client = static::createClient();
    }

    private function ensureJwtKeysExist(): void
    {
        $projectDir = dirname(__DIR__, 2);
        $privateKeyPath = $projectDir . '/config/jwt/private.pem';
        $publicKeyPath = $projectDir . '/config/jwt/public.pem';

        if (file_exists($privateKeyPath) && file_exists($publicKeyPath)) {
            return;
        }

        if (!is_dir(dirname($privateKeyPath))) {
            mkdir(dirname($privateKeyPath), 0755, true);
        }

        $privateKey = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
        ]);
        openssl_pkey_export($privateKey, $privateKeyPem);
        $publicKeyPem = openssl_pkey_get_details($privateKey)['key'];

        file_put_contents($privateKeyPath, $privateKeyPem);
        file_put_contents($publicKeyPath, $publicKeyPem);
    }

    protected function createUser(int $telegramId = 12345, string $firstName = 'Test'): User
    {
        $user = new User();
        $reflection = new \ReflectionProperty(User::class, 'telegramId');
        $reflection->setValue($user, $telegramId);

        $firstNameReflection = new \ReflectionProperty(User::class, 'firstName');
        $firstNameReflection->setValue($user, $firstName);

        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    protected function authenticatedRequest(string $method, string $uri, User $user, array $body = []): void
    {
        $token = $this->getJwtToken($user);

        $this->client->request(
            $method,
            $uri,
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
                'CONTENT_TYPE' => 'application/json',
            ],
            $body ? json_encode($body) : null,
        );
    }

    protected function unauthenticatedRequest(string $method, string $uri, array $body = []): void
    {
        $this->client->request(
            $method,
            $uri,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $body ? json_encode($body) : null,
        );
    }

    protected function getJwtToken(User $user): string
    {
        /** @var JWTTokenManagerInterface $jwtManager */
        $jwtManager = $this->client->getContainer()->get('lexik_jwt_authentication.jwt_manager');

        return $jwtManager->create($user);
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }
}
