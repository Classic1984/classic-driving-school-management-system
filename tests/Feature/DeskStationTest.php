<?php

namespace Tests\Feature;

use App\Models\DeskStation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeskStationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_director_can_add_a_desk_station(): void
    {
        $director = User::factory()->director()->create();

        $this->actingAs($director)->post(route('desk-stations.store'), [
            'name' => 'Front Desk',
        ])->assertRedirect(route('desk-stations.index'));

        $station = DeskStation::where('name', 'Front Desk')->firstOrFail();
        $this->assertNotEmpty($station->api_token);
        $this->assertSame('unknown', $station->status);
    }

    public function test_a_secretary_cannot_manage_desk_stations(): void
    {
        $secretary = User::factory()->secretary()->create();

        $this->actingAs($secretary)->get(route('desk-stations.index'))->assertForbidden();
        $this->actingAs($secretary)->post(route('desk-stations.store'), ['name' => 'Front Desk'])->assertForbidden();
    }

    public function test_two_stations_cannot_share_a_name(): void
    {
        $director = User::factory()->director()->create();
        DeskStation::factory()->create(['name' => 'Front Desk']);

        $this->actingAs($director)->post(route('desk-stations.store'), [
            'name' => 'Front Desk',
        ])->assertSessionHasErrors('name');
    }

    public function test_a_director_can_delete_a_station(): void
    {
        $director = User::factory()->director()->create();
        $station = DeskStation::factory()->create();

        $this->actingAs($director)->delete(route('desk-stations.destroy', $station))
            ->assertRedirect(route('desk-stations.index'));

        $this->assertDatabaseMissing('desk_stations', ['id' => $station->id]);
    }

    public function test_a_director_can_regenerate_a_stations_token(): void
    {
        $director = User::factory()->director()->create();
        $station = DeskStation::factory()->create(['api_token' => 'old-token']);

        $this->actingAs($director)->post(route('desk-stations.regenerate-token', $station))
            ->assertRedirect(route('desk-stations.index'));

        $this->assertNotSame('old-token', $station->fresh()->api_token);
    }

    public function test_the_on_site_agent_can_ping_a_station_as_occupied_without_logging_in(): void
    {
        $station = DeskStation::factory()->create(['status' => 'unknown']);

        $this->post(route('public-desk-stations.ping', $station->api_token), [
            'status' => 'occupied',
        ])->assertOk()->assertJson(['ok' => true]);

        $station->refresh();
        $this->assertSame('occupied', $station->status);
        $this->assertNotNull($station->last_seen_at);
        $this->assertNotNull($station->status_changed_at);
    }

    public function test_pinging_an_unknown_token_returns_not_found(): void
    {
        $this->post(route('public-desk-stations.ping', 'not-a-real-token'), [
            'status' => 'occupied',
        ])->assertNotFound();
    }

    public function test_pinging_with_an_invalid_status_is_rejected(): void
    {
        $station = DeskStation::factory()->create();

        $this->post(route('public-desk-stations.ping', $station->api_token), [
            'status' => 'on-a-break',
        ])->assertUnprocessable();
    }

    public function test_status_changed_at_only_moves_when_the_status_actually_changes(): void
    {
        $station = DeskStation::factory()->create(['status' => 'empty']);
        $station->forceFill(['status_changed_at' => now()->subHour()])->save();
        $unchangedSince = $station->fresh()->status_changed_at;

        $this->post(route('public-desk-stations.ping', $station->api_token), [
            'status' => 'empty',
        ])->assertOk();

        $this->assertTrue($station->fresh()->status_changed_at->equalTo($unchangedSince));
    }

    public function test_a_station_that_has_not_pinged_in_a_while_displays_as_offline(): void
    {
        $station = DeskStation::factory()->create([
            'status' => 'occupied',
            'last_seen_at' => now()->subMinutes(DeskStation::OFFLINE_AFTER_MINUTES + 1),
        ]);

        $this->assertSame('offline', $station->displayStatus());
    }

    public function test_a_recently_pinged_station_displays_its_reported_status(): void
    {
        $station = DeskStation::factory()->create([
            'status' => 'occupied',
            'last_seen_at' => now(),
        ]);

        $this->assertSame('occupied', $station->displayStatus());
    }

    public function test_a_station_that_has_never_pinged_displays_as_offline(): void
    {
        $station = DeskStation::factory()->create(['status' => 'unknown', 'last_seen_at' => null]);

        $this->assertSame('offline', $station->displayStatus());
    }

    public function test_the_status_endpoint_reports_live_display_status_for_a_director(): void
    {
        $director = User::factory()->director()->create();
        $station = DeskStation::factory()->create([
            'status' => 'occupied',
            'last_seen_at' => now()->subMinutes(DeskStation::OFFLINE_AFTER_MINUTES + 1),
        ]);

        $response = $this->actingAs($director)->get(route('desk-stations.status'));

        $response->assertOk()->assertJson([
            'stations' => [
                ['id' => $station->id, 'display_status' => 'offline'],
            ],
        ]);
    }
}
