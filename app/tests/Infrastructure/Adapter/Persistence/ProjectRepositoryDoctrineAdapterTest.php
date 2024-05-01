<?php

namespace App\Tests\Infrastructure\Adapter\Persistence;

use App\Domain\Entity\Project;
use App\Infrastructure\Adapter\Persistence\ProjectRepositoryDoctrineAdapter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;

class ProjectRepositoryDoctrineAdapterTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private ProjectRepositoryDoctrineAdapter $projectRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $classMetadata = new ClassMetadata(Project::class);
        $this->entityManager->expects($this->once())
            ->method('getClassMetadata')
            ->with(Project::class)
            ->willReturn($classMetadata);

        $this->projectRepository = new ProjectRepositoryDoctrineAdapter($this->entityManager);
    }


    public function testSave(): void
    {
        $project = new Project();
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($project);
        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->projectRepository->save($project);

        $this->assertTrue($result);
    }

    public function testDelete(): void
    {
        $project = new Project();
        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($project);
        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->projectRepository->delete($project);

        $this->assertTrue($result);
    }

    public function testIncrementHitCount(): void
    {
        $project = new Project();
        $project->setUrl('https://5d.com/test-project');
        $project->setHits(10);

        $this->projectRepository->incrementHitCount($project);

        $this->assertEquals(11, $project->getHits());
    }


}
