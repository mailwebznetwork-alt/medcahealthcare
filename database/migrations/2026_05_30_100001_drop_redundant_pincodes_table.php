<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('pincodes');
    }

    public function down(): void
    {
        // Legacy table intentionally not recreated; use pin_codes as the single source of truth.
    }
};
