<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\AccessToken;
use App\Entity\User;
use App\Repository\AccessTokenRepository;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Ulid;

/**
 * The `api` firewall reads `Authorization: Bearer <token>`. The token repository is replaced by a stub,
 * since the test environment has no database: what is under test is the handler, the firewall and the
 * API Platform operation that reads the authenticated user, not Doctrine.
 */
final class ApiBearerTokenTest extends WebTestCase
{
    private const string VALID_TOKEN = 'a-token-issued-to-admin';

    #[Test]
    public function aValidTokenAuthenticatesTheRequestAsItsOwner(): void
    {
        //Given
        $client = $this->clientKnowingOneToken();

        //When
        $client->request(Request::METHOD_GET, '/api/me', server: [
            'HTTP_AUTHORIZATION' => 'Bearer ' . self::VALID_TOKEN,
            'HTTP_ACCEPT' => 'application/ld+json',
        ]);

        //Then
        self::assertResponseIsSuccessful();
        self::assertJsonContains($client, [
            'username' => 'admin',
            'email' => 'admin@meteofony.test',
        ]);
    }

    #[Test]
    public function anUnknownTokenIsRejected(): void
    {
        //Given
        $client = $this->clientKnowingOneToken();

        //When
        $client->request(Request::METHOD_GET, '/api/me', server: [
            'HTTP_AUTHORIZATION' => 'Bearer not-a-token',
            'HTTP_ACCEPT' => 'application/ld+json',
        ]);

        //Then
        self::assertResponseStatusCodeSame(401);
    }

    /**
     * A token outside the RFC 6750 alphabet is not extracted at all, so the request is anonymous, the same
     * as one without the header. `/api/me` needs a user and answers with a 401, not a 500.
     */
    #[Test]
    public function aMalformedTokenOrNoTokenIsAskedToAuthenticate(): void
    {
        //Given
        $client = $this->clientKnowingOneToken();

        foreach ([['HTTP_AUTHORIZATION' => 'Bearer fdlgùpf'], []] as $headers) {
            //When
            $client->request(Request::METHOD_GET, '/api/me', server: [
                ...$headers,
                'HTTP_ACCEPT' => 'application/ld+json',
            ]);

            //Then
            self::assertResponseStatusCodeSame(401);
        }
    }

    private function clientKnowingOneToken(): KernelBrowser
    {
        $client = static::createClient(server: [
            'HTTPS' => 'on',
        ]);

        $owner = new User(Ulid::generate(), 'admin@meteofony.test', 'admin', 'not-a-hash');
        $repository = $this->createStub(AccessTokenRepository::class);
        $repository
            ->method('find')
            ->willReturnCallback(
                static fn (mixed $id): ?AccessToken => $id === self::VALID_TOKEN ? new AccessToken($id, $owner) : null
            );
        static::getContainer()->set(AccessTokenRepository::class, $repository);

        return $client;
    }

    /**
     * @param array<string, string> $expected
     */
    private static function assertJsonContains(KernelBrowser $client, array $expected): void
    {
        $content = $client->getResponse()
            ->getContent();
        self::assertIsString($content);
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($data);
        foreach ($expected as $key => $value) {
            self::assertArrayHasKey($key, $data);
            self::assertSame($value, $data[$key]);
        }
    }
}
