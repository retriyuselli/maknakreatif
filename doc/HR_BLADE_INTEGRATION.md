# HR Salary & Leave Blade Template - Integration Documentation

## 🔄 Real Data Integration

### Overview

File `hr-salary-leave.blade.php` telah diupdate untuk menggunakan data real dari relasi User, Payroll, dan LeaveRequest, menggantikan data static/mock.

## 🔗 Model Relationships Used

### 1. **User Model**

```php
$user = auth()->user();
```

**Relationships:**

-   `$user->payrolls()` - One-to-Many relationship
-   `$user->leaveRequests()` - One-to-Many relationship

### 2. **Payroll Model**

```php
$latestPayroll = $user->payrolls()->latest()->first();
```

**Accessor Methods Used:**

-   `$latestPayroll->formatted_monthly_salary_with_prefix`
-   `$latestPayroll->formatted_calculated_annual_salary_with_prefix`
-   `$latestPayroll->formatted_total_compensation_with_prefix`
-   `$latestPayroll->formatted_bonus_with_prefix`
-   `$latestPayroll->pay_period`
-   `$latestPayroll->updated_at`

### 3. **LeaveRequest Model**

```php
$user->leaveRequests()->with('leaveType')
```

**Query Methods Used:**

-   Status filtering: `where('status', 'approved')`
-   Year filtering: `whereYear('start_date', $currentYear)`
-   Aggregation: `sum('total_days')`
-   Relationships: `with('leaveType')`

## 📊 Data Calculations

### Leave Statistics

```php
$leaveStats = [
    'approved' => $user->leaveRequests()
        ->where('status', 'approved')
        ->whereYear('start_date', $currentYear)
        ->sum('total_days'),
    'pending' => $user->leaveRequests()
        ->where('status', 'pending')
        ->whereYear('start_date', $currentYear)
        ->sum('total_days'),
    'rejected' => $user->leaveRequests()
        ->where('status', 'rejected')
        ->whereYear('start_date', $currentYear)
        ->sum('total_days')
];
```

### Leave by Type Breakdown

```php
$leaveByType = $user->leaveRequests()
    ->with('leaveType')
    ->where('status', 'approved')
    ->whereYear('start_date', $currentYear)
    ->get()
    ->groupBy('leaveType.name')
    ->map(function($leaves) {
        return $leaves->sum('total_days');
    });
```

### Remaining Leave Calculation

```php
$annualLeaveAllowance = 12;
$usedLeave = $leaveStats['approved'];
$remainingLeave = max(0, $annualLeaveAllowance - $usedLeave);
```

## 🎨 Template Sections

### 1. **Salary Information Section**

#### With Payroll Data:

-   ✅ Monthly Salary (formatted with prefix)
-   ✅ Annual Salary (calculated)
-   ✅ Total Compensation (with bonus)
-   ✅ Bonus amount
-   ✅ Pay period
-   ✅ Last updated date

#### Without Payroll Data:

-   ✅ Fallback empty state with message
-   ✅ Instruction to contact HR
-   ✅ Appropriate icon and styling

### 2. **Leave Information Section**

#### Leave Balance Progress Bar:

-   ✅ Visual progress bar showing used vs remaining
-   ✅ Percentage calculation
-   ✅ Current year context

#### Leave Statistics Grid:

-   🟢 **Approved**: Total approved leave days
-   🟡 **Pending**: Total pending leave days
-   🔴 **Rejected**: Total rejected leave days

#### Leave by Type Breakdown:

-   ✅ Groups approved leaves by leave type
-   ✅ Shows days taken per type
-   ✅ Only displays if there are approved leaves

#### Recent Leave Requests:

-   ✅ Shows latest 3 leave requests
-   ✅ Displays leave type, dates, duration, and status
-   ✅ Color-coded status badges
-   ✅ Only displays if user has leave requests

### 3. **Interactive Elements**

#### Request New Leave Button:

-   ✅ Links to `/admin/leave-requests/create`
-   ✅ Consistent styling with hover effects
-   ✅ Call-to-action positioning

## 🔄 Dynamic Behavior

### Data Loading States:

#### Salary Section:

```php
@if($latestPayroll)
    <!-- Show real payroll data -->
@else
    <!-- Show empty state -->
@endif
```

#### Leave Section:

```php
@if($leaveByType->isNotEmpty())
    <!-- Show leave type breakdown -->
@endif

@if($recentLeaves->isNotEmpty())
    <!-- Show recent requests -->
@endif
```

### Responsive Design:

-   ✅ Grid system: `grid-cols-1 md:grid-cols-2`
-   ✅ Mobile-first approach
-   ✅ Proper spacing and transitions

## 🎯 Features Added

### Real-Time Calculations:

1. **Current Year Focus**: All leave calculations use current year
2. **Live Statistics**: Data pulled fresh from database
3. **Proper Aggregation**: Uses Laravel's query builder for performance

### Enhanced User Experience:

1. **Progress Visualization**: Visual progress bar for leave usage
2. **Status Color Coding**: Green/Yellow/Red for different statuses
3. **Contextual Information**: Tooltips and detailed breakdowns
4. **Empty States**: Graceful handling of missing data

### Performance Optimizations:

1. **Efficient Queries**: Single query per data type
2. **Eager Loading**: Uses `with('leaveType')` to prevent N+1
3. **Laravel Collections**: Uses collection methods for data manipulation

## 🔧 Technical Implementation

### Blade Template Variables:

```php
// Core data
$user = auth()->user();
$latestPayroll = $user->payrolls()->latest()->first();
$currentYear = date('Y');

// Calculated statistics
$leaveStats = [...];
$leaveByType = [...];
$recentLeaves = [...];

// Derived values
$annualLeaveAllowance = 12;
$usedLeave = $leaveStats['approved'];
$remainingLeave = max(0, $annualLeaveAllowance - $usedLeave);
```

### Query Optimization:

-   Uses relationship methods for efficient database queries
-   Applies filters at database level
-   Aggregates data using SQL functions
-   Minimizes memory usage with targeted data retrieval

### Error Handling:

-   Checks for null payroll data
-   Handles empty collections gracefully
-   Provides fallback values where appropriate
-   Uses safe navigation with null coalescing

## 📱 UI/UX Improvements

### Visual Hierarchy:

1. **Section Headers**: Clear iconography and typography
2. **Data Cards**: Gradient backgrounds for visual appeal
3. **Status Badges**: Consistent color coding across all elements
4. **Progress Indicators**: Visual representation of leave usage

### Micro-interactions:

1. **Hover Effects**: Scale transforms on interactive elements
2. **Smooth Transitions**: CSS transitions for better feel
3. **Color Gradients**: Modern gradient backgrounds
4. **Icon Integration**: Consistent SVG icons throughout

### Information Architecture:

1. **Logical Grouping**: Salary and leave information separated
2. **Contextual Data**: Recent activity and historical breakdown
3. **Actionable Elements**: Clear call-to-action for new requests
4. **Progressive Disclosure**: Details shown progressively

## 🚀 Benefits

### For Employees:

-   **Self-Service Dashboard**: Complete salary and leave overview
-   **Real-Time Data**: Always up-to-date information
-   **Visual Insights**: Easy-to-understand progress bars and charts
-   **Quick Actions**: Direct link to request new leave

### For HR:

-   **Reduced Inquiries**: Employees can self-serve basic information
-   **Data Transparency**: Open access to relevant personal data
-   **Process Efficiency**: Streamlined leave request workflow

### For System:

-   **Performance**: Optimized queries and caching
-   **Maintainability**: Clean separation of concerns
-   **Scalability**: Efficient data handling for large user bases
-   **Consistency**: Unified data presentation across platform

## 🔍 Testing Scenarios

### With Payroll Data:

1. User with complete payroll information
2. Multiple payroll records (shows latest)
3. Various salary ranges and bonus structures

### Without Payroll Data:

1. New user without payroll setup
2. Empty state display and messaging
3. Graceful degradation of UI

### With Leave Data:

1. User with multiple leave types
2. Mixed status leave requests
3. Leave usage at different levels (low/medium/high)

### Without Leave Data:

1. New user with no leave history
2. User with only rejected/pending leaves
3. Cross-year leave request handling

This comprehensive integration provides a rich, data-driven user experience that leverages the full power of Laravel's Eloquent relationships! 🎉
