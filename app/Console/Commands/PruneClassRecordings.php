<?php

namespace App\Console\Commands;

use App\Models\ClassRecording;
use App\Services\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Daily retention pruner — implements CLASS_RECORDING_CHECKLIST §9.
 *
 * Finds recordings where retention_expires_at < now AND preserved = false
 * AND status != 'archived'. For each match: deletes the underlying file from
 * storage, then updates the row to status = 'archived' (file gone) and
 * file_size_bytes = 0. The metadata row is intentionally NOT soft-deleted —
 * it stays visible in the list (with an "archived" badge) so the audit trail
 * for that recording remains discoverable. Manual deletes via the controller
 * DO soft-delete; the asymmetry is intentional.
 *
 * Schedule: routes/console.php at 02:00 Asia/Kuala_Lumpur (matches the
 * existing attendance:seed-today timezone).
 */
class PruneClassRecordings extends Command
{
    protected $signature = 'class-recordings:prune
                            {--dry-run : Report what would be pruned without deleting anything}
                            {--limit=500 : Maximum number of recordings to process this run}';

    protected $description = 'Delete files for class recordings past their retention window (preserved are exempt).';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit  = max(1, (int) $this->option('limit'));

        $now = now();
        $candidates = ClassRecording::query()
            ->where('preserved', false)
            ->where('status', '!=', ClassRecording::STATUS_ARCHIVED)
            ->whereNotNull('retention_expires_at')
            ->where('retention_expires_at', '<', $now)
            ->orderBy('retention_expires_at')
            ->limit($limit)
            ->get();

        if ($candidates->isEmpty()) {
            $this->info('No recordings past retention. Nothing to prune.');
            return self::SUCCESS;
        }

        $this->info(sprintf(
            '%s %d recording(s) past retention.',
            $dryRun ? 'Would prune' : 'Pruning',
            $candidates->count()
        ));

        $bytesFreed = 0;
        $filesDeleted = 0;
        $rowsArchived = 0;

        foreach ($candidates as $r) {
            $this->line(sprintf(
                '  - id=%d school=%d started=%s expired=%s size=%s',
                $r->id,
                $r->school_id,
                $r->started_at?->format('Y-m-d H:i'),
                $r->retention_expires_at?->format('Y-m-d H:i'),
                $r->file_size_bytes ? number_format($r->file_size_bytes / 1048576, 1).' MB' : '—'
            ));

            if ($dryRun) continue;

            // Delete file from storage if present. Failure is non-fatal —
            // we still archive the row (file may have been removed manually).
            try {
                $disk = Storage::disk($r->file_disk);
                if ($disk->exists($r->file_path)) {
                    $disk->delete($r->file_path);
                    $filesDeleted++;
                }
            } catch (\Throwable $e) {
                $this->warn("    storage delete failed for id={$r->id}: ".$e->getMessage());
            }

            $bytesFreed += (int) $r->file_size_bytes;

            $r->update([
                'status'          => ClassRecording::STATUS_ARCHIVED,
                'file_size_bytes' => 0,
            ]);
            $rowsArchived++;

            // System-initiated audit entry (no Auth::id() — falls back to NULL user_id).
            AuditLogger::log('class_recording.deleted', $r, [], [
                'reason' => 'retention',
            ], $r->school_id);
        }

        if ($dryRun) {
            $this->info('Dry run — nothing deleted.');
            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Done. Files deleted: %d. Rows archived: %d. Storage freed: %s MB.',
            $filesDeleted,
            $rowsArchived,
            number_format($bytesFreed / 1048576, 1)
        ));
        return self::SUCCESS;
    }
}
