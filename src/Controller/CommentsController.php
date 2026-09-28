<?php

namespace App\Controller;

use App\Dto\CommentPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class CommentsController extends AbstractController
{
    public const COMMENTS = [
        ['id' => 1, 'userId' => 2, 'content' => 'This is the first comment.'],
        ['id' => 2, 'userId' => 3, 'content' => 'A second comment for the demo.'],
        ['id' => 3, 'userId' => 2, 'content' => 'Another comment from the first user.'],
    ];

    #[Route('/comments', name: 'comments_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json(self::COMMENTS);
    }

    #[Route('/comments/{id}', name: 'get', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        foreach (self::COMMENTS as $comment) {
            if ($comment['id'] === $id) {
                return $this->json($comment);
            }
        }

        return $this->json(['error' => 'Comment not found.'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/comments', name: 'comments_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] CommentPayload $payload): JsonResponse
    {
        $comments = self::COMMENTS;
        $comment = [
            'id' => max(array_column($comments, 'id')) + 1,
            'userId' => $payload->userId,
            'content' => $payload->content,
        ];
        $comments[] = $comment;

        return $this->json($comment, Response::HTTP_CREATED, [
            'Location' => $this->generateUrl('get', ['id' => $comment['id']]),
        ]);
    }

    #[Route('/comments/{id}', name: 'comments_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(int $id, #[MapRequestPayload] CommentPayload $payload): JsonResponse
    {
        $comments = self::COMMENTS;

        foreach ($comments as $index => $comment) {
            if ($comment['id'] === $id) {
                $comments[$index] = [
                    'id' => $id,
                    'userId' => $payload->userId,
                    'content' => $payload->content,
                ];

                return $this->json($comments[$index]);
            }
        }

        return $this->json(['error' => 'Comment not found.'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/comments/{id}', name: 'comments_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $comments = self::COMMENTS;

        foreach ($comments as $index => $comment) {
            if ($comment['id'] === $id) {
                array_splice($comments, $index, 1);

                return new JsonResponse(null, Response::HTTP_NO_CONTENT);
            }
        }

        return $this->json(['error' => 'Comment not found.'], Response::HTTP_NOT_FOUND);
    }
}
