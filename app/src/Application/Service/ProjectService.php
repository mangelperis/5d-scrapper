<?php
declare(strict_types=1);


namespace App\Application\Service;

use App\Domain\Entity\Project;
use App\Infrastructure\Adapter\Persistence\ProjectRepositoryDoctrineAdapter;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class ProjectService
{
    public function __construct(
        private LoggerInterface                  $logger,
        private ProjectRepositoryDoctrineAdapter $projectRepository,
    )
    {
    }

    /**
     * @return array
     * @throws Exception
     */
    public function findAll(): array
    {
        try {
            return $this->projectRepository->findAll();

        } catch (Exception $exception) {
            $this->logger->error(sprintf("[SERVICE] Get All Projects fail: %s", $exception->getMessage()));
            throw new Exception('Error while fetching All Projects');

        }
    }

    /**
     * @param int $projectId
     * @return array
     * @throws Exception
     */
    public function findProjectById(int $projectId): ?Project
    {
        try {

            $project = $this->projectRepository->findOneBy(['id' => $projectId]);

            if (!$project) {
                throw new \InvalidArgumentException('Project not found.', Response::HTTP_NOT_FOUND);
            }

            //TODO Should use a DTO with the desired output values only
            return $project;

        } catch (Exception $exception) {
            $this->logger->error(sprintf("[SERVICE] Get Project Detail fail: %s", $exception->getMessage()));
            throw new Exception('Error while fetching Project');
        }
    }
}