<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('role')->default('customer');
            $t->string('phone')->nullable();
        });
        Schema::create('settings', function (Blueprint $t) {
            $t->id();
            $t->unsignedInteger('open_hour')->default(7);
            $t->unsignedInteger('close_hour')->default(23);
            $t->unsignedInteger('dp_percent')->default(50);
            $t->unsignedInteger('lock_version')->default(0);
            $t->timestamps();
        });
        Schema::create('courts', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('sport');
            $t->text('description');
            $t->json('facilities');
            $t->unsignedInteger('hourly_rate');
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('bookings', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->foreignId('user_id')->constrained();
            $t->foreignId('court_id')->constrained();
            $t->string('court_name');
            $t->dateTime('starts_at');
            $t->dateTime('ends_at');
            $t->unsignedInteger('duration');
            $t->unsignedInteger('hourly_rate');
            $t->unsignedInteger('dp_percent');
            $t->unsignedInteger('total');
            $t->unsignedInteger('dp_amount');
            $t->unsignedInteger('paid_amount')->default(0);
            $t->string('status')->default('held');
            $t->string('payment_status')->default('unpaid');
            $t->dateTime('hold_expires_at');
            $t->dateTime('balance_due_at');
            $t->text('cancellation_reason')->nullable();
            $t->timestamps();
            $t->index(['status', 'starts_at']);
        });
        Schema::create('maintenances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('court_id')->constrained();
            $t->dateTime('starts_at');
            $t->dateTime('ends_at');
            $t->string('reason');
            $t->timestamps();
        });
        Schema::create('reservations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('court_id')->constrained();
            $t->dateTime('starts_at');
            $t->foreignId('booking_id')->nullable()->constrained();
            $t->foreignId('maintenance_id')->nullable()->constrained();
            $t->unique(['court_id', 'starts_at']);
        });
        Schema::create('payment_attempts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('booking_id')->constrained();
            $t->unsignedBigInteger('active_booking_id')->nullable()->unique();
            $t->string('order_id')->unique();
            $t->unsignedInteger('amount');
            $t->string('kind');
            $t->string('status')->default('creating');
            $t->text('snap_token')->nullable();
            $t->string('provider_transaction_id')->nullable()->unique();
            $t->dateTime('expires_at');
            $t->dateTime('verified_at')->nullable();
            $t->dateTime('credited_at')->nullable();
            $t->timestamps();
        });
        Schema::create('refund_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('booking_id')->constrained();
            $t->foreignId('payment_attempt_id')->nullable()->constrained();
            $t->string('reference')->unique();
            $t->unsignedInteger('amount');
            $t->string('reason');
            $t->string('status')->default('requested');
            $t->text('admin_notes')->nullable();
            $t->foreignId('handled_by')->nullable()->constrained('users');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['refund_requests', 'payment_attempts', 'reservations', 'maintenances', 'bookings', 'courts', 'settings'] as $table) {
            Schema::dropIfExists($table);
        } Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['role', 'phone']));
    }
};
