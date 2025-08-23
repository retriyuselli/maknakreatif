<?php

namespace App\Imports;

use App\Models\ExpenseOps;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ExpenseOpsImport implements 
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithBatchInserts,
    WithChunkReading
{
    use Importable, SkipsErrors, SkipsFailures;

    private $rowCount = 0;

    /**
     * Transform Excel row into ExpenseOps model
     *
     * @param array $row
     * @return \App\Models\ExpenseOps|null
     */
    public function model(array $row)
    {
        // Skip empty rows
        if (empty(array_filter($row))) {
            return null;
        }

        $this->rowCount++;

        return new ExpenseOps([
            'name' => $row['name'],
            'amount' => $this->transformAmount($row['amount']),
            'date_expense' => $this->transformDate($row['date_expense']),
            'no_nd' => $row['no_nd'] ?? null,
            'note' => $row['note'] ?? null,
        ]);
    }

    /**
     * Transform amount from various formats to decimal
     *
     * @param mixed $amount
     * @return float
     */
    private function transformAmount($amount)
    {
        // Handle string amounts
        if (is_string($amount)) {
            // Remove currency symbols and thousand separators
            $amount = preg_replace('/[^0-9.-]/', '', $amount);
            
            // Handle negative amounts in parentheses
            if (preg_match('/\((.*?)\)/', $amount)) {
                $amount = '-' . $amount;
            }
        }

        return (float) $amount;
    }

    /**
     * Transform date from various formats to Carbon instance
     *
     * @param mixed $date
     * @return \Carbon\Carbon
     */
    private function transformDate($date)
    {
        try {
            // Handle Excel date format
            if (is_numeric($date)) {
                return Carbon::instance(Date::excelToDateTimeObject($date));
            }

            // Handle string date
            return Carbon::parse($date);
        } catch (\Exception $e) {
            return Carbon::now();
        }
    }

    /**
     * Validation rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric'],
            'date_expense' => ['required'],
            'no_nd' => ['nullable', 'numeric'],
            'note' => ['nullable', 'string'],
        ];
    }

    /**
     * Custom validation messages
     *
     * @return array
     */
    public function customValidationMessages(): array
    {
        return [
            'name.required' => 'The expense name field is required',
            'name.max' => 'The expense name must not exceed 255 characters',
            'amount.required' => 'The amount field is required',
            'amount.numeric' => 'The amount must be a number',
            'date_expense.required' => 'The expense date field is required',
        ];
    }

    /**
     * Batch size for performance optimization
     *
     * @return int
     */
    public function batchSize(): int
    {
        return 1000;
    }

    /**
     * Chunk size for memory optimization
     *
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * Get the number of imported rows
     *
     * @return int
     */
    public function getRowCount(): int
    {
        return $this->rowCount;
    }
}