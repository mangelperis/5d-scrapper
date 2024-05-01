<?php
declare(strict_types=1);


namespace App\Infrastructure\Adapter\Persistence;

use App\Domain\Entity\Project;
use App\Domain\Repository\CommonRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;

class ProjectRepositoryDoctrineAdapter extends EntityRepository implements CommonRepositoryInterface
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata(Project::class));
        $this->entityManager = $entityManager;
    }


    /**
     * @param object $entity
     * @return bool
     */
    public function save(object $entity): bool
    {
        try {
            /** @var Project $entity */
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            return true;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param object $entity
     * @return bool
     */
    public function delete(object $entity): bool
    {
        try {
            /** @var Project $entity */
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
            return true;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param string $attribute
     * @param mixed $value
     * @param object $entity
     * @param bool $persist
     * @return object|null
     */
    public function update(string $attribute, mixed $value, object $entity, $persist = true): ?object
    {
        try {
            if (!$entity instanceof Project) {
                throw new \InvalidArgumentException(sprintf(
                    'The entity must be an instance of %s, %s given.',
                    Project::class,
                    get_class($entity)
                ));
            }

            $setter = 'set' . ucfirst($attribute);

            if (!method_exists($entity, $setter)) {
                throw new \InvalidArgumentException(sprintf(
                    'The setter method "%s" does not exist in the %s entity.',
                    $setter,
                    Project::class
                ));
            }

            $entity->$setter($value);

            if($persist === true){
                $this->getEntityManager()->persist($entity);
                $this->getEntityManager()->flush();
            }

            return $entity;
        } catch (Exception $exception) {
            return $exception;
        }
    }
}