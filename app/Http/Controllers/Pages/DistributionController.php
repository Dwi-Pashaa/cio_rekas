<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Services\DistributionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DistributionController extends Controller
{
    protected DistributionService $distributionService;

    public function __construct(DistributionService $distributionService)
    {
        $this->distributionService = $distributionService;
    }

    /**
     * Halaman Manajemen Distribusi Utama.
     */
    public function indexUtama(Request $request)
    {
        $user = Auth::user();
        $utamaStocks = $this->distributionService->getUtamaStocks();
        $eligibleUsers = $this->distributionService->getEligibleCabangUsers($user ? $user->id : null);
        $productNames = $this->distributionService->getAvailableProductNames();
        $productList = $this->distributionService->getAllProductsWithBranch();

        $search = $request->search ?? null;
        $sort = (int)($request->sort ?? 10);
        $histories = $this->distributionService->getHistoriesPaginated(
            type: null,
            user: $user,
            search: $search,
            perPage: $sort
        );

        return view('pages.distribution.utama', compact(
            'utamaStocks',
            'eligibleUsers',
            'productNames',
            'productList',
            'histories'
        ));
    }

    /**
     * Admin menambah / top up saldo stok Distribusi Utama.
     */
    public function storeAdminTopup(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('Admin')) {
            return response()->json([
                'code' => 403,
                'status' => 'error',
                'message' => 'Hanya Admin yang dapat mengalokasikan stok ke Distribusi Utama.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'status' => 'error',
                'message' => 'Validasi gagal, silakan periksa inputan Anda.',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $history = $this->distributionService->addStockToUtama(
                productName: $request->product_name,
                qty: (int) $request->qty,
                notes: $request->notes,
                adminUser: $user
            );

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => "Berhasil menambahkan {$request->qty} stok voucher ke Distribusi Utama.",
                'data' => $history,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Distribusi Utama mendistribusikan stok ke User Distribusi Cabang.
     */
    public function storeUtamaToCabang(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'product_name' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'status' => 'error',
                'message' => 'Validasi gagal, periksa data yang dimasukkan.',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $history = $this->distributionService->distributeUtamaToCabang(
                receiverUserId: (int) $request->receiver_id,
                productName: $request->product_name,
                qty: (int) $request->qty,
                notes: $request->notes,
                senderUser: $user
            );

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => "Berhasil mendistribusikan {$request->qty} voucher ke Distribusi Cabang.",
                'data' => $history,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'code' => 400,
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Halaman Manajemen Distribusi Cabang.
     */
    public function indexCabang(Request $request)
    {
        $user = Auth::user();
        $cabangStocks = $this->distributionService->getCabangStocks($user);
        $branches = Branch::all();
        $productNames = $this->distributionService->getAvailableProductNames();

        $search = $request->search ?? null;
        $sort = (int)($request->sort ?? 10);
        $histories = $this->distributionService->getHistoriesPaginated(
            type: 'cabang_to_branch',
            user: $user,
            search: $search,
            perPage: $sort
        );

        return view('pages.distribution.cabang', compact(
            'cabangStocks',
            'branches',
            'productNames',
            'histories'
        ));
    }

    /**
     * Distribusi Cabang mendistribusikan stok ke Cabang Fisik (Outlet/POS).
     */
    public function storeCabangToBranch(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'target_branch_id' => 'required|exists:branche,id',
            'product_name' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'status' => 'error',
                'message' => 'Validasi gagal, periksa data yang dimasukkan.',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $history = $this->distributionService->distributeCabangToBranch(
                targetBranchId: (int) $request->target_branch_id,
                productName: $request->product_name,
                qty: (int) $request->qty,
                notes: $request->notes,
                senderUser: $user
            );

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => "Berhasil mendistribusikan {$request->qty} voucher ke cabang terkait.",
                'data' => $history,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'code' => 400,
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Halaman Riwayat Lengkap Distribusi.
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        $type = $request->type ?? null;
        $search = $request->search ?? null;
        $sort = (int)($request->sort ?? 10);

        $histories = $this->distributionService->getHistoriesPaginated(
            type: $type,
            user: $user,
            search: $search,
            perPage: $sort
        );

        return view('pages.distribution.history', compact('histories', 'type'));
    }
}
