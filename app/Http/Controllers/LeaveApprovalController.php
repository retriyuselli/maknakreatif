<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;

class LeaveApprovalController extends Controller
{
    public function show(LeaveRequest $leaveRequest)
    {
        // Check if the leave request is approved
        if ($leaveRequest->status !== 'approved') {
            abort(404, 'Approval detail not found or request not approved.');
        }

        // Load necessary relationships
        $leaveRequest->load(['user', 'leaveType', 'approver']);

        // Get leave balance information
        $leaveBalance = LeaveBalance::where('user_id', $leaveRequest->user_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->first();

        return view('leave-approval.detail', [
            'record' => $leaveRequest,
            'leaveBalance' => $leaveBalance
        ]);
    }
}
