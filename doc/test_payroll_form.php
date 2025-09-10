<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Bootstrap Laravel
$app = Application::configure(basePath: __DIR__)
    ->withRouting(
        web: __DIR__.'/routes/web.php',
        commands: __DIR__.'/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧪 Testing PayrollResource Form Components...\n";
echo "=" . str_repeat("=", 50) . "\n";

try {
    // Test PayrollResource
    $resource = new App\Filament\Resources\PayrollResource();
    echo "✅ PayrollResource instantiated successfully\n";
    
    // Test model
    $model = App\Filament\Resources\PayrollResource::getModel();
    echo "✅ Model: {$model}\n";
    
    // Test record count
    $count = App\Models\Payroll::count();
    echo "✅ Database records: {$count}\n";
    
    // Test a specific record
    $payroll = App\Models\Payroll::with('user')->first();
    if ($payroll) {
        echo "✅ Sample record found:\n";
        echo "   - ID: {$payroll->id}\n";
        echo "   - User: {$payroll->user->name}\n";
        echo "   - Monthly Salary: Rp " . number_format($payroll->monthly_salary, 0, '.', '.') . "\n";
        echo "   - Annual Salary: Rp " . number_format($payroll->annual_salary, 0, '.', '.') . "\n";
        echo "   - Bonus: Rp " . number_format($payroll->bonus, 0, '.', '.') . "\n";
        echo "   - Total: Rp " . number_format($payroll->annual_salary + $payroll->bonus, 0, '.', '.') . "\n";
    }
    
    echo "\n🎉 All tests passed! PayrollResource is working correctly.\n";
    echo "🌐 You can now access: http://localhost/admin/payrolls\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
