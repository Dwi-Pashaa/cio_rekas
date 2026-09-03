@extends('layouts.app')

@section('title', 'Kasir POS')
@section('pretitle', 'Input Transaksi Baru (NFC Agent)')

@section('header-actions')
    <div class="d-flex align-items-center gap-2">
        @if(isset($sumProduct))
            <div id="desktop-branch-stock-badge" class="d-none d-sm-flex align-items-center gap-2 px-3 py-1.5 bg-blue-50 border border-blue-200 rounded-xl text-xs font-semibold text-blue-700" style="transition: all 0.3s ease;">
                <x-icons.package class="w-4 h-4 text-blue-600" />
                <span>Sisa Stok di {{ $userBranch ?? 'Cabang' }}: <b id="desktop-branch-stock-val" class="text-primary font-bold">{{ number_format($sumProduct, 0, ',', '.') }}</b> unit</span>
            </div>
        @endif

        <a href="{{ route('transaksi.create') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-2 rounded-2 text-xs fw-semibold">
            <x-icons.barcode class="w-4 h-4" />
            <span>Mode Input Biasa</span>
        </a>
    </div>
@endsection

@section('content')
{{-- Banner NFC Active --}}
<div class="alert bg-blue-50 border border-blue-200 text-blue-900 rounded-3 p-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2 shadow-sm">
    <div class="d-flex align-items-center gap-2.5">
        <div class="w-8 h-8 rounded-circle bg-blue-600 text-white d-flex align-items-center justify-center shrink-0">
            <x-icons.check class="w-4 h-4" />
        </div>
        <div>
            <div class="fw-bold fs-6">NFC Agent Terdeteksi Otomatis</div>
            <div class="text-xs text-blue-700">Data profil agent dan nominal paket barang telah disiapkan secara otomatis.</div>
        </div>
    </div>
    <span class="badge bg-blue-600 text-white px-3 py-1.5 rounded-pill font-monospace fw-bold text-xs shadow-sm">
        SN: {{ $customer->code }}
    </span>
</div>

<div class="row g-3">
    {{-- KOLOM KIRI: Identitas Agent & Rincian Paket Barang --}}
    <div class="col-lg-6 col-12 space-y-3">
        
        {{-- Mobile Stock Badge --}}
        @if(isset($sumProduct))
            <div id="mobile-branch-stock-badge" class="d-sm-none d-flex align-items-center justify-content-between p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-800" style="transition: all 0.3s ease;">
                <span class="d-flex align-items-center gap-1.5 fw-medium">
                    <x-icons.package class="w-4 h-4 text-blue-600" />
                    Stok di {{ $userBranch ?? 'Cabang' }}
                </span>
                <span class="fw-bold bg-white px-2 py-0.5 rounded border border-blue-200"><b id="mobile-branch-stock-val">{{ number_format($sumProduct, 0, ',', '.') }}</b> unit</span>
            </div>
        @endif

        {{-- 1. Kartu Identitas Agent --}}
        <div class="card border-0 shadow-sm rounded-2xl">
            <div class="card-header bg-transparent border-bottom border-slate-100 py-3 d-flex align-items-center justify-content-between">
                <h4 class="card-title text-xs fw-bold text-slate-700 text-uppercase tracking-wider d-flex align-items-center gap-1.5 m-0">
                    <x-icons.users class="w-4 h-4 text-blue-600" />
                    Identitas Profil Agent
                </h4>
                @php
                    $statusName = strtolower(optional($customer->status)->name ?? 'aktif');
                    $badgeClass = 'bg-success-subtle text-success border-success-subtle';
                    if (str_contains($statusName, 'blokir') || str_contains($statusName, 'nonaktif')) {
                        $badgeClass = 'bg-danger-subtle text-danger border-danger-subtle';
                    }
                @endphp
                <span class="badge {{ $badgeClass }} border px-2.5 py-1 rounded-pill fw-semibold">
                    {{ optional($customer->status)->name ?? 'Aktif' }}
                </span>
            </div>
            <div class="card-body p-4 space-y-3">
                <div class="d-flex align-items-center gap-3 p-3 bg-blue-50 border border-blue-100 rounded-xl">
                    <div class="w-12 h-12 rounded-xl bg-blue-600 text-white fw-bold d-flex align-items-center justify-center shrink-0 fs-5 shadow-sm">
                        {{ strtoupper(substr($customer->name, 0, 2)) }}
                    </div>
                    <div class="overflow-hidden">
                        <h4 class="fw-bold text-slate-900 m-0 text-truncate fs-5">{{ $customer->name }}</h4>
                        <div class="d-flex align-items-center gap-2 mt-0.5">
                            <span class="badge bg-light text-primary border font-monospace fw-bold fs-8">SN: {{ $customer->code }}</span>
                            @if($customer->nik)
                                <span class="badge bg-light text-slate-700 border font-monospace fs-8">NIK: {{ $customer->nik }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row g-2 text-xs">
                    <div class="col-sm-6 col-12">
                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 h-100">
                            <span class="text-slate-400 d-block mb-0.5">No. Telepon / WA:</span>
                            <span class="text-slate-800 fw-bold fs-7">{{ $customer->telp ?: '-' }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6 col-12">
                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 h-100">
                            <span class="text-slate-400 d-block mb-0.5">Email:</span>
                            <span class="text-slate-800 fw-bold fs-7 text-truncate d-block" title="{{ $customer->email }}">{{ $customer->email ?: '-' }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6 col-12">
                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 h-100">
                            <span class="text-slate-400 d-block mb-0.5">Tipe Agent:</span>
                            <span class="text-slate-800 fw-bold fs-7">{{ optional($customer->type)->name ?? 'Reguler' }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6 col-12">
                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 h-100">
                            <span class="text-slate-400 d-block mb-0.5">Alamat:</span>
                            <span class="text-slate-800 fw-medium fs-7 text-truncate d-block" title="{{ $customer->address }}">{{ $customer->address ?: '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Rincian Paket Barang yang Dibeli --}}
        <div class="card border-0 shadow-sm rounded-2xl">
            <div class="card-header bg-transparent border-bottom border-slate-100 py-3 d-flex align-items-center justify-content-between">
                <h4 class="card-title text-xs fw-bold text-slate-700 text-uppercase tracking-wider d-flex align-items-center gap-1.5 m-0">
                    <x-icons.package class="w-4 h-4 text-blue-600" />
                    Paket Barang Agent
                </h4>
                <span class="text-xs text-slate-400">Otomatis Terhubung</span>
            </div>
            <div class="card-body p-4 space-y-3">
                @if($customer->product)
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-xl bg-slate-50 border border-slate-100 flex-wrap gap-2">
                        <div>
                            <span class="text-xs text-slate-400 fw-semibold text-uppercase d-block">Nama Paket / Barang:</span>
                            <span class="fw-bold text-slate-900 fs-6">{{ $customer->product->name }}</span>
                            <div class="d-flex align-items-center flex-wrap gap-2 mt-1">
                                <span class="text-xs text-slate-500 font-monospace">Kode: {{ $customer->product->code }}</span>
                                <span class="badge bg-purple-subtle text-purple-700 border border-purple-subtle rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                    Sisa Stok: <b>{{ number_format($customer->product->stock ?? 0, 0, ',', '.') }} Unit</b>
                                </span>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="text-xs text-slate-400 fw-semibold text-uppercase d-block">Kuota Limit:</span>
                            <span class="fw-bold text-blue-600 fs-4">{{ $customer->limit }} Unit</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between text-xs px-1 text-slate-600">
                        <span>Harga Satuan Paket:</span>
                        <span class="fw-bold text-slate-800 fs-7">Rp {{ number_format($customer->amount ?? 0, 0, ',', '.') }}</span>
                    </div>
                @else
                    <div class="p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-xs">
                        Pelanggan ini belum memiliki paket barang yang terhubung.
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- KOLOM KANAN: Tagihan & Pembayaran Kasir --}}
    <div class="col-lg-6 col-12 space-y-3">
        
        <div class="card border-0 shadow-sm rounded-2xl">
            <div class="card-header bg-transparent border-bottom border-slate-100 py-3 d-flex align-items-center justify-content-between">
                <h4 class="card-title text-xs fw-bold text-slate-700 text-uppercase tracking-wider d-flex align-items-center gap-1.5 m-0">
                    <x-icons.receipt class="w-4 h-4 text-blue-600" />
                    Pembayaran & Checkout Transaksi
                </h4>
                <span class="badge bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-pill fw-semibold fs-8">
                    Kasir Siap
                </span>
            </div>

            <div class="card-body p-4 space-y-4">
                {{-- Total Tagihan Box (Besar & Jelas) --}}
                <div class="p-3.5 rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-xs fw-bold text-blue-800 text-uppercase tracking-wider d-block">Total Pembayaran</span>
                        <span class="text-xs text-blue-600">{{ $customer->limit }} unit &times; Rp {{ number_format($customer->amount ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="text-end">
                        <span class="fs-1 fw-extrabold text-blue-700 font-monospace" id="display-total">
                            Rp {{ number_format($customer->total ?? 0, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- Input Uang Diterima --}}
                <div>
                    <label for="payment" class="text-xs fw-bold text-slate-700 text-uppercase tracking-wider d-block mb-1.5">
                        Uang Diterima (Rp) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-slate-50 text-slate-500 fw-bold border-slate-300 rounded-start-xl">Rp</span>
                        <input 
                            type="text" 
                            name="payment" 
                            id="payment" 
                            placeholder="0"
                            autocomplete="off"
                            autofocus
                            class="form-control form-control-lg fw-bold text-slate-800 border-slate-300 rounded-end-xl"
                            style="font-size: 1.25rem;"
                        />
                    </div>
                    <span class="text-xs text-danger mt-1 d-block invalid-feedback error_payment"></span>
                </div>

                {{-- Quick Cash Buttons --}}
                <div>
                    <div class="text-xs fw-semibold text-slate-400 text-uppercase tracking-wider mb-2">Tombol Cepat Uang Diterima:</div>
                    <div class="row g-2">
                        <div class="col-4 col-sm">
                            <button type="button" onclick="setExactPay()" class="btn btn-sm btn-primary w-100 rounded-lg fw-bold py-2 shadow-sm">
                                Uang Pas
                            </button>
                        </div>
                        <div class="col-4 col-sm">
                            <button type="button" onclick="setNominal(10000)" class="btn btn-sm btn-light border w-100 rounded-lg py-2 fw-semibold">
                                10.000
                            </button>
                        </div>
                        <div class="col-4 col-sm">
                            <button type="button" onclick="setNominal(20000)" class="btn btn-sm btn-light border w-100 rounded-lg py-2 fw-semibold">
                                20.000
                            </button>
                        </div>
                        <div class="col-6 col-sm">
                            <button type="button" onclick="setNominal(50000)" class="btn btn-sm btn-light border w-100 rounded-lg py-2 fw-semibold">
                                50.000
                            </button>
                        </div>
                        <div class="col-6 col-sm">
                            <button type="button" onclick="setNominal(100000)" class="btn btn-sm btn-light border w-100 rounded-lg py-2 fw-semibold">
                                100.000
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Kembalian Box --}}
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-xs fw-bold text-slate-500 text-uppercase tracking-wider d-block">Kembalian:</span>
                        <span class="text-xs text-slate-400">Sisa uang agent</span>
                    </div>
                    <div>
                        <input 
                            type="text" 
                            name="return" 
                            id="return" 
                            value="Rp 0" 
                            readonly 
                            class="bg-transparent text-end fw-extrabold text-slate-800 border-0 p-0 fs-3 w-100"
                        />
                    </div>
                </div>

                {{-- Hidden Form Values --}}
                <input type="hidden" name="customers_id" id="customers_id" value="{{ $customer->id }}">
                <input type="hidden" name="products_id" id="products_id" value="{{ optional($customer->product)->id }}">
                <input type="hidden" name="qty" id="qty" value="{{ $customer->limit }}">
                <input type="hidden" name="total" id="total" value="{{ $customer->total }}">

                {{-- Submit Button --}}
                <div class="pt-2">
                    <button 
                        id="btnSave" 
                        type="button" 
                        disabled
                        class="btn btn-primary btn-lg w-100 rounded-xl fw-bold py-3 d-flex align-items-center justify-center gap-2 shadow-sm"
                    >
                        <x-icons.check class="w-5 h-5" />
                        <span>Selesaikan Transaksi & Cetak Struk</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('js')
<script>
    const BASE = "{{ route('transaksi.index') }}";
    const TOTAL_RAW = {{ (float) ($customer->total ?? 0) }};

    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });

    function formatRupiah(angka) {
        if (!angka) return "0";
        
        let numberString = angka.toString().replace(/\D/g, ""),
            sisa = numberString.length % 3,
            rupiah = numberString.substr(0, sisa),
            ribuan = numberString.substr(sisa).match(/\d{3}/g);

        if (ribuan) {
            let separator = sisa ? "." : "";
            rupiah += separator + ribuan.join(".");
        }

        return rupiah;
    }

    // --- REALTIME STOCK MANAGER ---
    let currentBranchStock = {{ (int) ($sumProduct ?? 0) }};

    function updateBranchStockDisplay(newStock) {
        if (newStock === undefined || newStock === null) return;
        currentBranchStock = parseInt(newStock) || 0;
        let formatted = formatRupiah(currentBranchStock);

        $("#desktop-branch-stock-val").text(formatted);
        $("#mobile-branch-stock-val").text(formatted);

        $("#desktop-branch-stock-badge, #mobile-branch-stock-badge")
            .stop(true, true)
            .addClass('bg-emerald-100 border-emerald-400 text-emerald-900 shadow-sm')
            .removeClass('bg-blue-50 border-blue-200 text-blue-700 text-blue-800');

        setTimeout(() => {
            $("#desktop-branch-stock-badge, #mobile-branch-stock-badge")
                .removeClass('bg-emerald-100 border-emerald-400 text-emerald-900 shadow-sm')
                .addClass('bg-blue-50 border-blue-200 text-blue-700');
        }, 2200);
    }

    function syncBranchStockLive() {
        $.ajax({
            url: BASE + '/current-stock',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res && res.status && res.stock !== undefined) {
                    if (parseInt(res.stock) !== currentBranchStock) {
                        updateBranchStockDisplay(res.stock);
                    }
                }
            }
        });
    }

    window.addEventListener('focus', function() {
        syncBranchStockLive();
    });
    setInterval(syncBranchStockLive, 15000);

    function setExactPay() {
        if (TOTAL_RAW > 0) {
            $("#payment").val(formatRupiah(TOTAL_RAW));
            calculateReturn();
        }
    }

    function setNominal(amount) {
        $("#payment").val(formatRupiah(amount));
        calculateReturn();
    }

    function calculateReturn() {
        let paymentVal = $("#payment").val().replace(/\D/g, "");
        let paymentAmount = paymentVal ? parseInt(paymentVal) : 0;
        let totalAmount = TOTAL_RAW ? parseInt(TOTAL_RAW) : 0;

        if (totalAmount <= 0) {
            $("#return").val("Rp 0").removeClass("text-danger text-success");
            $("#btnSave").attr("disabled", "disabled");
            return;
        }

        let returnAmount = paymentAmount - totalAmount;

        if (paymentVal === "") {
            $("#return").val("Rp 0").removeClass("text-danger text-success");
            $("#btnSave").attr("disabled", "disabled");
        } else if (returnAmount < 0) {
            $("#return").val("- Rp " + formatRupiah(Math.abs(returnAmount)))
                .addClass("text-danger")
                .removeClass("text-success text-slate-800");
            $("#btnSave").attr("disabled", "disabled");
        } else {
            $("#return").val("Rp " + formatRupiah(returnAmount))
                .addClass("text-success")
                .removeClass("text-danger text-slate-800");
            $("#btnSave").removeAttr("disabled");
        }
    }

    $("#payment").on("keyup", function() {
        let val = $(this).val().replace(/\D/g, "");
        $(this).val(val ? formatRupiah(val) : "");
        calculateReturn();
    });

    // Auto calculate return on load if any
    calculateReturn();

    // Submit Transaksi
    $("#btnSave").click(function() {
        let customers_id = $("#customers_id").val();
        let products_id = $("#products_id").val();
        let qty = $("#qty").val();
        let total = $("#total").val();
        let payment = $("#payment").val();

        if (!customers_id || !payment) {
            Toast.fire({ icon: 'warning', title: 'Lengkapi data transaksi terlebih dahulu.' });
            return;
        }

        Swal.fire({
            title: "Konfirmasi Transaksi NFC",
            text: "Lanjutkan pemrosesan transaksi dan cetak struk?",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#2563EB",
            cancelButtonColor: "#64748B",
            confirmButtonText: "Ya, Proses & Cetak",
            cancelButtonText: "Batal",
        }).then((result) => {
            if (result.isConfirmed) {
                let prevStock = currentBranchStock;
                let deductQty = parseInt(qty) || 1;
                let optimisticStock = Math.max(0, currentBranchStock - deductQty);
                updateBranchStockDisplay(optimisticStock);

                $.ajax({
                    url: BASE + '/store',
                    method: "POST",
                    data: {
                        customers_id: customers_id,
                        products_id: products_id,
                        qty: qty,
                        total: total,
                        payment: payment
                    },
                    dataType: "json",
                }).done(function(response) {
                    if (response.code == 400 && response.errors) {
                        updateBranchStockDisplay(prevStock);
                        $.each(response.errors, function(index, value) {
                            $("#" + index).addClass('is-invalid');
                            $(".error_" + index).html(value);
                        });
                        Toast.fire({ icon: 'warning', title: 'Periksa kembali formulir input Anda.' });
                    } else if (response.code == 400 || response.code == 401 || response.status == 'warning') {
                        updateBranchStockDisplay(prevStock);
                        Swal.fire({
                            icon: 'warning',
                            title: 'Stok Cabang Tidak Mencukupi!',
                            text: response.message || 'Stok barang di cabang tidak mencukupi untuk memproses transaksi ini.',
                            confirmButtonColor: '#2563EB',
                            confirmButtonText: 'Tutup & Periksa Stok'
                        });
                    } else {
                        if (response.updated_branch_stock !== undefined && response.updated_branch_stock !== null) {
                            updateBranchStockDisplay(response.updated_branch_stock);
                        }

                        $("#payment").val("");
                        $("#return").val("Rp 0").removeClass("text-danger text-success");
                        $("#btnSave").attr("disabled", "disabled");

                        printReceipt(response.transaction.id);

                        Swal.fire({
                            icon: "success",
                            title: "Transaksi Berhasil!",
                            text: response.message || "Penjualan berhasil disimpan dan stok otomatis berkurang!",
                            showCancelButton: true,
                            confirmButtonText: "Transaksi Lainnya",
                            cancelButtonText: "Tutup",
                            confirmButtonColor: "#2563EB"
                        }).then((choice) => {
                            if (choice.isConfirmed) {
                                window.location.href = "{{ route('transaksi.create') }}";
                            }
                        });
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    updateBranchStockDisplay(prevStock);
                    console.log("Error:", textStatus, errorThrown);
                    Toast.fire({ icon: 'error', title: 'Terjadi kesalahan sistem.' });
                });
            }
        });
    });

    function printReceipt(transactionId) {
        let receiptUrl = BASE + "/" + transactionId + "/receipt";
        let popupWidth = 480;
        let popupHeight = 650;
        let left = (window.screen.width - popupWidth) / 2;
        let top = (window.screen.height - popupHeight) / 2;

        let printWindow = window.open(
            receiptUrl,
            "_blank",
            `width=${popupWidth},height=${popupHeight},top=${top},left=${left}`
        );

        if (printWindow) {
            printWindow.focus();
        } else {
            alert("Izinkan popup di browser Anda untuk mencetak struk kasir.");
        }
    }
</script>
@endpush
