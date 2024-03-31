<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MetricRepository;
use App\Service\Metric\MetricType;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MetricRepository::class)]
#[ORM\Table(name: 'metrics')]
class Metric
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(targetEntity: Habit::class, inversedBy: 'metrics')]
    private ?Habit $habit = null;

    #[ORM\Column(length: 32, enumType: MetricType::class)]
    private ?MetricType $type = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $metricDate;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->metricDate = new \DateTimeImmutable();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getHabit(): ?Habit
    {
        return $this->habit;
    }

    public function setHabit(Habit $habit): void
    {
        $this->habit = $habit;
    }

    public function getType(): ?MetricType
    {
        return $this->type;
    }

    public function setType(MetricType $type): void
    {
        $this->type = $type;
    }

    public function getMetricDate(): \DateTimeImmutable
    {
        return $this->metricDate;
    }

    public function setMetricDate(\DateTimeImmutable $metricDate): void
    {
        $this->metricDate = $metricDate;
    }
}
