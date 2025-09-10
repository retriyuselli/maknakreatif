#!/bin/bash

# 🚀 Makna Finance Seeder Management Script
# Usage: ./seeder-helper.sh [command]

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_header() {
    echo -e "${BLUE}"
    echo "=================================="
    echo "🎯 Makna Finance Seeder Helper"
    echo "=================================="
    echo -e "${NC}"
}

# Function to check if we're in Laravel project
check_laravel() {
    if [ ! -f "artisan" ]; then
        print_error "Not in Laravel project directory. Please run from project root."
        exit 1
    fi
}

# Function to fresh setup
fresh_setup() {
    print_header
    print_info "Starting fresh database setup..."
    
    print_warning "This will delete ALL data in database!"
    read -p "Are you sure? (y/N): " -n 1 -r
    echo
    
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        print_info "Running migrate:fresh --seed..."
        php artisan migrate:fresh --seed
        
        print_info "Creating storage link..."
        php artisan storage:link
        
        print_info "Clearing caches..."
        php artisan optimize:clear
        
        print_success "Fresh setup completed!"
        show_data_count
    else
        print_info "Operation cancelled."
    fi
}

# Function to run all seeders
run_all_seeders() {
    print_header
    print_info "Running all seeders..."
    
    php artisan db:seed
    
    print_success "All seeders completed!"
    show_data_count
}

# Function to run specific seeder
run_specific_seeder() {
    print_header
    
    if [ -z "$1" ]; then
        print_error "Please provide seeder name."
        echo "Usage: ./seeder-helper.sh specific StatusSeeder"
        list_available_seeders
        exit 1
    fi
    
    seeder_name="$1"
    
    # Add Seeder suffix if not present
    if [[ ! "$seeder_name" =~ Seeder$ ]]; then
        seeder_name="${seeder_name}Seeder"
    fi
    
    print_info "Running $seeder_name..."
    
    if php artisan db:seed --class="$seeder_name"; then
        print_success "$seeder_name completed!"
    else
        print_error "Failed to run $seeder_name"
        exit 1
    fi
}

# Function to show data count
show_data_count() {
    print_info "Checking database counts..."
    
    php artisan tinker --execute="
    echo '📊 Database Statistics:' . PHP_EOL;
    echo '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━' . PHP_EOL;
    
    // Master Data
    echo '📋 Master Data:' . PHP_EOL;
    echo '   Users: ' . str_pad(App\Models\User::count(), 8) . ' | Status: ' . str_pad(App\Models\Status::count(), 8) . PHP_EOL;
    echo '   Industries: ' . str_pad(App\Models\Industry::count(), 3) . ' | Categories: ' . str_pad((class_exists('App\Models\Category') ? App\Models\Category::count() : 0), 5) . PHP_EOL;
    
    // Business Data
    echo PHP_EOL . '💼 Business Data:' . PHP_EOL;
    echo '   Prospects: ' . str_pad(App\Models\Prospect::count(), 5) . ' | Orders: ' . str_pad(App\Models\Order::count(), 8) . PHP_EOL;
    echo '   Vendors: ' . str_pad(App\Models\Vendor::count(), 7) . ' | Products: ' . str_pad((class_exists('App\Models\Product') ? App\Models\Product::count() : 0), 6) . PHP_EOL;
    
    // Financial Data (optional, might not exist)
    echo PHP_EOL . '💰 Financial Data:' . PHP_EOL;
    echo '   Bank Statements: ' . str_pad((class_exists('App\Models\BankStatement') ? App\Models\BankStatement::count() : 0), 3) . PHP_EOL;
    
    echo '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━' . PHP_EOL;
    "
}

# Function to list available seeders
list_available_seeders() {
    print_info "Available seeders:"
    
    echo "📋 Master Data Seeders:"
    echo "   • StatusSeeder"
    echo "   • IndustrySeeder" 
    echo "   • CategorySeeder"
    echo "   • PaymentMethodSeeder"
    echo "   • RoleSeeder"
    echo
    echo "👤 User Seeders:"
    echo "   • UserSeeder"
    echo "   • EmployeeSeeder"
    echo "   • DataPribadiSeeder"
    echo
    echo "💼 Business Seeders:"
    echo "   • VendorSeeder"
    echo "   • ProductSeeder"
    echo "   • ProspectSeeder"
    echo "   • ProspectAppSeeder"
    echo "   • OrderSeeder"
    echo "   • SimulasiProdukSeeder"
    echo
    echo "💰 Financial Seeders:"
    echo "   • BankStatementSeeder"
    echo "   • NotaDinasSeeder"
    echo "   • ExpenseOpsSeeder"
    echo "   • PendapatanLainSeeder"
    echo "   • PengeluaranLainSeeder"
    echo "   • AccountManagerTargetSeeder"
}

# Function to create new seeder
create_new_seeder() {
    print_header
    
    if [ -z "$1" ]; then
        print_error "Please provide seeder name."
        echo "Usage: ./seeder-helper.sh create ProductCategorySeeder"
        exit 1
    fi
    
    seeder_name="$1"
    
    # Add Seeder suffix if not present
    if [[ ! "$seeder_name" =~ Seeder$ ]]; then
        seeder_name="${seeder_name}Seeder"
    fi
    
    print_info "Creating $seeder_name..."
    
    # Generate seeder
    php artisan make:seeder "$seeder_name"
    
    # Copy template
    template_file="database/seeders/TEMPLATE_YourModelSeeder.php"
    seeder_file="database/seeders/$seeder_name.php"
    
    if [ -f "$template_file" ]; then
        print_info "Copying template to $seeder_file..."
        cp "$template_file" "$seeder_file"
        
        # Replace YourModel with actual name (remove Seeder suffix)
        model_name="${seeder_name%Seeder}"
        
        # Basic replacements
        sed -i.bak "s/YourModel/$model_name/g" "$seeder_file"
        sed -i.bak "s/YourModelSeeder/$seeder_name/g" "$seeder_file"
        sed -i.bak "s/your_models/${model_name,,}s/g" "$seeder_file"
        
        # Remove backup file
        rm "${seeder_file}.bak" 2>/dev/null || true
        
        print_success "$seeder_name created successfully!"
        print_warning "Don't forget to:"
        echo "1. Edit database/seeders/$seeder_name.php"
        echo "2. Add to DatabaseSeeder.php"
        echo "3. Test with: php artisan db:seed --class=$seeder_name"
    else
        print_warning "Template not found. Manual editing required."
    fi
}

# Function to test specific seeder
test_seeder() {
    if [ -z "$1" ]; then
        print_error "Please provide seeder name."
        echo "Usage: ./seeder-helper.sh test StatusSeeder"
        exit 1
    fi
    
    seeder_name="$1"
    
    # Add Seeder suffix if not present
    if [[ ! "$seeder_name" =~ Seeder$ ]]; then
        seeder_name="${seeder_name}Seeder"
    fi
    
    print_header
    print_info "Testing $seeder_name..."
    
    # Show count before
    print_info "Data count before seeding:"
    show_data_count
    
    # Run seeder
    if php artisan db:seed --class="$seeder_name"; then
        print_success "$seeder_name test completed!"
        
        # Show count after
        print_info "Data count after seeding:"
        show_data_count
    else
        print_error "Failed to test $seeder_name"
        exit 1
    fi
}

# Function to show help
show_help() {
    print_header
    echo "Usage: ./seeder-helper.sh [command] [arguments]"
    echo
    echo "Commands:"
    echo "  fresh                 - Fresh database setup (migrate:fresh --seed)"
    echo "  all                   - Run all seeders"
    echo "  specific <name>       - Run specific seeder"
    echo "  create <name>         - Create new seeder from template"
    echo "  test <name>           - Test specific seeder"
    echo "  list                  - List available seeders"
    echo "  count                 - Show database record counts"
    echo "  help                  - Show this help"
    echo
    echo "Examples:"
    echo "  ./seeder-helper.sh fresh"
    echo "  ./seeder-helper.sh specific StatusSeeder"
    echo "  ./seeder-helper.sh create ProductCategorySeeder"
    echo "  ./seeder-helper.sh test UserSeeder"
    echo
    print_info "For more detailed documentation, see SEEDER_README.md"
}

# Main script logic
main() {
    check_laravel
    
    case "${1:-help}" in
        "fresh")
            fresh_setup
            ;;
        "all")
            run_all_seeders
            ;;
        "specific")
            run_specific_seeder "$2"
            ;;
        "create")
            create_new_seeder "$2"
            ;;
        "test")
            test_seeder "$2"
            ;;
        "list")
            list_available_seeders
            ;;
        "count")
            show_data_count
            ;;
        "help"|*)
            show_help
            ;;
    esac
}

# Run main function with all arguments
main "$@"
