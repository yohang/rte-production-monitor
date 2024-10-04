<?php
declare(strict_types=1);

namespace App\Bridge\RTE\Model;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class AccessToken
{
    #[SerializedName('access_token')]
    public string $accessToken;

    #[SerializedName('token_type')]
    public string $tokenType;

    #[SerializedName('expires_in')]
    public int $expiresIn;

    public function __construct(
        string $accessToken,
        string $tokenType,
        int $expiresIn
    )
    {
        $this->accessToken = $accessToken;
        $this->tokenType = $tokenType;
        $this->expiresIn = $expiresIn;
    }
}
