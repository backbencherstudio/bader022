<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Subscription;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::where('user_id', auth()->id())->get();

        return response()->json([
            'success' => true,
            'message' => 'Branch list fetched successfully',
            'data' => $branches
        ]);
    }

    // public function store(Request $request)
    // {
    //     $data = $request->validate([
    //         'name' => 'required|string',
    //         'phone' => 'nullable|string',
    //         'address' => 'nullable|string',
    //         'status' => 'nullable|boolean',
    //         'is_main' => 'nullable|boolean',
    //     ]);

    //     $data['user_id'] = auth()->id();
    //     $data['status'] = $data['status'] ?? 1;
    //     $data['is_main'] = $data['is_main'] ?? 0;


    //     if ($data['is_main'] == 1) {
    //         \App\Models\Branch::where('user_id', auth()->id())
    //             ->update(['is_main' => 0]);
    //     }

    //     $branch = \App\Models\Branch::create($data);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Branch created successfully',
    //         'data' => $branch
    //     ]);
    // }

    public function store(Request $request)
    {
        $userId = auth()->id();

        $userSubscription = Subscription::where('user_id', $userId)->first();

        if ($userSubscription && $userSubscription->plan_id == 1) {
            $branchCount = Branch::where('user_id', $userId)->count();

            if ($branchCount >= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your current plan only allows creating 1 branch.'
                ], 403);
            }
        }

        $data = $request->validate([
            'name' => 'required|string|unique:branches,name',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'status' => 'nullable|boolean',
            'is_main' => 'nullable|boolean',
        ]);

        $data['user_id'] = $userId;
        $data['status'] = $data['status'] ?? 1;
        $data['is_main'] = $data['is_main'] ?? 0;

        if ($data['is_main'] == 1) {
        Branch::where('user_id', $userId)
                ->update(['is_main' => 0]);
        }

        $branch = Branch::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Branch created successfully!',
            'data' => $branch
        ]);
    }

    public function show($id)
    {
        $branch = Branch::where('user_id', auth()->id())->find($id);

        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'Branch not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Branch details',
            'data' => $branch
        ]);
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::where('user_id', auth()->id())->find($id);

        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'Branch not found'
            ], 404);
        }

        $data = $request->validate([
            'name' => 'sometimes|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'status' => 'nullable|boolean',
            'is_main' => 'nullable|boolean',
        ]);

        if (isset($data['is_main']) && $data['is_main'] == 1) {
           Branch::where('user_id', auth()->id())
                ->where('id', '!=', $branch->id)
                ->update(['is_main' => 0]);
        }

        $branch->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Branch updated successfully!',
            'data' => $branch
        ]);
    }


    public function destroy($id)
    {

        $branch = Branch::where('user_id', auth()->id())->find($id);

        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'Branch not found'
            ], 404);
        }

        if ($branch->is_main == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Main branch cannot be deleted. Set another branch as main first.'
            ], 403);
        }

        $hasDependencies = $branch->staffs()->exists() ||
                           $branch->services()->exists() ||
                           $branch->bookings()->exists();

        if ($hasDependencies) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete branch! It has associated staff, services, or bookings.'
            ], 422);
        }

        $branch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Branch deleted successfully!'
        ]);
    }


    public function setMainBranch(Request $request, $id)
    {

        Branch::where('user_id', auth()->id())
            ->where('is_main', 1)
            ->update(['is_main' => 0]);

        $branch = Branch::where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        if ($branch) {
            $branch->update(['is_main' => 1]);

            return response()->json([
                'success' => true,
                'message' => 'Branch ' . $branch->name . ' is now set as the main branch.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Branch not found.'
        ], 404);
    }

}
