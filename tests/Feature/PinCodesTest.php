<?php

use App\Models\PinCode;
use App\Models\User;
use App\ModuleAccess;
use Illuminate\Http\UploadedFile;

it('forbids pin codes when the user lacks operations access', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'module_access' => collect(ModuleAccess::keys())
            ->mapWithKeys(fn (string $k) => [$k => $k === ModuleAccess::DASHBOARD])
            ->all(),
    ]);

    $this->actingAs($user)
        ->get(route('operations.pin-codes.index'))
        ->assertForbidden();
});

it('allows operations users to open the pin codes directory', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'module_access' => collect(ModuleAccess::keys())
            ->mapWithKeys(fn (string $k) => [$k => $k === ModuleAccess::OPERATIONS])
            ->all(),
    ]);

    $this->actingAs($user)
        ->get(route('operations.pin-codes.index'))
        ->assertOk()
        ->assertSee(__('Pin codes'), false);
});

it('creates a pin code from the form', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'module_access' => collect(ModuleAccess::keys())
            ->mapWithKeys(fn (string $k) => [$k => $k === ModuleAccess::OPERATIONS])
            ->all(),
    ]);

    $this->actingAs($user)
        ->post(route('operations.pin-codes.store'), [
            'pincode' => '560076',
            'area_name' => 'Arekere',
            'city' => 'Bangalore',
            'locality' => 'Bannerghatta Road',
            'is_serviceable' => '1',
            'is_active' => '1',
            'delivery_charge' => '49.00',
            'meta_title' => 'Arekere 560076',
            'meta_description' => 'Service area',
            'seo_keywords' => 'arekere, bangalore',
            'slug' => '',
            'geo_page_ready' => '0',
        ])
        ->assertRedirect(route('operations.pin-codes.index'));

    $row = PinCode::query()->where('pincode', '560076')->first();
    expect($row)->not->toBeNull()
        ->and($row->is_serviceable)->toBeTrue()
        ->and($row->slug)->not->toBeNull();
});

it('imports CSV rows and skips duplicate pincodes', function () {
    PinCode::factory()->create(['pincode' => '560001', 'area_name' => 'Existing', 'city' => 'Bangalore']);

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'module_access' => collect(ModuleAccess::keys())
            ->mapWithKeys(fn (string $k) => [$k => $k === ModuleAccess::OPERATIONS])
            ->all(),
    ]);

    $csv = "pincode,area_name,city,locality,serviceability,delivery_charge\n560001,Duplicate,BLR,,1,\n560002,New Area,Bangalore,JP Nagar,1,25.50\n";

    $this->actingAs($user)
        ->post(route('operations.pin-codes.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('pins.csv', $csv),
        ])
        ->assertRedirect(route('operations.pin-codes.index'));

    expect(PinCode::query()->where('pincode', '560002')->exists())->toBeTrue()
        ->and(PinCode::query()->where('pincode', '560001')->count())->toBe(1);
});

it('rejects CSV import when required columns are missing', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'module_access' => collect(ModuleAccess::keys())
            ->mapWithKeys(fn (string $k) => [$k => $k === ModuleAccess::OPERATIONS])
            ->all(),
    ]);

    $csv = "foo,bar\n1,2\n";

    $this->actingAs($user)
        ->post(route('operations.pin-codes.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('bad.csv', $csv),
        ])
        ->assertRedirect(route('operations.pin-codes.index'));

    $result = session('import_result');
    expect($result)->toBeArray()
        ->and($result['created'] ?? 0)->toBe(0)
        ->and($result['errors'] ?? [])->not->toBeEmpty();
});
