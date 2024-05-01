<?php

namespace App\Tests\Domain\Entity;

use App\Domain\Entity\Project;
use App\Domain\Entity\ProjectStatistic;
use PHPUnit\Framework\TestCase;

class ProjectTest extends TestCase
{
    public function testCreateProject()
    {
        $project = new Project();
        $project->setTitle('Test Project');
        $project->setUrl('https://planner5d.com');
        $project->setUser('testuser');
        $project->setHits(100);

        $this->assertInstanceOf(Project::class, $project);
        $this->assertEquals('Test Project', $project->getTitle());
        $this->assertEquals('https://planner5d.com', $project->getUrl());
        $this->assertEquals('testuser', $project->getUser());
        $this->assertEquals(100, $project->getHits());
    }

    public function testIncrementHits()
    {
        $project = new Project();
        $project->setHits(50);

        $project->incrementHits();

        $this->assertEquals(51, $project->getHits());
    }

    public function testAddStatistic()
    {
        $project = new Project();
        $statistic = new ProjectStatistic('likes', '10');

        $project->addStatistic($statistic);

        $this->assertContains($statistic, $project->getStatistics());
        $this->assertSame($project, $statistic->getProject());
    }

    public function testRemoveStatistic()
    {
        $project = new Project();
        $statistic = new ProjectStatistic('likes', '10');
        $project->addStatistic($statistic);

        $project->removeStatistic($statistic);

        $this->assertNotContains($statistic, $project->getStatistics());
        $this->assertNull($statistic->getProject());
    }

    public function testSetStatistics()
    {
        $project = new Project();
        $existingStatistic = new ProjectStatistic('likes', '10');
        $project->addStatistic($existingStatistic);

        $newStatistics = [
            'likes' => '20',
            'comments' => '7',
        ];

        $project->setStatistics($newStatistics);

        $this->assertCount(2, $project->getStatistics());
        $this->assertEquals('20', $project->getStatistics()->first()->getValue());
        $this->assertEquals('7', $project->getStatistics()->last()->getValue());
    }

}
