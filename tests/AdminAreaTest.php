<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AdminAreaTest extends WebTestCase
{
    #[Test]
    public function noCertificateInTheRequest(): void
    {
        // Given
        $client = static::createClient();

        // When
        $client->request('GET', '/admin');

        // Then
        $this->assertResponseRedirects('/login/admin', 302);
    }
}
