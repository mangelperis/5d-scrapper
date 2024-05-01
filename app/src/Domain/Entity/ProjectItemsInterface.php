<?php
declare(strict_types=1);


namespace App\Domain\Entity;

interface ProjectItemsInterface
{

    public function getProject(): ?Project;

    public function setProject(?Project $project): self;

}