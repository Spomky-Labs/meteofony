<?php

declare(strict_types=1);

namespace App\Twig;

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class CaptchaExtension extends AbstractExtension
{
    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public function getFunctions(): array
    {
        return [new TwigFunction('captcha', $this->getCaptcha(...))];
    }

    public function getCaptcha(
        string $identifier,
        int $length = 5,
        int $width = 250,
        int $height = 50,
        bool $distorsion = true,
        bool $interpolation = true,
        ?int $maxFrontLines = null,
        ?int $maxBehindLines = null,
    ): string {
        $phraseBuilder = new PhraseBuilder($length, '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $builder = new CaptchaBuilder(null, $phraseBuilder);
        $builder
            ->setDistortion($distorsion)
            ->setInterpolation($interpolation)
            ->setMaxFrontLines($maxFrontLines)
            ->setMaxBehindLines($maxBehindLines)
        ;
        $captcha = $builder->build($width, $height);

        $phrase = $captcha->getPhrase();
        $this->requestStack->getSession()
            ->set($identifier, $phrase);

        return $captcha->inline();
    }
}
