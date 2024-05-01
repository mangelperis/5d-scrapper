<?php

namespace App\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'projects')]
#[ORM\UniqueConstraint(name: 'url', columns: ['url'])]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'string', length: 255)]
    private string $url;

    #[ORM\Column(type: 'string', length: 255)]
    private string $user;

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

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getHits(): int
    {
        return $this->hits;
    }

    public function setHits(int $hits): void
    {
        $this->hits = $hits;
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

    public function addStatistic(ProjectStatistic $statistic): void
    {
        if (!$this->statistics->contains($statistic)) {
            $this->statistics->add($statistic);
            $statistic->setProject($this);
        }
    }

    public function removeStatistic(ProjectStatistic $statistic): void
    {
        if ($this->statistics->removeElement($statistic)) {
            $statistic->setProject(null);
        }
    }

    /**
     * @param array $statistics
     * @return $this
     */
    public function setStatistics(array $statistics): self
    {
        // Remove existing statistics that are not in the updated array
        foreach ($this->statistics as $existingStatistic) {
            /** @var ProjectStatistic $existingStatistic */
            if (!in_array($existingStatistic->getName(), array_keys($statistics))) {
                $this->removeStatistic($existingStatistic);
            }
        }

        // Update or add new statistics
        foreach ($statistics as $name => $value) {
            $statistic = $this->findStatisticByName($name);

            if ($statistic === null) {
                $statistic = new ProjectStatistic($name, $value);
                $this->addStatistic($statistic);
            } else {
                $statistic->setValue($value);
            }
        }

        return $this;
    }

    /**
     * @param string $key
     * @return ProjectStatistic|null
     */
    private function findStatisticByName(string $key): ?ProjectStatistic
    {
        foreach ($this->statistics as $statistic) {
            /** @var ProjectStatistic $statistic */
            if ($statistic->getName() === $key) {
                return $statistic;
            }
        }

        return null;
    }


    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): void
    {
        $this->user = $user;
    }

}
