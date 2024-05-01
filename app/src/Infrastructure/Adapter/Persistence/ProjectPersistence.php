<?php
declare(strict_types=1);


namespace App\Infrastructure\Adapter\Persistence;

use App\Domain\Entity\Project;
use App\Domain\Repository\CommonRepositoryInterface;

class ProjectPersistence
{

    private CommonRepositoryInterface $projectRepository;

    public function __construct(CommonRepositoryInterface $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    /**
     * @param array $projects
     * @return int
     */
    public function persist(array $projects): int
    {
        $count = 0;

        foreach ($projects as $element) {
            /** @var Project $project */
            $project = $this->projectRepository->findOneBy(['url' => $element['url']]);

            // Update an existing one or create a new one
            if (!$project) {
                $project = new Project();
            }

            // Set desired and existing attributes in the Entity
            foreach ($element as $attribute => $value) {
                $project = $this->projectRepository->update($attribute, $value, $project, false);
            }

            // Persist
            if ($this->projectRepository->save($project)) {
                $count++;
            }
        }

        return $count;
    }

}