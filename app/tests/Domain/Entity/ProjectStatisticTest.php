<?php

namespace App\Tests\Domain\Entity;

use App\Domain\Entity\Project;
use App\Domain\Entity\ProjectStatistic;
use PHPUnit\Framework\TestCase;

class ProjectStatisticTest extends TestCase
{
    public function testCreateProjectStatistic()
    {
        $statistic = new ProjectStatistic('likes', '10');

        $this->assertInstanceOf(ProjectStatistic::class, $statistic);
        $this->assertEquals('likes', $statistic->getName());
        $this->assertEquals('10', $statistic->getValue());
        $this->assertNull($statistic->getProject());
    }

    public function testSetName()
    {
        $statistic = new ProjectStatistic('likes', '10');
        $statistic->setName('comments');

        $this->assertEquals('comments', $statistic->getName());
    }

    public function testSetValue()
    {
        $statistic = new ProjectStatistic('likes', '10');
        $statistic->setValue('20');

        $this->assertEquals('20', $statistic->getValue());
    }

    public function testSetProject()
    {
        $project = new Project();
        $statistic = new ProjectStatistic('likes', '10');
        $statistic->setProject($project);

        $this->assertSame($project, $statistic->getProject());
    }

}
