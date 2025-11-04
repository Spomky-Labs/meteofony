<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class HomepageTest extends WebTestCase
{
    #[Test]
    public function theHomepageIsAvailable(): void
    {
        //Given
        $client = static::createClient(server: [
            'HTTPS' => 'on',
        ]);

        //When
        $client->request(Request::METHOD_GET, '/');

        //Then
        self::assertResponseIsSuccessful();
    }
}
