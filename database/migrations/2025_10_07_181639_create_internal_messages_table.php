<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('internal_messages', function (Blueprint $table) {
            $table->id();
            $table->string('subject', 255)->nullable()->index();
            $table->text('message');
            $table->enum('type', ['instruction', 'communication', 'announcement', 'task', 'reminder', 'feedback', 'urgent'])
                  ->default('communication')
                  ->index();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])
                  ->default('normal')
                  ->index();
            $table->enum('status', ['draft', 'sent', 'read', 'replied', 'archived', 'deleted'])
                  ->default('sent')
                  ->index();
            
            // Sender information
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            
            // Recipients (using JSON for multiple recipients) - NOT NULL with proper handling
            $table->json('recipient_ids'); // Array of user IDs - NOT NULL
            $table->json('cc_ids')->nullable(); // Carbon copy recipients
            $table->json('bcc_ids')->nullable(); // Blind carbon copy recipients
            
            // Message details
            $table->json('attachments')->nullable(); // File attachments
            $table->boolean('requires_response')->default(false);
            $table->timestamp('due_date')->nullable(); // For tasks/instructions
            $table->timestamp('read_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            
            // Thread/Reply system
            $table->foreignId('parent_id')->nullable()->constrained('internal_messages')->onDelete('cascade');
            $table->integer('thread_count')->unsigned()->default(0)->nullable(false); // Number of replies - NOT NULL with default
            
            // Organizational features
            $table->json('tags')->nullable(); // Custom tags for categorization
            $table->string('department', 100)->nullable()->index(); // Target department
            $table->boolean('is_public')->default(false)->index(); // Public announcements
            $table->boolean('is_pinned')->default(false)->index(); // Pin important messages
            
            // Tracking - NOT NULL with proper defaults
            $table->json('read_by'); // Track who read the message - NOT NULL
            $table->timestamp('expires_at')->nullable(); // Auto-delete date
            $table->json('deleted_by')->nullable()->comment('Array of user IDs who deleted this message for themselves'); // Soft delete tracking
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['sender_id', 'created_at']);
            $table->index(['type', 'priority']);
            $table->index(['status', 'created_at']);
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_messages');
    }
};
