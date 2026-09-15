<?php

namespace App\Models;

use Database\Factories\ActivityLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * A general-purpose "who did what and when" trail across the system's
 * everyday actions (registering a student, recording a payment, logging
 * attendance, etc.), independent from the narrower, structured
 * DiscountAuditLog/ReactivationAuditLog which track those two specific
 * events with their own dedicated fields.
 */
class ActivityLog extends Model
{
    /** @use HasFactory<ActivityLogFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'description',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A best-effort icon and color for this entry's timeline marker, guessed
     * from its free-text description - there's no structured "type" column,
     * every action just writes a human-readable sentence. Matched by
     * keyword, most specific first, with a generic fallback for anything
     * that doesn't match a known pattern.
     *
     * Returns a color *key*, not Tailwind classes - the caller (a blade
     * view) is responsible for mapping that key to literal class strings.
     * Tailwind's content scan only covers resources/views/**\/*.blade.php,
     * so a literal class string sitting in this file would never be picked
     * up by the build and would silently render with no styling at all.
     *
     * @return array{icon: string, color: string}
     */
    public function iconMeta(): array
    {
        $description = $this->description;

        return match (true) {
            str_contains($description, 'backup') => [
                'icon' => 'M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z',
                'color' => 'sky',
            ],
            str_contains($description, 'Re-sent') => [
                'icon' => 'M6 12 3.269 3.126A59.768 59.768 0 0 1 21.485 12 59.77 59.77 0 0 1 3.27 20.876L5.999 12Zm0 0h7.5',
                'color' => 'purple',
            ],
            str_contains($description, 'Revoked') => [
                'icon' => 'M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636',
                'color' => 'red',
            ],
            str_contains($description, 'Granted') || str_contains($description, 'Enabled') => [
                'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
                'color' => 'green',
            ],
            str_contains($description, 'started processing') || str_contains($description, 'processing status') => [
                'icon' => 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99',
                'color' => 'blue',
            ],
            str_contains($description, 'Recorded a payment') => [
                'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z',
                'color' => 'green',
            ],
            str_contains($description, 'Charged') || str_contains($description, 'expense') => [
                'icon' => 'M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-19.5 0V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-5.25m-19.5 0h19.5',
                'color' => 'amber',
            ],
            str_contains($description, 'certificate') => [
                'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
                'color' => 'indigo',
            ],
            str_contains($description, 'correction') => [
                'icon' => 'm16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125',
                'color' => 'orange',
            ],
            str_contains($description, 'Registered') || str_contains($description, 'Enrolled') => [
                'icon' => 'M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z',
                'color' => 'teal',
            ],
            default => [
                'icon' => 'M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.563.563 0 0 0-.586 0L6.982 21.14a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z',
                'color' => 'gray',
            ],
        };
    }

    /**
     * Record an action taken by the currently authenticated user (or the
     * given user, for console/system-triggered actions with no request).
     */
    public static function record(string $description, ?User $user = null): self
    {
        return static::create([
            'user_id' => ($user ?? Auth::user())?->id,
            'description' => $description,
        ]);
    }

    /**
     * The broad business categories the Activity Log can be browsed by
     * (Driver's License, Training & Assessments, Payments, etc.), so a
     * director can jump straight to "what happened with Driver's License"
     * instead of scrolling one long mixed timeline. There's no structured
     * "type" column, so - the same as iconMeta() - each entry is matched by
     * keyword against its free-text description, most specific first (a
     * catalog-service name like "Driver's License" is checked well before
     * the generic "payment"/"enrollment" buckets, so a combined "Registered
     * X for Driver's License Processing and recorded a payment" lands under
     * Driver's License, not Payments or Enrollment).
     *
     * ActivityLogController::applyCategory() rebuilds this exact ordering
     * as a SQL LIKE/NOT LIKE chain (this category's keywords, excluding
     * every earlier category's keywords) so a tile's count and its
     * filtered list always agree with category() below, and every entry
     * lands in exactly one bucket.
     *
     * @return list<array{key: string, label: string, icon: string, color: string, keywords: list<string>}>
     */
    public static function categories(): array
    {
        return [
            ['key' => 'drivers_license', 'label' => "Driver's License", 'color' => 'blue', 'icon' => self::ID_CARD_ICON, 'keywords' => ["Driver's License"]],
            ['key' => 'learners_permit', 'label' => "Learner's Permit", 'color' => 'teal', 'icon' => self::ID_CARD_ICON, 'keywords' => ["Learner's Permit"]],
            ['key' => 'online_certificate', 'label' => 'Online Certificate', 'color' => 'indigo', 'icon' => self::CERTIFICATE_ICON, 'keywords' => ['Online Certificate']],
            ['key' => 'student_certificate', 'label' => 'Student Certificate', 'color' => 'purple', 'icon' => self::CERTIFICATE_ICON, 'keywords' => ['Student Certificate']],
            ['key' => 'corporate', 'label' => 'Corporate', 'color' => 'cyan', 'icon' => 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21', 'keywords' => ['corporate']],
            ['key' => 'payments', 'label' => 'Payments', 'color' => 'green', 'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z', 'keywords' => ['payment']],
            ['key' => 'certificates', 'label' => 'Certificates', 'color' => 'emerald', 'icon' => self::CERTIFICATE_ICON, 'keywords' => ['certificate']],
            ['key' => 'training', 'label' => 'Training & Assessments', 'color' => 'amber', 'icon' => 'M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84A50.654 50.654 0 0 0 19.74 10.147m-15.482 0a50.717 50.717 0 0 1 7.74-3.342 50.717 50.717 0 0 1 7.74 3.342', 'keywords' => ['training attendance', 'for the theory class', 'assessment']],
            ['key' => 'theory_classes', 'label' => 'Theory Classes', 'color' => 'orange', 'icon' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25', 'keywords' => ['theory class']],
            ['key' => 'approvals', 'label' => 'Approvals & Corrections', 'color' => 'red', 'icon' => 'm16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125', 'keywords' => ['correction', 'discount']],
            ['key' => 'enrollment', 'label' => 'Students & Enrollment', 'color' => 'sky', 'icon' => 'M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z', 'keywords' => ['registered student', 'enrolled ', 'enrollment', 'updated student', 'deleted student', 'changed', 'upgraded', 'app access to student', 'app access from student']],
            ['key' => 'courses', 'label' => 'Courses', 'color' => 'violet', 'icon' => 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z', 'keywords' => ['course']],
            ['key' => 'instructors', 'label' => 'Instructors', 'color' => 'fuchsia', 'icon' => 'M4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632ZM15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z', 'keywords' => ['instructor']],
            ['key' => 'vehicles', 'label' => 'Vehicles', 'color' => 'lime', 'icon' => 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 0h-12', 'keywords' => ['vehicle']],
            ['key' => 'staff', 'label' => 'Staff', 'color' => 'rose', 'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z', 'keywords' => ['staff account']],
            ['key' => 'leads', 'label' => 'Leads', 'color' => 'pink', 'icon' => 'M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73s-.316 1.319-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46', 'keywords' => ['inquiry', 'booking']],
            ['key' => 'services', 'label' => 'Other Services', 'color' => 'amber', 'icon' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a7.723 7.723 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a7.65 7.65 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28Z', 'keywords' => ['billable service', 'charged', 'processing status', 'started processing']],
            ['key' => 'expenses', 'label' => 'Expenses', 'color' => 'orange', 'icon' => 'M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-19.5 0V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-5.25m-19.5 0h19.5', 'keywords' => ['expense']],
            ['key' => 'system', 'label' => 'System', 'color' => 'gray', 'icon' => 'M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z', 'keywords' => ['backup', 'reminder']],
        ];
    }

    /**
     * The fallback bucket for a description that matches none of
     * categories() - always checked last.
     *
     * @return array{key: string, label: string, icon: string, color: string, keywords: list<string>}
     */
    public static function otherCategory(): array
    {
        return [
            'key' => 'other',
            'label' => 'Other',
            'color' => 'gray',
            'icon' => 'M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.563.563 0 0 0-.586 0L6.982 21.14a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z',
            'keywords' => [],
        ];
    }

    /**
     * The category() this entry falls under, matched the same way as
     * iconMeta() - first keyword match wins, checked in categories()'s
     * order - or otherCategory() if nothing matches.
     *
     * @return array{key: string, label: string, icon: string, color: string, keywords: list<string>}
     */
    public function category(): array
    {
        foreach (static::categories() as $category) {
            if (Str::contains($this->description, $category['keywords'], true)) {
                return $category;
            }
        }

        return static::otherCategory();
    }

    private const ID_CARD_ICON = 'M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6.75-10.5a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-4.5 4.5a4.5 4.5 0 0 1 4.5 0';

    private const CERTIFICATE_ICON = 'M9 4.5h6M9 4.5a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 4.5M9 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v12A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25v-12A2.25 2.25 0 0 0 17.25 4.5H15M9 12.75l2.25 2.25L15 10.5';
}
