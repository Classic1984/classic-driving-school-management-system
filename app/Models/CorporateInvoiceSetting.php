<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * A single-row settings table holding the company/bank details and
 * document-number prefixes printed on every corporate quotation, invoice,
 * and receipt.
 */
class CorporateInvoiceSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_name',
        'tagline',
        'slogan',
        'address',
        'bank_account_name',
        'bank_name',
        'bank_account_number',
        'phone',
        'email',
        'website',
        'signature_path',
        'invoice_prefix',
        'quotation_prefix',
        'receipt_prefix',
        'payment_terms',
        'programme_options',
        'duration_options',
        'driver_count_options',
        'service_options',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quotation_numbering_year' => 'integer',
            'quotation_numbering_sequence' => 'integer',
            'invoice_numbering_year' => 'integer',
            'invoice_numbering_sequence' => 'integer',
            'receipt_numbering_year' => 'integer',
            'receipt_numbering_sequence' => 'integer',
        ];
    }

    /**
     * payment_terms is stored as one bullet per line - split into a clean
     * list for the printed document, same convention as
     * CorporateInvoice::courseCoverageList().
     *
     * @return array<int, string>
     */
    public function paymentTermsList(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->payment_terms))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Suggested Programme values for the quotation/invoice create forms'
     * pick-from-a-list-or-type-your-own field, one per line.
     *
     * @return array<int, string>
     */
    public function programmeOptionsList(): array
    {
        return $this->splitLines($this->programme_options);
    }

    /**
     * Suggested Duration values, same convention as programmeOptionsList().
     *
     * @return array<int, string>
     */
    public function durationOptionsList(): array
    {
        return $this->splitLines($this->duration_options);
    }

    /**
     * Suggested Number of Drivers values, same convention as
     * programmeOptionsList().
     *
     * @return array<int, string>
     */
    public function driverCountOptionsList(): array
    {
        return $this->splitLines($this->driver_count_options);
    }

    /**
     * Suggested line-item description values for a quotation/invoice's
     * Charges table (e.g. "Certificate of Completion"), same convention as
     * programmeOptionsList().
     *
     * @return array<int, string>
     */
    public function serviceOptionsList(): array
    {
        return $this->splitLines($this->service_options);
    }

    /**
     * @return array<int, string>
     */
    private function splitLines(?string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * The signature image as a data: URI, for embedding directly in a PDF.
     * dompdf can't fetch remote URLs (enable_remote is off) and the
     * "public" disk can be S3-backed in production (see
     * config/filesystems.php), so a plain local file path or a Storage URL
     * won't reliably work either - inlining the bytes sidesteps both.
     */
    public function signatureDataUri(): ?string
    {
        if (! $this->signature_path || ! Storage::disk('public')->exists($this->signature_path)) {
            return null;
        }

        $mimeType = Storage::disk('public')->mimeType($this->signature_path);
        $contents = base64_encode(Storage::disk('public')->get($this->signature_path));

        return "data:{$mimeType};base64,{$contents}";
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * The one settings row, created on first access if it doesn't exist yet
     * rather than requiring a seeder - there's nothing meaningful to
     * pre-fill except the numbering prefixes, which the Director can still
     * change later. The prefix defaults are passed explicitly here (not
     * left to the migration's column default) because Eloquent doesn't
     * re-read column defaults back into the model after an insert - without
     * this, the freshly created instance's prefix attributes would be null
     * in PHP even though the database row itself correctly has "QUO" etc.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'invoice_prefix' => 'INV',
            'quotation_prefix' => 'QUO',
            'receipt_prefix' => 'REC',
            'website' => 'classicdriving.com.ng',
            'programme_options' => "Defensive Driving\nAuto Course\nManual Course\nCombined Course\nAuto Advanced\nManual Advanced",
            'duration_options' => "One Day\nThree Days\nOne Week\nTwo Weeks\nOne Month",
            'driver_count_options' => "1\n5\n10\n20\n50",
            'service_options' => "Certificate of Completion\nRegistration Fee\nTraining Materials\nAssessment Fee",
        ]);
    }

    /**
     * The next number in a document type's per-year sequence (1, 2, 3...
     * for quotations issued in 2026, starting back at 1 for 2027), so
     * printed numbers read INV-2026-00001, INV-2027-00001 rather than
     * counting up forever across years. Locks the settings row for the
     * duration of the transaction so two documents created back-to-back
     * can never be handed the same number.
     */
    public static function nextSequence(string $type, int $year): int
    {
        static::current();

        return DB::transaction(function () use ($type, $year) {
            $settings = static::query()->lockForUpdate()->firstOrFail();

            $yearColumn = "{$type}_numbering_year";
            $sequenceColumn = "{$type}_numbering_sequence";

            $sequence = $settings->{$yearColumn} === $year ? $settings->{$sequenceColumn} + 1 : 1;

            $settings->forceFill([$yearColumn => $year, $sequenceColumn => $sequence])->save();

            return $sequence;
        });
    }
}
