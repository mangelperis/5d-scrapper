<?php

namespace App\Tests\Application\Service;

use App\Application\Service\ProjectService;
use App\Domain\Entity\Project;
use App\Infrastructure\Adapter\Persistence\ProjectRepositoryDoctrineAdapter;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ProjectServiceTest extends TestCase
{
    private ProjectService $projectService;
    private ProjectRepositoryDoctrineAdapter $projectRepositoryMock;
    private LoggerInterface $loggerMock;

    protected function setUp(): void
    {
        $this->projectRepositoryMock = $this->createMock(ProjectRepositoryDoctrineAdapter::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->projectService = new ProjectService(
            $this->loggerMock,
            $this->projectRepositoryMock
        );
    }

    /**
     * @throws Exception
     */
    public function testFindAll(): void
    {
        $expectedProjects = [
            new Project(),
            new Project(),
        ];

        $this->projectRepositoryMock->expects($this->once())
            ->method('findAll')
            ->willReturn($expectedProjects);

        $projects = $this->projectService->findAll();

        $this->assertEquals($expectedProjects, $projects);
    }

    /**
     * @throws Exception
     */
    public function testFindProjectById(): void
    {
        $projectId = 1;
        $expectedProject = new Project();

        $this->projectRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => $projectId])
            ->willReturn($expectedProject);

        $project = $this->projectService->findProjectById($projectId);

        $this->assertSame($expectedProject, $project);
    }

    /**
     * @throws Exception
     */
    public function testFindProjectByIdNotFound(): void
    {
        $projectId = 1;

        $this->projectRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => $projectId])
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(404);

        $this->projectService->findProjectById($projectId);
    }

    /**
     * @throws Exception
     */
    public function testGenerateQR(): void
    {
        $url = 'https://planner5d.com/gallery/floorplans/LaPGTG/floorplans-bathroom-cafe-office-kitchen-entryway-3d';
        $expectedQrCode = "data:image/png;base64,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"; // valid base64 string

        $result = $this->projectService->generateQR($url);

        $this->assertEquals($expectedQrCode, $result);

    }

}
