<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestS3Connection extends Command
{
    protected $signature = 'test:s3';
    protected $description = 'Test S3/MinIO connection and configuration';

    public function handle()
    {
        $this->info("Testing S3/MinIO Connection...\n");

        // Test configuration
        $this->info("Configuration:");
        $this->line("FILESYSTEM_DISK: " . config('filesystems.default'));
        $this->line("AWS_ENDPOINT: " . config('filesystems.disks.s3.endpoint'));
        $this->line("AWS_BUCKET: " . config('filesystems.disks.s3.bucket'));
        $this->line("AWS_REGION: " . config('filesystems.disks.s3.region'));
        $this->line("AWS_USE_PATH_STYLE: " . (config('filesystems.disks.s3.use_path_style_endpoint') ? 'true' : 'false'));
        $this->line("AWS_ACCESS_KEY_ID: " . (config('filesystems.disks.s3.key') ? '***' . substr(config('filesystems.disks.s3.key'), -4) : 'NOT SET'));
        $this->newLine();

        // Test write
        try {
            $this->info("Testing write...");
            $disk = Storage::disk(config('filesystems.default') === 'local' ? 'public' : config('filesystems.default'));
            $testFile = 'test-' . time() . '.txt';
            $disk->put($testFile, 'Test content from Laravel');
            $this->line("✅ Write successful: {$testFile}");
            
            // Test read
            $this->info("Testing read...");
            $content = $disk->get($testFile);
            $this->line("✅ Read successful: " . strlen($content) . " bytes");
            
            // Test URL
            $this->info("Testing URL generation...");
            $url = $disk->url($testFile);
            $this->line("✅ URL: {$url}");
            
            // Test delete
            $this->info("Testing delete...");
            $disk->delete($testFile);
            $this->line("✅ Delete successful");
            
            $this->newLine();
            $this->info("🎉 All tests passed! MinIO/S3 is working correctly.");
            return 0;
        } catch (\Exception $e) {
            $this->error("\n❌ Test failed:");
            $this->error($e->getMessage());
            $this->newLine();
            $this->line("Trace:");
            $this->line($e->getTraceAsString());
            return 1;
        }
    }
}
