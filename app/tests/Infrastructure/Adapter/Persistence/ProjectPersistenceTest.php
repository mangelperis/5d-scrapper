<?php

namespace App\Tests\Infrastructure\Adapter\Persistence;

use App\Domain\Entity\Project;
use App\Infrastructure\Adapter\Persistence\ProjectPersistence;
use App\Infrastructure\Adapter\Persistence\ProjectRepositoryDoctrineAdapter;
use PHPUnit\Framework\TestCase;

class ProjectPersistenceTest extends TestCase
{
    public function testPersist()
    {
        // the Interface doesn't have the method 'findByOne' so
        $projectRepositoryMock = $this->createMock(ProjectRepositoryDoctrineAdapter::class);

        $projectPersistence = new ProjectPersistence($projectRepositoryMock);

        $projects = [
            [
                'url' => 'https://5d.com/project1',
                'title' => 'Project 1',
                'user' => 'u1',
                'hits' => '5',
            ],
            [
                'url' => 'https://5d.com/project2',
                'title' => 'Project 2',
                'user' => 'u2',
                'hits' => '7',
            ],
        ];

        //New addition
        $projectRepositoryMock->expects($this->exactly(2))
            ->method('findOneBy')
            ->withConsecutive(
                [['url' => 'https://5d.com/project1']],
                [['url' => 'https://5d.com/project2']]
            )
            ->willReturnOnConsecutiveCalls(
                null,
                $this->createMock(Project::class)
            );

        //2x4 array elements = 8
        $projectRepositoryMock->expects($this->exactly(8))
            ->method('update')
            ->willReturn($this->createMock(Project::class));

        //2 projects to save
        $projectRepositoryMock->expects($this->exactly(2))
            ->method('save')
            ->willReturn(true);

        // Call the persist method
        $count = $projectPersistence->persist($projects);

        //expected count of 2
        $this->assertEquals(2, $count);
    }
}
