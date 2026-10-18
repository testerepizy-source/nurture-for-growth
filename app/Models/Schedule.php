<?php
namespace App\Models;
use Core\Model;

class Schedule extends Model
{
    protected static string $table = 'schedules';

    public static function forProvider(int $userId): array
    {
        return self::raw('SELECT * FROM schedules WHERE user_id = ? ORDER BY day_of_week ASC, start_time ASC', [$userId]);
    }

    public static function getAvailableSlots(int $userId, int $daysAhead = 14, ?string $mode = null): array
    {
        $schedules = self::forProvider($userId);
        if (empty($schedules)) {
            return [];
        }

        $today = new \DateTimeImmutable('today');
        $now = new \DateTimeImmutable();
        $slots = [];

        for ($i = 0; $i <= $daysAhead; $i++) {
            $date = $today->modify("+{$i} days");
            $weekday = (int)$date->format('w');

            foreach ($schedules as $schedule) {
                if (!(int) $schedule['is_active']) {
                    continue;
                }
                if ((int)$schedule['day_of_week'] !== $weekday) {
                    continue;
                }
                $scheduleMode = (string) ($schedule['mode'] ?? 'both');
                if ($mode !== null && $scheduleMode !== 'both' && $scheduleMode !== $mode) {
                    continue;
                }

                $timeString = trim($schedule['start_time']);
                $endTimeString = trim($schedule['end_time']);
                $format = substr_count($timeString, ':') === 1 ? 'Y-m-d H:i' : 'Y-m-d H:i:s';
                $slotDateTime = \DateTimeImmutable::createFromFormat($format, $date->format('Y-m-d') . ' ' . $timeString);
                $endDateTime = \DateTimeImmutable::createFromFormat($format, $date->format('Y-m-d') . ' ' . $endTimeString);
                $duration = max(15, (int) ($schedule['slot_duration_minutes'] ?? 30));
                if (!$slotDateTime || !$endDateTime || $endDateTime <= $slotDateTime) {
                    continue;
                }

                while ($slotDateTime->modify("+{$duration} minutes") <= $endDateTime) {
                    $slotEnd = $slotDateTime->modify("+{$duration} minutes");
                    $isBooked = self::rawOne("SELECT id FROM consultations
                                               WHERE provider_id = ?
                                                 AND status NOT IN ('cancelled', 'rejected', 'no_show')
                                                 AND schedule_at < ?
                                                 AND DATE_ADD(schedule_at, INTERVAL duration_minutes MINUTE) > ?
                                               LIMIT 1", [$userId, $slotEnd->format('Y-m-d H:i:s'), $slotDateTime->format('Y-m-d H:i:s')]);

                    if ($slotDateTime > $now && !$isBooked) {
                        $slots[] = [
                            'datetime' => $slotDateTime->format('Y-m-d\TH:i'),
                            'date' => $slotDateTime->format('Y-m-d'),
                            'date_label' => $slotDateTime->format('d M Y'),
                            'time' => $slotDateTime->format('H:i'),
                            'duration_minutes' => $duration,
                            'mode' => $scheduleMode,
                        ];
                    }
                    $slotDateTime = $slotEnd;
                }
            }
        }

        usort($slots, fn($a, $b) => strcmp($a['datetime'], $b['datetime']));
        return $slots;
    }

    public static function getSlotDurationForDateTime(int $userId, string $dateTime): int
    {
        try {
            $slotDateTime = new \DateTimeImmutable($dateTime);
        } catch (\Exception $e) {
            return 30;
        }

        $weekday = (int) $slotDateTime->format('w');
        $schedules = self::forProvider($userId);
        foreach ($schedules as $schedule) {
            if (!(int) $schedule['is_active'] || (int) $schedule['day_of_week'] !== $weekday) {
                continue;
            }

            $timeString = trim($schedule['start_time']);
            $endTimeString = trim($schedule['end_time']);
            $format = substr_count($timeString, ':') === 1 ? 'Y-m-d H:i' : 'Y-m-d H:i:s';
            $start = \DateTimeImmutable::createFromFormat($format, $slotDateTime->format('Y-m-d') . ' ' . $timeString);
            $end = \DateTimeImmutable::createFromFormat($format, $slotDateTime->format('Y-m-d') . ' ' . $endTimeString);

            if (!$start || !$end || $end <= $start) {
                continue;
            }

            $duration = max(15, (int) ($schedule['slot_duration_minutes'] ?? 30));
            if ($slotDateTime < $start || $slotDateTime->modify("+{$duration} minutes") > $end) {
                continue;
            }

            $offset = ($slotDateTime->getTimestamp() - $start->getTimestamp()) / 60;
            if ($offset % $duration === 0) {
                return $duration;
            }
        }

        return 30;
    }
}
