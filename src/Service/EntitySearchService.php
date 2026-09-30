<?php

namespace App\Service;

use Closure;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class EntitySearchService
{
    private const DEFAULT_ITEMS_PER_PAGE = 20;
    private const MAX_ITEMS_PER_PAGE = 100;

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param class-string $entityClass
     * @param array<string, array{expression: string, type: string, nullable?: bool}> $filters
     * @param array<string, mixed> $query
     *
     * @return array{items: list<object>, pagination: array{page: int, itemsPerPage: int, totalItems: int, totalPages: int}}
     */
    public function search(string $entityClass, array $filters, array $query, ?Closure $scope = null): array
    {
        $allowedQueryParameters = ['page', 'itemsPerPage', ...array_keys($filters)];
        $unknownParameters = array_diff(array_keys($query), $allowedQueryParameters);

        if ($unknownParameters !== []) {
            throw new BadRequestHttpException(sprintf(
                'Unknown query parameter(s): %s.',
                implode(', ', $unknownParameters),
            ));
        }

        $page = $this->parsePositiveInteger($query['page'] ?? '1', 'page');
        $itemsPerPage = $this->parsePositiveInteger(
            $query['itemsPerPage'] ?? (string) self::DEFAULT_ITEMS_PER_PAGE,
            'itemsPerPage',
        );

        if ($itemsPerPage > self::MAX_ITEMS_PER_PAGE) {
            throw new BadRequestHttpException(sprintf(
                'itemsPerPage must not exceed %d.',
                self::MAX_ITEMS_PER_PAGE,
            ));
        }

        if ($page - 1 > intdiv(PHP_INT_MAX, $itemsPerPage)) {
            throw new BadRequestHttpException('page is too large.');
        }

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from($entityClass, 'e')
            ->orderBy('e.id', 'ASC');

        $scope?->__invoke($queryBuilder);

        foreach ($filters as $parameter => $filter) {
            if (!array_key_exists($parameter, $query)) {
                continue;
            }

            $value = $query[$parameter];

            if (!is_string($value) && !is_int($value) && !is_float($value)) {
                throw new BadRequestHttpException(sprintf('%s must be a scalar value.', $parameter));
            }

            $this->addFilter($queryBuilder, $parameter, (string) $value, $filter);
        }

        $countQueryBuilder = clone $queryBuilder;
        $totalItems = (int) $countQueryBuilder
            ->select('COUNT(e.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $items = $queryBuilder
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage)
            ->getQuery()
            ->getResult();

        return [
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'itemsPerPage' => $itemsPerPage,
                'totalItems' => $totalItems,
                'totalPages' => (int) ceil($totalItems / $itemsPerPage),
            ],
        ];
    }

    /** @param array{expression: string, type: string, nullable?: bool} $filter */
    private function addFilter(QueryBuilder $queryBuilder, string $parameter, string $value, array $filter): void
    {
        $expression = $filter['expression'];
        $parameterName = 'filter_'.$parameter;

        if (($filter['nullable'] ?? false) && strtolower($value) === 'null') {
            $queryBuilder->andWhere($expression.' IS NULL');

            return;
        }

        if (($filter['nullable'] ?? false) && $value === '') {
            throw new BadRequestHttpException(sprintf('%s must not be empty; use "null" to match a null value.', $parameter));
        }

        switch ($filter['type']) {
            case 'string':
                $queryBuilder
                    ->andWhere('LOWER('.$expression.') LIKE LOWER(:'.$parameterName.')')
                    ->setParameter($parameterName, '%'.$value.'%', Types::STRING);

                return;

            case 'integer':
                $integer = filter_var($value, FILTER_VALIDATE_INT);

                if ($integer === false) {
                    throw new BadRequestHttpException(sprintf('%s must be an integer.', $parameter));
                }

                $queryBuilder->andWhere($expression.' = :'.$parameterName)
                    ->setParameter($parameterName, $integer, Types::INTEGER);

                return;

            case 'decimal':
                if (!is_numeric($value)) {
                    throw new BadRequestHttpException(sprintf('%s must be numeric.', $parameter));
                }

                $queryBuilder->andWhere($expression.' = :'.$parameterName)
                    ->setParameter($parameterName, $value, Types::DECIMAL);

                return;

            case 'date':
                $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
                $dateErrors = \DateTimeImmutable::getLastErrors();

                if ($date === false || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))) {
                    throw new BadRequestHttpException(sprintf('%s must be a valid date in YYYY-MM-DD format.', $parameter));
                }

                $queryBuilder->andWhere($expression.' = :'.$parameterName)
                    ->setParameter($parameterName, $date, Types::DATE_IMMUTABLE);

                return;

            case 'datetime':
                if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/D', $value) !== 1) {
                    throw new BadRequestHttpException(sprintf('%s must be an ISO 8601 datetime with timezone.', $parameter));
                }

                try {
                    $dateTime = new \DateTimeImmutable($value);
                } catch (\Exception) {
                    throw new BadRequestHttpException(sprintf('%s must be a valid ISO 8601 datetime.', $parameter));
                }

                $dateTimeErrors = date_parse($value);

                if ($dateTimeErrors['warning_count'] > 0 || $dateTimeErrors['error_count'] > 0) {
                    throw new BadRequestHttpException(sprintf('%s must be a valid ISO 8601 datetime.', $parameter));
                }

                $queryBuilder->andWhere($expression.' = :'.$parameterName)
                    ->setParameter($parameterName, $dateTime, Types::DATETIME_IMMUTABLE);

                return;

            case 'exact':
                $queryBuilder->andWhere($expression.' = :'.$parameterName)
                    ->setParameter($parameterName, $value, Types::STRING);

                return;

            default:
                throw new \LogicException(sprintf('Unsupported filter type "%s".', $filter['type']));
        }
    }

    private function parsePositiveInteger(mixed $value, string $parameter): int
    {
        if ((!is_string($value) && !is_int($value)) || filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value < 1) {
            throw new BadRequestHttpException(sprintf('%s must be a positive integer.', $parameter));
        }

        return (int) $value;
    }
}
