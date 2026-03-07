<?php

declare(strict_types=1);

namespace App\Tests\Service\Message;

use App\Service\Message\Animation;
use App\Service\Message\AnimationType;
use PHPUnit\Framework\TestCase;

class AnimationTest extends TestCase
{
    public function testGetByTypeReturnsStringForEachType(): void
    {
        $animation = new Animation();

        foreach (AnimationType::cases() as $type) {
            $result = $animation->getByType($type);
            $this->assertNotEmpty($result);
        }
    }
}
