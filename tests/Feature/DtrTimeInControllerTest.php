<?php

namespace Tests\Feature;

use App\Models\Dtr;
use App\Models\DtrBreak;
use App\Models\User;
use App\Testing\DtrTestingTrait;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DtrTimeInControllerTest extends TestCase
{
    use RefreshDatabase, DtrTestingTrait;

    /**
     * Setup method to create a user, Dtr, and DtrBreak.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpUserDtrDtrBreak();
    }

    /**
     * Teardown method.
     */
    public function tearDown(): void
    {
        $this->tearDownUserDtrDtrBreak();
        parent::tearDown();
    }

    /**
     * Test successful time in.
     */
    public function testTimeIn()
    {
        $response = $this->postJson('/api/dtr/time-in');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Time in recorded successfully.'
            ]);

        $this->assertDatabaseHas('dtrs', [
            'user_id' => $this->user->id,
            'time_out' => null
        ]);
    }

    /**
     * Test open time record needs to be closed before timing in again.
     */
    public function testTimeInOpenTimeRecordExists()
    {
        // Create an existing DTR record without a time_out
        Dtr::factory()->create([
            'user_id' => $this->user->id,
            'time_in' => Carbon::now()->subHours(2),
            'time_out' => null
        ]);

        $response = $this->postJson('/api/dtr/time-in');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'You have an open time record that needs to be closed before timing in again.'
            ]);
    }
}
