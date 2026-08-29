<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\BatchStatusLog;
use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\RefundRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BatchManagementService
{
    /**
     * Create a new Batch and attach selected bookings.
     */
    public function createBatch(array $data, array $bookingIds, User $creator): Batch
    {
        return DB::transaction(function () use ($data, $bookingIds, $creator) {
            $startDate = Carbon::parse($data['start_date'])->startOfDay();
            $endDate = isset($data['end_date']) ? Carbon::parse($data['end_date'])->startOfDay() : $startDate->copy()->addDay();

            // Auto-generate or sanitize unified batch number / code
            $batchNumber = trim($data['batch_number'] ?? '');
            $name = trim($data['name'] ?? '');
            $batchCode = trim($data['batch_code'] ?? '');

            if ($batchNumber) {
                $name = $name ?: $batchNumber;
                $batchCode = $batchCode ?: $batchNumber;
            } else {
                $name = $name ?: ($startDate->format('M d') . '-' . $endDate->format('d') . ' Batch');
                $batchCode = $batchCode ?: ('Batch #' . $startDate->format('Y-m-d'));
            }

            // Guarantee unique batch_code even if pre-filled duplicate was submitted
            $counter = 1;
            $origCode = $batchCode;
            while (Batch::where('batch_code', $batchCode)->exists()) {
                $batchCode = $origCode . '-' . $counter;
                $counter++;
            }

            $batch = Batch::create([
                'name' => $name,
                'batch_code' => $batchCode,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'confirmed',
                'lifecycle_status' => 'confirmed',
                'risk_classification' => $data['risk_classification'] ?? 'safe',
                'capacity_note' => $data['capacity_note'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $creator->id,
            ]);

            // Link selected bookings
            if (!empty($bookingIds)) {
                Booking::whereIn('id', $bookingIds)->update([
                    'batch_id' => $batch->id,
                ]);
            }

            // Audit log
            BatchStatusLog::create([
                'batch_id' => $batch->id,
                'old_status' => null,
                'new_status' => 'confirmed',
                'changed_by' => $creator->id,
                'note' => "Batch created with " . count($bookingIds) . " attached booking(s).",
            ]);

            AuditLogger::log(
                'BATCH_CREATED',
                "Created batch {$batch->batch_code} ({$batch->name}) with " . count($bookingIds) . " booking(s).",
                $creator,
                $creator->name
            );

            // Auto-run initial live weather assessment if within 16-day forecast horizon
            try {
                $weatherService = app(\App\Services\WeatherForecastService::class);
                $weatherService->assessBatch($batch, null, $creator);
            } catch (\Exception $e) {
                // If beyond forecast horizon (>16 days) or service unreachable, proceed gracefully
            }

            return $batch;
        });
    }

    /**
     * Update whole-batch status and cascade to connected bookings.
     *
     * @throws Exception
     */
    public function updateStatus(Batch $batch, string $newStatus, User $changer, ?string $note = null): void
    {
        $validStatuses = ['confirmed', 'completed', 'rescheduled', 'cancelled_by_camp'];
        if (!in_array($newStatus, $validStatuses)) {
            throw new Exception("Invalid batch status: {$newStatus}");
        }

        DB::transaction(function () use ($batch, $newStatus, $changer, $note) {
            $oldStatus = $batch->status;
            if ($oldStatus === $newStatus) {
                return;
            }

            // 1. Update Batch model
            $updateData = [
                'status' => $newStatus,
                'lifecycle_status' => $newStatus,
            ];

            if ($newStatus === 'cancelled_by_camp') {
                $updateData['cancelled_at'] = now();
                $updateData['cancellation_reason'] = $note ?: 'Cancelled by Camp due to weather or operational advisory';
            } elseif ($newStatus === 'completed') {
                $updateData['completed_at'] = now();
            }

            $batch->update($updateData);

            // 2. Record in BatchStatusLog
            BatchStatusLog::create([
                'batch_id' => $batch->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $changer->id,
                'note' => $note,
            ]);

            // 3. Cascade logic to connected bookings
            $connectedBookings = $batch->bookings()
                ->whereNotIn('status', ['cancelled_by_guest', 'no_show'])
                ->get();

            foreach ($connectedBookings as $booking) {
                $bookingOldStatus = $booking->status;

                if ($newStatus === 'cancelled_by_camp') {
                    $booking->update(['status' => 'cancelled_by_camp']);

                    // Create auto refund eligibility trigger for paid bookings
                    $completedPayments = $booking->payments()->where('status', 'completed')->get();
                    foreach ($completedPayments as $payment) {
                        RefundRequest::create([
                            'payment_id' => $payment->id,
                            'booking_id' => $booking->id,
                            'requested_by' => 'camp_force_majeure',
                            'requested_at' => now(),
                            'eligibility_calculated' => [
                                'days_until_dive' => max(0, Carbon::now()->diffInDays($booking->start_date, false)),
                                'eligible_for_refund' => true,
                                'refund_percentage' => 100,
                                'window_label' => 'Camp Cancellation (100% Force Majeure Refund)',
                                'policy_action_text' => 'Camp cancelled batch due to marine safety / weather. Full refund approved.',
                            ],
                            'status' => 'pending',
                            'notes' => "Auto-generated refund request from batch cancellation: {$batch->batch_code}. Note: {$note}",
                        ]);
                    }

                    BookingStatusLog::create([
                        'booking_id' => $booking->id,
                        'old_status' => $bookingOldStatus,
                        'new_status' => 'cancelled_by_camp',
                        'changed_by' => $changer->id,
                        'note' => "Whole-batch cancellation: {$batch->name}. Full refund eligibility triggered. Custom email alert sent to {$booking->contact_email}.",
                    ]);

                } elseif ($newStatus === 'completed') {
                    $booking->update(['status' => 'completed']);

                    BookingStatusLog::create([
                        'booking_id' => $booking->id,
                        'old_status' => $bookingOldStatus,
                        'new_status' => 'completed',
                        'changed_by' => $changer->id,
                        'note' => "Concluded 2D1N dive session with batch {$batch->name}.",
                    ]);

                } elseif ($newStatus === 'rescheduled') {
                    $booking->update(['status' => 'rescheduled']);

                    BookingStatusLog::create([
                        'booking_id' => $booking->id,
                        'old_status' => $bookingOldStatus,
                        'new_status' => 'rescheduled',
                        'changed_by' => $changer->id,
                        'note' => "Batch {$batch->name} was rescheduled by camp. Customer notified via email to select their new preferred date via Manage Booking portal.",
                    ]);
                }
            }

            AuditLogger::log(
                'BATCH_STATUS_UPDATED',
                "Updated status of batch {$batch->batch_code} from '{$oldStatus}' to '{$newStatus}'. Cascaded to " . $connectedBookings->count() . " bookings.",
                $changer,
                $changer->name
            );
        });
    }

    /**
     * Move a booking from its current batch to a new batch (or remove from batch).
     */
    public function moveBooking(Booking $booking, ?Batch $targetBatch, User $changer, ?string $reason = null): void
    {
        $oldBatchId = $booking->batch_id;
        $oldBatchName = $booking->batch ? $booking->batch->name : 'Unbatched';
        $newBatchName = $targetBatch ? $targetBatch->name : 'Unbatched';

        $booking->update(['batch_id' => $targetBatch?->id]);

        BookingStatusLog::create([
            'booking_id' => $booking->id,
            'old_status' => $booking->status,
            'new_status' => $booking->status,
            'changed_by' => $changer->id,
            'note' => "Moved from batch [{$oldBatchName}] to [{$newBatchName}]. Reason: " . ($reason ?: 'Operational adjustment'),
        ]);

        AuditLogger::log(
            'BOOKING_REBATCHED',
            "Moved booking {$booking->booking_number} from [{$oldBatchName}] to [{$newBatchName}].",
            $changer,
            $changer->name
        );
    }

    /**
     * Get unbatched confirmed bookings for a specific dive date.
     */
    public function getUnbatchedBookingsForDate(Carbon $date): Collection
    {
        return Booking::with('participants')
            ->whereDate('start_date', $date->format('Y-m-d'))
            ->where(function ($q) {
                $q->whereNull('batch_id')
                  ->orWhere('batch_id', 0);
            })
            ->whereDoesntHave('batch')
            ->whereNotIn('status', ['cancelled_by_camp', 'cancelled_by_guest', 'completed', 'no_show', 'cancellation_requested'])
            ->get();
    }
}
