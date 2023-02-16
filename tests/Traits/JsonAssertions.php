<?php
/**
 * Project: CartApp
 * Owner: Pixelated
 * Copyright: 2023
 */

namespace Tests\Traits;

use Illuminate\Testing\TestResponse;

trait JsonAssertions
{
    public function assertJsonHasKeys(TestResponse $response, string $jsonPath, string ...$keys)
    {
        $items = $response->json($jsonPath);

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $items);
        }
    }
}
