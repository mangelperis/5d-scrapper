<?php

namespace App\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'projects')]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'string', length: 255)]
    private string $qrImage;

    #[ORM\Column(type: 'integer')]
    private int $hits = 0;

    #[ORM\OneToMany(targetEntity: ProjectStatistic::class, mappedBy: 'project', cascade: ['persist', 'remove'])]
    private Collection $statistics;

    public function __construct()
    {
        $this->statistics = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getQrImage(): string
    {
        return $this->qrImage;
    }

    public function setQrImage(string $qrImage): self
    {
        $this->qrImage = $qrImage;
        return $this;
    }

    public function getHits(): int
    {
        return $this->hits;
    }

    public function incrementHits(): self
    {
        $this->hits++;
        return $this;
    }

    /**
     * @return Collection<int, ProjectStatistic>
     */
    public function getStatistics(): Collection
    {
        return $this->statistics;
    }

    public function addStatistic(ProjectStatistic $statistic): self
    {
        if (!$this->statistics->contains($statistic)) {
            $this->statistics[] = $statistic;
            $statistic->setProject($this);
        }
        return $this;
    }

    public function removeStatistic(ProjectStatistic $statistic): self
    {
        if ($this->statistics->removeElement($statistic)) {
            if ($statistic->getProject() === $this) {
                $statistic->setProject(null);
            }
        }
        return $this;
    }
}
