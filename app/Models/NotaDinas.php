<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotaDinas extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'no_nd',
        'tanggal',
        'pengirim_id',      // relasi ke user/admin
        'penerima_id',      // relasi ke user/finance
        'sifat',
        'hal',
        'status',           // draft, diajukan, disetujui, dibayar, ditolak
        'catatan',
        'approved_by',      // user id yang approve
        'approved_at',
    ];

    public function details()
    {
        return $this->hasMany(NotaDinasDetail::class, 'nota_dinas_id');
    }

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }

    public function penerima()
    {
        return $this->belongsTo(User::class, 'penerima_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getFormattedLabelAttribute()
    {
        return "ND-{$this->no_nd} - {$this->hal}";
    }

    /**
     * Override delete to prevent deletion if has related details
     */
    public function delete()
    {
        // Check if NotaDinas has related details
        $detailCount = $this->details()->count();
        
        if ($detailCount > 0) {
            throw new \Exception("Cannot delete Nota Dinas '{$this->no_nd}' because it has {$detailCount} related detail record(s). Please remove all details first.");
        }

        return parent::delete();
    }

    /**
     * Force delete with cascade deletion of all related records
     * Use with extreme caution - this will permanently delete all related data
     */
    public function forceDelete()
    {
        try {
            // Start database transaction
            DB::beginTransaction();

            // Store record info for logging
            $recordId = $this->id;
            $recordNo = $this->no_nd;

            // Force delete all related details first
            // Get all details (including soft deleted ones) and force delete them individually
            $details = $this->details()->withTrashed()->get();
            $detailsDeleted = 0;
            
            foreach ($details as $detail) {
                $detail->forceDelete();
                $detailsDeleted++;
            }

            // Then force delete the NotaDinas itself using raw query to ensure it works
            $result = $this->newQuery()->where('id', $recordId)->forceDelete();

            DB::commit();
            
            // Log the successful deletion
            Log::info("Force deleted NotaDinas {$recordNo} (ID: {$recordId}) with {$detailsDeleted} details");
            
            return $result;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to force delete NotaDinas: " . $e->getMessage());
            throw new \Exception("Failed to force delete Nota Dinas: " . $e->getMessage());
        }
    }
}
