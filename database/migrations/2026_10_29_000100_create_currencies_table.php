<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table): void {
            $table->id();
            $table->char('code', 3)->unique();
            $table->string('name', 100)->unique();
            $table->string('symbol', 10);
            $table->decimal('exchange_rate', 18, 8);
            $table->timestamps();
        });

        // Second line of defense (Constitution II.3): the database itself rejects invalid rates.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE currencies ADD CONSTRAINT currencies_exchange_rate_positive CHECK (exchange_rate > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
