<?php

namespace Tests\Feature;

use Tests\TestCase;

class VehicleSyncTest extends TestCase
{
    public function test_vehicle_store_persists_vehicle_data(): void
    {
        $response = $this->post('/vehicle-store', [
            'nama' => 'Budi Test',
            'jenis' => 'Motor',
            'plat' => 'B 9999 XYZ',
            'sim' => 'SIM A',
            'sim_exp' => '2026-12-31',
            'stnk_exp' => '2027-02-15',
        ]);

        $response->assertRedirect(route('registrasi-kendaraan'));
        $response->assertSessionHas('success');
    }
}
