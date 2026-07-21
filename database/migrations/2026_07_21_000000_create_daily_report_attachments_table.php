<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_report_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_report_id')->constrained()->cascadeOnDelete();
            $table->string('section', 20);
            $table->string('attachment_key', 100);
            $table->string('context');
            $table->string('path');
            $table->timestamps();

            $table->index(['daily_report_id', 'section', 'attachment_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_report_attachments');
    }
};
