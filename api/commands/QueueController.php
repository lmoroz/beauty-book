<?php

declare(strict_types=1);

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class QueueController extends Controller
{
    public string $queue = 'notifications';

    public int $timeout = 5;

    private bool $shouldStop = false;

    public function options($actionID): array
    {
        return array_merge(parent::options($actionID), ['queue', 'timeout']);
    }

    public function optionAliases(): array
    {
        return array_merge(parent::optionAliases(), [
            'q' => 'queue',
            't' => 'timeout',
        ]);
    }

    public function actionListen(): int
    {
        $this->registerSignals();

        $this->stdout("=== Notification Queue Worker ===\n", Console::FG_CYAN, Console::BOLD);
        $this->stdout("Queue: {$this->queue}\n");
        $this->stdout("Timeout: {$this->timeout}s\n");
        $this->stdout("Waiting for jobs...\n\n");

        $processed = 0;
        $errors = 0;

        while (!$this->shouldStop) {
            $this->dispatchSignals();

            $job = Yii::$app->queue->pop($this->queue, $this->timeout);

            if ($job === null) {
                continue;
            }

            $processed++;
            $type = $job['type'] ?? 'unknown';
            $jobId = $job['_id'] ?? '?';

            $this->stdout(
                sprintf("[%s] #%d Processing: %s (job: %s)\n", date('H:i:s'), $processed, $type, $jobId),
                Console::FG_GREEN
            );

            try {
                $this->processJob($type, $job);
            } catch (\Throwable $e) {
                $errors++;
                Yii::error(
                    sprintf("Queue job failed [%s]: %s\n%s", $type, $e->getMessage(), $e->getTraceAsString()),
                    'queue'
                );
                $this->stderr(
                    sprintf("  ✗ Error: %s\n", $e->getMessage()),
                    Console::FG_RED
                );
            }
        }

        $this->stdout(
            sprintf("\nShutting down. Processed: %d, Errors: %d\n", $processed, $errors),
            Console::FG_YELLOW
        );

        return ExitCode::OK;
    }

    public function actionInfo(): int
    {
        $size = Yii::$app->queue->size($this->queue);

        $this->stdout("Queue: {$this->queue}\n");
        $this->stdout("Pending jobs: {$size}\n", $size > 0 ? Console::FG_YELLOW : Console::FG_GREEN);

        return ExitCode::OK;
    }

    public function actionPurge(): int
    {
        $size = Yii::$app->queue->size($this->queue);

        if ($size === 0) {
            $this->stdout("Queue '{$this->queue}' is already empty.\n", Console::FG_GREEN);
            return ExitCode::OK;
        }

        if (!$this->confirm("Purge {$size} jobs from '{$this->queue}'?")) {
            return ExitCode::OK;
        }

        Yii::$app->queue->clear($this->queue);
        $this->stdout("Purged {$size} jobs.\n", Console::FG_YELLOW);

        return ExitCode::OK;
    }

    protected function processJob(string $type, array $job): void
    {
        switch ($type) {
            case 'booking_confirmation':
                $this->handleBookingConfirmation($job);
                break;

            case 'booking_cancellation':
                $this->handleBookingCancellation($job);
                break;

            case 'booking_reminder':
                $this->handleBookingReminder($job);
                break;

            default:
                Yii::warning("Unknown job type: {$type}", 'queue');
                $this->stdout("  ⚠ Unknown job type: {$type}\n", Console::FG_YELLOW);
        }
    }

    protected function handleBookingConfirmation(array $job): void
    {
        $bookingId = $job['booking_id'] ?? null;

        if (!$bookingId) {
            throw new \RuntimeException('booking_id is missing from job payload.');
        }

        $booking = \app\models\Booking::findOne($bookingId);
        if (!$booking) {
            throw new \RuntimeException("Booking #{$bookingId} not found.");
        }

        if ($booking->status === \app\models\Booking::STATUS_PENDING) {
            $booking->status = \app\models\Booking::STATUS_CONFIRMED;
            $booking->save(false);
        }

        Yii::info(
            sprintf(
                'Booking #%d confirmed. Client: %s, Phone: %s',
                $booking->id,
                $booking->client_name,
                $booking->client_phone
            ),
            'queue'
        );

        $this->stdout(
            sprintf("  ✓ Confirmed booking #%d for %s\n", $booking->id, $booking->client_name),
            Console::FG_GREEN
        );
    }

    protected function handleBookingCancellation(array $job): void
    {
        $bookingId = $job['booking_id'] ?? null;
        $reason = $job['reason'] ?? 'No reason provided';

        if (!$bookingId) {
            throw new \RuntimeException('booking_id is missing from job payload.');
        }

        Yii::info(
            sprintf('Cancellation notification for booking #%d. Reason: %s', $bookingId, $reason),
            'queue'
        );

        $this->stdout(
            sprintf("  ✓ Cancellation notice sent for booking #%d\n", $bookingId),
            Console::FG_GREEN
        );
    }

    protected function handleBookingReminder(array $job): void
    {
        $bookingId = $job['booking_id'] ?? null;

        if (!$bookingId) {
            throw new \RuntimeException('booking_id is missing from job payload.');
        }

        $booking = \app\models\Booking::findOne($bookingId);
        if (!$booking) {
            Yii::warning("Reminder skipped: booking #{$bookingId} not found.", 'queue');
            return;
        }

        if ($booking->status === \app\models\Booking::STATUS_CANCELLED) {
            $this->stdout(
                sprintf("  ⊘ Reminder skipped for cancelled booking #%d\n", $bookingId),
                Console::FG_YELLOW
            );
            return;
        }

        Yii::info(
            sprintf('Reminder sent for booking #%d. Client: %s', $booking->id, $booking->client_name),
            'queue'
        );

        $this->stdout(
            sprintf("  ✓ Reminder sent for booking #%d (%s)\n", $booking->id, $booking->client_name),
            Console::FG_GREEN
        );
    }

    private function registerSignals(): void
    {
        if (!extension_loaded('pcntl')) {
            $this->stdout("pcntl not available — Ctrl+C will hard-kill the worker.\n", Console::FG_YELLOW);
            return;
        }

        pcntl_async_signals(true);

        $handler = function (int $signal): void {
            $this->stdout("\nReceived signal {$signal}, shutting down gracefully...\n", Console::FG_YELLOW);
            $this->shouldStop = true;
        };

        pcntl_signal(SIGTERM, $handler);
        pcntl_signal(SIGINT, $handler);
    }

    private function dispatchSignals(): void
    {
        if (extension_loaded('pcntl')) {
            pcntl_signal_dispatch();
        }
    }
}
