<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Core\Config;

use JsonSerializable;

final class NotFoundRedirects implements JsonSerializable
{
    private function __construct(
        public readonly ?string $baseUrlRedirect,
        public readonly ?string $regular404Redirect,
        public readonly ?string $invalidShortUrlRedirect,
    ) {
    }

    public static function withRedirects(
        ?string $baseUrlRedirect,
        ?string $regular404Redirect = null,
        ?string $invalidShortUrlRedirect = null,
    ): self {
        return new self($baseUrlRedirect, $regular404Redirect, $invalidShortUrlRedirect);
    }

    public static function withoutRedirects(): self
    {
        return new self(null, null, null);
    }

    public static function fromConfig(NotFoundRedirectConfigInterface $config): self
    {
        return new self($config->baseUrlRedirect(), $config->regular404Redirect(), $config->invalidShortUrlRedirect());
    }

    public function jsonSerialize(): array
    {
        return [
            'baseUrlRedirect' => $this->baseUrlRedirect,
            'regular404Redirect' => $this->regular404Redirect,
            'invalidShortUrlRedirect' => $this->invalidShortUrlRedirect,
        ];
    }
}
