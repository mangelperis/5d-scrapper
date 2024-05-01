<?php

namespace App\Domain\Repository;

interface CommonRepositoryInterface
{
    public function save(object $entity): bool;
    public function delete(object $entity): bool;
    public function update(string $attribute, mixed $value, object $entity, $persist): ?object;
}