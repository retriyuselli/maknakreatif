<?php

use App\Http\Controllers\FrontendDataPribadiController;
use App\Http\Controllers\InvoiceOrderController;
use App\Http\Controllers\OrderProfitLossController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\SimulasiDisplayController;
use App\Http\Controllers\ProductDisplayController;
use App\Http\Controllers\ProspectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SopViewController;
use App\Http\Controllers\SopPrintController;
use App\Http\Controllers\Front\VendorController;
use App\Http\Controllers\Front\ProjectController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\AuthController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\LeaveRequestController;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/', function () {
//     return view('landing'); 
// })->name('landing'); 

// Route::get('/', function () {
//     return redirect()->route('filament.admin.auth.login');
// });

// Home route with proper method handling
// Route::get('/', function () {
//     return redirect()->route('filament.admin.auth.login');
// })->name('home');

// SIMULASI
// Rute untuk preview HTML simulasi produk
Route::get('/simulasi/{record:slug}', [SimulasiDisplayController::class, 'show'])
    ->name('simulasi.show')
    ->middleware(\Filament\Http\Middleware\Authenticate::class);

// Rute untuk download PDF simulasi produk
Route::get('/simulasi/{record:slug}/download-pdf', [SimulasiDisplayController::class, 'downloadPdf'])
    ->name('simulasi.pdf')
    ->middleware(\Filament\Http\Middleware\Authenticate::class);

// PAYROLL SLIP GAJI
// Rute untuk download PDF slip gaji
Route::get('/payroll/{record}/slip-gaji', [App\Http\Controllers\PayrollSlipController::class, 'download'])
    ->name('payroll.slip-gaji.download')
    ->middleware(\Filament\Http\Middleware\Authenticate::class);

// LEAVE APPROVAL DETAIL
// Rute untuk melihat detail persetujuan cuti
Route::get('/leave-request/{leaveRequest}/approval-detail', [App\Http\Controllers\LeaveApprovalController::class, 'show'])
    ->name('leave-request.approval-detail')
    ->middleware(\Filament\Http\Middleware\Authenticate::class);

//INVOICE
Route::get('/invoice/{order}', [InvoiceOrderController::class, 'show'])
    ->name('invoice.show');
Route::get('/invoice/{order}/download', [InvoiceOrderController::class, 'download'])
    ->name('invoice.download');
Route::get('/invoice/{order}/print', [InvoiceOrderController::class, 'print'])
    ->name('invoice.print');
Route::post('/invoice/{order}/update-payment', [InvoiceOrderController::class, 'updatePayment'])
    ->name('invoice.update-payment')
    ->middleware(['web']);

// WIDGET ROUTE
//Widget yang langsung link ke processing
Route::get('/orders/reports/customer-payments/{status}', [ReportController::class, 'customerPayments'])
    ->name('reports.customer-payments');

// REPORT ROUTES
// Route untuk Laporan DataPembayaran HTML
Route::get('/laporan/pembayaran/html', [ReportController::class, 'generateDataPembayaranHtmlReport'])
    ->name('data-pembayaran.html-report')
    ->middleware(\Filament\Http\Middleware\Authenticate::class);

// Route untuk Laporan Pengeluaran Operasional HTML
Route::get('/laporan/expense-ops/html', [ReportController::class, 'generateExpenseOpsHtmlReport'])
    ->name('expense-ops.html-report')
    ->middleware(\Filament\Http\Middleware\Authenticate::class);



// PRODUCT ROUTES
//Detail product
Route::get('/products/{product:slug}', [ProductDisplayController::class, 'show'])
    ->name('products.show') 
    ->middleware(\Filament\Http\Middleware\Authenticate::class);

Route::get('/products/{product}/download-pdf', [ProductDisplayController::class, 'downloadPdf'])
    ->name('products.downloadPdf')
    ->middleware(\Filament\Http\Middleware\Authenticate::class);

// Route for product details (preview, download, print)
Route::get('/products/{product:slug}/details/{action}', [ProductDisplayController::class, 'details'])
    ->whereIn('action', ['preview', 'download', 'print'])
    ->name('products.details')
    ->middleware(\Filament\Http\Middleware\Authenticate::class);

// Route baru untuk ekspor detail produk ke Excel
Route::get('/products/{product}/export-excel-detail', [ProductDisplayController::class, 'exportDetailToExcel'])
    ->name('products.exportExcelDetail')
    ->middleware(\Filament\Http\Middleware\Authenticate::class);

// EXPENSE ROUTES
// Route untuk Laporan Pengeluaran Wedding HTML
Route::get('/laporan/expense/html', [ReportController::class, 'generateExpenseHtmlReport'])
    ->name('expense.html-report')
    ->middleware(\Filament\Http\Middleware\Authenticate::class);

// Route untuk Laporan Pengeluaran Operasional PDF
Route::get('/laporan/expense-ops/pdf', [ReportController::class, 'generateExpenseOpsPdfReport'])
    ->name('expense-ops.pdf-report')
    ->middleware(\Filament\Http\Middleware\Authenticate::class);

// Route untuk Laporan Pengeluaran Wedding PDF
Route::get('/laporan/expense/pdf', [ReportController::class, 'generateExpensePdfReport'])
    ->name('expense.pdf-report')
    ->middleware(\Filament\Http\Middleware\Authenticate::class);
    


//RUTE DATA PRIBADI
// Route untuk menampilkan form tambah data pribadi
Route::get('/data-pribadi/tambah', [FrontendDataPribadiController::class, 'create'])
    ->name('data-pribadi.create');

// Route untuk menampilkan daftar data pribadi
Route::get('/data-pribadi', [FrontendDataPribadiController::class, 'index'])
    ->name('data-pribadi.index');

// Route untuk menyimpan data baru dari form
Route::post('/data-pribadi', [FrontendDataPribadiController::class, 'store'])
    ->name('data-pribadi.store');

// Route untuk Prospect (Original)
Route::get('/prospect', [ProspectController::class, 'create'])
    ->name('prospect.form');

Route::post('/prospect', [ProspectController::class, 'store'])
    ->name('prospect.store');

Route::get('/prospect/success', [ProspectController::class, 'success'])
    ->name('prospect.success');

Route::post('/prospect/check-email', [ProspectController::class, 'checkEmail'])
    ->name('prospect.check-email');

// Route untuk ProspectApp (New - Modern Form)
Route::get('/prospect-app', [App\Http\Controllers\ProspectAppController::class, 'create'])
    ->name('prospect-app.form');

// Route untuk halaman pendaftaran prospect yang lebih lengkap
Route::get('/pendaftaran', [App\Http\Controllers\ProspectAppController::class, 'pendaftaran'])
    ->name('pendaftaran');

Route::post('/prospect-app', [App\Http\Controllers\ProspectAppController::class, 'store'])
    ->name('prospect-app.store');

Route::get('/prospect-app/success', [App\Http\Controllers\ProspectAppController::class, 'success'])
    ->name('prospect-app.success');

Route::post('/prospect-app/check-email', [App\Http\Controllers\ProspectAppController::class, 'checkEmail'])
    ->name('prospect-app.check-email');

// Route untuk download PDF Proposal ProspectApp
Route::get('/prospect-app/{prospectApp}/proposal/pdf', [App\Http\Controllers\ProspectAppController::class, 'generateProposalPdf'])
    ->middleware('auth')
    ->name('prospect-app.proposal.pdf');

// Route untuk download PDF Laporan Keuangan
Route::get('/laporan-keuangan/download-pdf', [App\Filament\Pages\LaporanKeuangan::class, 'handleDownloadPdf'])
    ->middleware('auth')
    ->name('laporan-keuangan.download-pdf');

// SOP ROUTES
// Route untuk menampilkan daftar SOP (untuk user biasa)
Route::get('/sop', [SopViewController::class, 'index'])
    ->name('sop.index')
    ->middleware('auth');

// Route untuk menampilkan detail SOP
Route::get('/sop/{id}', [SopViewController::class, 'show'])
    ->name('sop.show')
    ->middleware('auth');

// Route untuk print SOP
Route::get('/sop/{id}/print', [SopPrintController::class, 'show'])
    ->name('sop.print')
    ->middleware('auth');

// Route untuk download PDF SOP (future enhancement)
Route::get('/sop/{id}/pdf', [SopPrintController::class, 'pdf'])
    ->name('sop.pdf')
    ->middleware('auth');

// Route untuk pencarian SOP via AJAX
Route::get('/api/sop/search', [SopViewController::class, 'search'])
    ->name('sop.search')
    ->middleware('auth');

// Route untuk mendapatkan SOP berdasarkan kategori via AJAX
Route::get('/api/sop/category/{categoryId}', [SopViewController::class, 'byCategory'])
    ->name('sop.by-category')
    ->middleware('auth');


// Tampilan FrontEnd
Route::get('/', [HomeController::class, 'index'])->name('home');

// Frontend Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLoginForm'])->name('front.login');
    Route::post('/masuk', [AuthController::class, 'login']);
    Route::get('/daftar', [AuthController::class, 'showRegisterForm'])->name('front.register');
    Route::post('/daftar', [AuthController::class, 'register']);
});

// Frontend Logout Route
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Route yang lama - DIHAPUS karena sudah ada di bawah dengan controller
// Route::get('/project', function () {
//     return view('front.project');
// })->name('project');

Route::get('/anggota', function () {
    $teamMembers = \App\Models\Employee::with(['user', 'dataPribadi'])
        ->orderBy('date_of_join')
        ->get();
    
    // Statistics
    $stats = [
        'total_members' => $teamMembers->count(),
        'active_members' => $teamMembers->whereNull('date_of_out')->count(),
        'avg_experience' => $teamMembers->avg(function ($member) {
            return $member->date_of_join ? now()->diffInMonths($member->date_of_join) : 0;
        }),
        'positions' => $teamMembers->groupBy('position')->map->count(),
        'total_orders' => $teamMembers->sum('em_count'),
        'avg_salary' => $teamMembers->avg('salary'),
    ];
    
    return view('front.anggota', compact('teamMembers', 'stats'));
})->name('anggota');

Route::get('/kontak', function () {
    return view('front.kontak');
})->name('kontak');

Route::get('/harga', function () {
    return view('front.harga');
})->name('harga');

Route::get('/blog', [BlogController::class, 'index'])->name('blog');
Route::get('/blog/search', [BlogController::class, 'search'])->name('blog.search');
Route::get('/blog/category/{category}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/tutorial', function () {
    return view('blog.tutorial');
})->name('blog.tutorial');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.detail');

// Route untuk dashboard (setelah login)
Route::get('/dashboard', function () {
    return redirect()->route('filament.admin.auth.login');
})->name('dashboard')->middleware('auth');

// Route untuk profile
// Route::get('/profile', function () {
//     return redirect()->route('filament.admin.auth.login');
// })->name('profile')->middleware('auth');

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/show', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/profile/report', [ProfileController::class, 'generateReport'])->name('profile.report');
    Route::get('/profile/events', [ProfileController::class, 'getEvents'])->name('profile.events');
    Route::get('/profile/benefits', [ProfileController::class, 'getBenefits'])->name('profile.benefits');
    
    // Leave Request Routes
    Route::get('/leave/show', [LeaveRequestController::class, 'create'])->name('leave.create');
    Route::post('/leave/store', [LeaveRequestController::class, 'store'])->name('leave.store');
    Route::put('/leave/{id}', [LeaveRequestController::class, 'update'])->name('leave.update');
    Route::get('/leave/index', [LeaveRequestController::class, 'index'])->name('leave.index');
    Route::get('/leave/status', [LeaveRequestController::class, 'status'])->name('leave.status');
    Route::post('/leave/{id}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave.cancel');
    Route::get('/leave/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('leave.show');
    Route::get('/leave/document/download/{path}', [LeaveRequestController::class, 'downloadDocument'])->name('leave.document.download')->where('path', '.*');
});

// Login route for auth middleware redirects
Route::get('/login', function () {
    return redirect()->route('front.login');
})->name('login');

// Register route for header links
Route::get('/register', function () {
    return redirect()->route('front.register');
})->name('register');

Route::get('/vendor', [VendorController::class, 'index'])->name('vendor');

// Route untuk project management  
Route::middleware(['auth', 'project.access'])->group(function () {
    Route::get('/project', [ProjectController::class, 'index'])->name('project');
    Route::get('/project/{order}', [ProjectController::class, 'show'])->name('project.show');
    Route::get('/project-stats', [ProjectController::class, 'getStats'])->name('project.stats');
    Route::get('/project-export', [ProjectController::class, 'export'])->name('project.export');
});

// Admin routes for Account Manager Targets
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/targets/generate-12-months', function () {
        try {
            Artisan::call('targets:generate', ['--auto-12-months' => true]);
            return redirect()->back()->with('success', 'Account Manager targets for 12 months have been generated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to generate targets: ' . $e->getMessage());
        }
    })->name('admin.targets.generate-12-months');
    
    Route::get('/admin/targets/generate-from-orders', function () {
        try {
            Artisan::call('targets:generate', ['--update' => true]);
            return redirect()->back()->with('success', 'Account Manager targets have been generated and updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to generate targets: ' . $e->getMessage());
        }
    })->name('admin.targets.generate-from-orders');
});

// FALLBACK ROUTE - Modified to exclude Livewire routes
Route::fallback(function () {
    // Don't catch Livewire requests
    if (str_starts_with(request()->path(), 'livewire')) {
        abort(404);
    }
    
    if (request()->wantsJson()) {
        return response()->json(['error' => 'Not Found', 'url' => request()->url()], 404);
    }
    
    // For debugging - show which URL was accessed
    if (app()->environment('local')) {
        logger('Fallback route hit for URL: ' . request()->url());
    }
    
    return redirect()->route('home');
});
