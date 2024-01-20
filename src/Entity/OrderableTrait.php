<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait OrderableTrait
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $position = 0;

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }
}
