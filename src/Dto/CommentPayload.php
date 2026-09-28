<?php

namespace App\Dto;

final readonly class CommentPayload
{
    public function __construct(
        public int $userId,
        public string $content,
    ) {
    }
}
