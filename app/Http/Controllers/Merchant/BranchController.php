<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;

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

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'status' => 'nullable|boolean',
            'is_main' => 'nullable|boolean',
        ]);

        $data['user_id'] = auth()->id();
        $data['status'] = $data['status'] ?? 1;
        $data['is_main'] = $data['is_main'] ?? 0;


        if ($data['is_main'] == 1) {
            \App\Models\Branch::where('user_id', auth()->id())
                ->update(['is_main' => 0]);
        }

        $branch = \App\Models\Branch::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Branch created successfully',
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
        $branch = \App\Models\Branch::where('user_id', auth()->id())->find($id);

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

        // 🔥 If setting this as main → remove main from others
        if (isset($data['is_main']) && $data['is_main'] == 1) {
            \App\Models\Branch::where('user_id', auth()->id())
                ->where('id', '!=', $branch->id)
                ->update(['is_main' => 0]);
        }

        $branch->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Branch updated successfully',
            'data' => $branch
        ]);
    }


    // DELETE
    public function destroy($id)
    {
        $branch = Branch::where('user_id', auth()->id())->find($id);

        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'Branch not found'
            ], 404);
        }

        $branch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Branch deleted successfully'
        ]);
    }


}
