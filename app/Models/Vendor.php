<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Vendor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'slug',
        'pic_name',
        'address',
        'status',
        'stock',
        'description',
        'harga_publish',
        'harga_vendor',
        'bank_name',
        'account_holder',
        'kontrak_kerjasama',
        'bank_account',
        'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function productVendors(): HasMany
    {
        return $this->hasMany(ProductVendor::class);
    }

    public function notaDinasDetails(): HasMany
    {
        return $this->hasMany(NotaDinasDetail::class);
    }

    /**
     * Override delete method to check for dependencies
     */
    public function delete()
    {
        // Check if vendor is used in products
        $productVendorCount = $this->productVendors()->count();
        $expenseCount = $this->vendors()->count(); // expenses relationship
        $notaDinasCount = $this->notaDinasDetails()->count(); // nota dinas details relationship
        
        if ($productVendorCount > 0 || $expenseCount > 0 || $notaDinasCount > 0) {
            $details = [];
            if ($productVendorCount > 0) {
                $details[] = "{$productVendorCount} product(s)";
            }
            if ($expenseCount > 0) {
                $details[] = "{$expenseCount} expense(s)";
            }
            if ($notaDinasCount > 0) {
                $details[] = "{$notaDinasCount} nota dinas detail(s)";
            }
            
            throw new \Exception(
                'Cannot delete vendor because it is being used in ' . 
                implode(' and ', $details) . '. ' .
                'Please remove these associations first.'
            );
        }
        
        return parent::delete();
    }

    /**
     * Override forceDelete method to handle cascading deletes
     */
    public function forceDelete()
    {
        try {
            // Start database transaction
            DB::beginTransaction();
            
            // Delete related records first to avoid foreign key constraints
            // Use raw database queries to delete even soft-deleted records
            DB::table('product_vendors')->where('vendor_id', $this->id)->delete();
            DB::table('expenses')->where('vendor_id', $this->id)->delete();
            DB::table('nota_dinas_details')->where('vendor_id', $this->id)->delete();
            
            // Force delete the vendor using raw query to bypass soft delete
            $result = DB::table('vendors')->where('id', $this->id)->delete();
            
            DB::commit();
            return $result > 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
