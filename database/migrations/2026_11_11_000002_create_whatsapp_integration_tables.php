<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. WhatsApp Integrations (One per tenant)
        if (! Schema::hasTable('whatsapp_integrations')) {
            Schema::create('whatsapp_integrations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->unique()->constrained('tenants')->cascadeOnDelete();
                $table->string('waba_id')->nullable()->index();
                $table->string('phone_number_id')->nullable()->unique();
                $table->string('display_phone_number')->nullable();
                $table->text('access_token')->nullable(); // encrypted via model cast
                $table->timestamp('token_expires_at')->nullable();
                $table->string('status', 20)->default('disconnected')->index(); // connected, disconnected, expired, failed
                $table->timestamp('connected_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        // 2. WhatsApp Conversations (One per patient per tenant)
        if (! Schema::hasTable('whatsapp_conversations')) {
            Schema::create('whatsapp_conversations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('integration_id')->constrained('whatsapp_integrations')->cascadeOnDelete();
                $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
                $table->string('wa_id', 30)->index(); // clean phone digits (e.g. 8801711223344)
                $table->string('status', 25)->default('active')->index(); // active, waiting_human, resolved
                $table->string('current_step', 40)->default('IDLE'); // IDLE, AWAITING_NAME, AWAITING_CHAMBER, etc.
                $table->json('state_data')->nullable(); // state payload for booking steps
                $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('last_inbound_at')->nullable();
                $table->timestamp('last_message_at')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'wa_id']);
            });
        }

        // 3. WhatsApp Messages (Inbound and Outbound)
        if (! Schema::hasTable('whatsapp_messages')) {
            Schema::create('whatsapp_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('conversation_id')->constrained('whatsapp_conversations')->cascadeOnDelete();
                $table->string('wamid')->unique(); // Meta message identifier
                $table->enum('direction', ['inbound', 'outbound'])->index();
                $table->string('message_type', 30)->default('text'); // text, interactive, template, etc.
                $table->text('message_body')->nullable();
                $table->string('media_id')->nullable();
                $table->string('delivery_status', 25)->default('sent')->index(); // sent, delivered, read, failed
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->timestamps();

                $table->index(['conversation_id', 'created_at']);
            });
        }

        // 4. Webhook Events (Centralized Meta Event Ledger with Idempotency)
        if (! Schema::hasTable('webhook_events')) {
            Schema::create('webhook_events', function (Blueprint $table) {
                $table->id();
                $table->string('event_key')->unique(); // Meta entry[].id or wamid or hash for idempotency
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
                $table->string('event_type', 50)->index(); // messages, message_status, etc.
                $table->json('payload');
                $table->string('processing_status', 25)->default('pending')->index(); // pending, processing, processed, failed
                $table->unsignedInteger('attempts')->default(0);
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('whatsapp_conversations');
        Schema::dropIfExists('whatsapp_integrations');
    }
};
