@extends('layouts.app')

@section('title', 'Kasir POS')
@section('pretitle', 'Input Transaksi Baru')

@section('header-actions')
    @if(isset($sumProduct))
        <div id="desktop-branch-stock-badge" class="d-none d-sm-flex align-items-center gap-2 px-3 py-1.5 bg-blue-50 border border-blue-200 rounded-xl text-xs font-semibold text-blue-700" style="transition: all 0.3s ease;">
            <x-icons.package class="w-4 h-4 text-blue-600" />
            <span>Sisa Stok di {{ $userBranch ?? 'Cabang' }}: <b id="desktop-branch-stock-val" class="text-primary font-bold">{{ number_format($sumProduct, 0, ',', '.') }}</b> unit</span>
        </div>
    @endif
@endsection

@section('content')
<div class="row g-3">
    {{-- KOLOM KIRI: Cari Pelanggan & Identitas Pelanggan --}}
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

        {{-- 1. Scan / Input Serial Number --}}
        <div class="card border-0 shadow-sm rounded-2xl">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <label for="code" class="text-xs fw-bold text-slate-700 text-uppercase tracking-wider d-flex align-items-center gap-1.5 m-0">
                        <x-icons.barcode class="w-4 h-4 text-blue-600" />
                        Serial Number Agent
                    </label>
                    <span class="text-xs text-slate-400">Tekan Enter atau ketik SN</span>
                </div>

                <div class="position-relative">
                    <input 
                        type="text" 
                        name="code" 
                        id="code" 
                        autofocus
                        autocomplete="off"
                        placeholder="Ketik atau scan Serial Number agent..."
                        class="form-control form-control-lg rounded-xl text-slate-800 fw-bold border-slate-300"
                        style="font-size: 1rem;"
                    />
                </div>
                <div class="text-xs text-slate-400 mt-2">
                    Sistem otomatis mencari profil agent dan barang yang ditransaksikan.
                </div>
            </div>
        </div>

        {{-- 2. Detail Identitas Agent --}}
        <div class="card border-0 shadow-sm rounded-2xl">
            <div class="card-header bg-transparent border-bottom border-slate-100 py-3 d-flex align-items-center justify-content-between">
                <h4 class="card-title text-xs fw-bold text-slate-700 text-uppercase tracking-wider d-flex align-items-center gap-1.5 m-0">
                    <x-icons.users class="w-4 h-4 text-blue-600" />
                    Identitas Agent
                </h4>
                <div id="badge-customer-status"></div>
            </div>
            <div class="card-body p-4" id="append-customer">
                {{-- Diisi via JavaScript --}}
            </div>
        </div>

    </div>

    {{-- KOLOM KANAN: Ringkasan Tagihan & Pembayaran Kasir --}}
    <div class="col-lg-6 col-12 space-y-3">
        
        {{-- Unified Checkout Card --}}
        <div class="card border-0 shadow-sm rounded-2xl">
            {{-- Ringkasan Pembelian Header --}}
            <div class="card-header bg-transparent border-bottom border-slate-100 py-3 d-flex align-items-center justify-content-between">
                <h4 class="card-title text-xs fw-bold text-slate-700 text-uppercase tracking-wider d-flex align-items-center gap-1.5 m-0">
                    <x-icons.receipt class="w-4 h-4 text-blue-600" />
                    Rincian Pembelian
                </h4>
                <span class="text-xs text-slate-400">Kasir Aktif</span>
            </div>

            <div class="card-body p-4">
                {{-- Detail Item & Total Tagihan --}}
                <div id="append-transaksi" class="mb-4">
                    {{-- Diisi via JavaScript --}}
                </div>

                <hr class="my-4 border-slate-100">

                {{-- Form Pembayaran --}}
                <div class="space-y-3">
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
                                class="form-control form-control-lg fw-bold text-slate-800 border-slate-300 rounded-end-xl"
                                style="font-size: 1.15rem;"
                            />
                        </div>
                        <span class="text-xs text-danger mt-1 d-block invalid-feedback error_payment"></span>
                    </div>

                    {{-- Quick Cash Buttons --}}
                    <div>
                        <div class="text-xs fw-semibold text-slate-400 text-uppercase tracking-wider mb-2">Uang Cepat:</div>
                        <div class="row g-2">
                            <div class="col-4 col-sm">
                                <button type="button" onclick="setExactPay()" class="btn btn-sm btn-outline-primary w-100 rounded-lg fw-bold py-1.5">
                                    Uang Pas
                                </button>
                            </div>
                            <div class="col-4 col-sm">
                                <button type="button" onclick="setNominal(10000)" class="btn btn-sm btn-light border w-100 rounded-lg py-1.5">
                                    10.000
                                </button>
                            </div>
                            <div class="col-4 col-sm">
                                <button type="button" onclick="setNominal(20000)" class="btn btn-sm btn-light border w-100 rounded-lg py-1.5">
                                    20.000
                                </button>
                            </div>
                            <div class="col-6 col-sm">
                                <button type="button" onclick="setNominal(50000)" class="btn btn-sm btn-light border w-100 rounded-lg py-1.5">
                                    50.000
                                </button>
                            </div>
                            <div class="col-6 col-sm">
                                <button type="button" onclick="setNominal(100000)" class="btn btn-sm btn-light border w-100 rounded-lg py-1.5">
                                    100.000
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Kembalian Box --}}
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 d-flex align-items-center justify-content-between mt-3">
                        <div>
                            <span class="text-xs fw-bold text-slate-500 text-uppercase tracking-wider d-block">Kembalian:</span>
                            <span class="text-xs text-slate-400">Sisa uang pelanggan</span>
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
                    <input type="hidden" name="customers_id" id="customers_id">
                    <input type="hidden" name="products_id" id="products_id">
                    <input type="hidden" name="qty" id="qty">
                    <input type="hidden" name="total" id="total">

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
</div>
@endsection

@push('js')
<script>
    const BASE = "{{ route('transaksi.index') }}";

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

        // Update teks angka pada badge desktop dan mobile
        $("#desktop-branch-stock-val").text(formatted);
        $("#mobile-branch-stock-val").text(formatted);

        // Efek visual lembut hijau seketika (pulse)
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

    // Sinkronisasi otomatis saat user kembali ke tab browser kasir ini
    window.addEventListener('focus', function() {
        syncBranchStockLive();
    });
    // Polling background setiap 15 detik agar tetap sinkron
    setInterval(syncBranchStockLive, 15000);

    let typingTimer;
    let doneTypingInterval = 350;
    let currentTotalRaw = 0;

    appendCustomer(null, false);
    appendTransaction(null, false);
    $("#btnSave").attr("disabled", "disabled");

    // Lookup Pelanggan by Serial Number
    $("#code").on("keyup", function() {
        clearTimeout(typingTimer);

        typingTimer = setTimeout(function() {
            let code = $("#code").val().trim();

            if (code !== "") {
                $.ajax({
                    url: BASE + '/getCustomerBySerialNumber',
                    method: "POST",
                    data: { code: code },
                    dataType: "json",
                }).done(function(response) {
                    if (response.status == false) {
                        Toast.fire({
                            icon: 'warning',
                            title: response.message
                        });
                        appendCustomer(null, false);
                        appendTransaction(null, false);
                        resetAmounts();
                    } else {
                        appendCustomer(response.data, true);
                        appendTransaction(response.data, true);
                        
                        currentTotalRaw = response.data.total;
                        $("#total").val(formatRupiah(response.data.total));
                        $("#customers_id").val(response.data.id);
                        $("#products_id").val(response.data.product.id);
                        $("#qty").val(response.data.limit);

                        calculateReturn();
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.log("Error:", textStatus, errorThrown);
                });
            } else {
                appendCustomer(null, false);
                appendTransaction(null, false);
                resetAmounts();
            }
        }, doneTypingInterval);
    });

    function resetAmounts() {
        currentTotalRaw = 0;
        $("#total").val("");
        $("#customers_id").val("");
        $("#products_id").val("");
        $("#qty").val("");
        $("#payment").val("");
        $("#return").val("Rp 0").removeClass("text-danger text-success");
        $("#btnSave").attr("disabled", "disabled");
    }

    function setExactPay() {
        if (currentTotalRaw > 0) {
            $("#payment").val(formatRupiah(currentTotalRaw));
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
        let totalAmount = currentTotalRaw ? parseInt(currentTotalRaw) : 0;

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

    // Render Kartu Pelanggan
    function appendCustomer(data, status) {
        let html = ``;

        if (!status || !data) {
            html = `
                <div class="py-4 text-center text-slate-400">
                    <x-icons.users class="w-8 h-8 mx-auto mb-1.5 text-slate-300" />
                    <p class="text-sm fw-medium m-0">Belum ada agent dipilih</p>
                    <p class="text-xs text-slate-400 m-0">Scan kode atau ketik serial number agent di atas</p>
                </div>
            `;
            $("#badge-customer-status").html('');
        } else {
            let statusText = (typeof data.status === 'object' && data.status !== null) 
                ? (data.status.name || 'Aktif') 
                : (data.status_name || data.status || 'Aktif');

            let typeText = (typeof data.type === 'object' && data.type !== null) 
                ? (data.type.name || 'Reguler') 
                : (data.type_name || data.type || 'Reguler');

            $("#badge-customer-status").html(`
                <span class="badge bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-pill px-2.5">
                    ${statusText}
                </span>
            `);

            html = `
                <div class="space-y-3">
                    <div class="d-flex align-items-center gap-3 p-3 bg-blue-50 border border-blue-100 rounded-xl">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white fw-bold d-flex align-items-center justify-center shrink-0">
                            ${data.name.substring(0, 2).toUpperCase()}
                        </div>
                        <div class="overflow-hidden">
                            <h4 class="fw-bold text-slate-900 m-0 text-truncate">${data.name}</h4>
                            <div class="text-xs text-blue-700 fw-medium">SN: ${data.code}</div>
                        </div>
                    </div>

                    <div class="row g-2 text-xs">
                        <div class="col-6">
                            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                                <span class="text-slate-400 d-block">No. Telepon:</span>
                                <span class="text-slate-800 fw-bold">${data.telp || '-'}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                                <span class="text-slate-400 d-block">Tipe Agent:</span>
                                <span class="text-slate-800 fw-bold">${typeText}</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 text-xs">
                        <span class="text-slate-400 d-block">Alamat:</span>
                        <span class="text-slate-800 fw-medium">${data.address || '-'}</span>
                    </div>
                </div>
            `;
        }
        $("#append-customer").html(html);
    }

    // Render Kartu Transaksi
    function appendTransaction(data, status) {
        let html = ``;

        if (!status || !data) {
            html = `
                <div class="py-4 text-center text-slate-400">
                    <x-icons.receipt class="w-8 h-8 mx-auto mb-1.5 text-slate-300" />
                    <p class="text-sm fw-medium m-0">Belum ada item transaksi</p>
                    <p class="text-xs text-slate-400 m-0">Item akan muncul otomatis setelah agent dipilih</p>
                </div>
            `;
        } else {
            let productStock = data.product.stock !== undefined ? formatRupiah(data.product.stock) : '-';
            html = `
                <div class="space-y-3">
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div>
                            <span class="text-xs text-slate-400 fw-semibold text-uppercase d-block">Barang / Paket:</span>
                            <span class="fw-bold text-slate-900">${data.product.name}</span>
                            <div class="d-flex align-items-center flex-wrap gap-2 mt-1">
                                <span class="text-xs text-slate-500 font-monospace">Kode: ${data.product.code}</span>
                                <span class="badge bg-purple-subtle text-purple-700 border border-purple-subtle rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                    Sisa Stok: <b>${productStock} Unit</b>
                                </span>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="text-xs text-slate-400 fw-semibold text-uppercase d-block">Jumlah:</span>
                            <span class="fw-bold text-blue-600 fs-5">${data.limit} Unit</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between text-xs px-1 text-slate-600">
                        <span>Harga Satuan:</span>
                        <span class="fw-bold text-slate-800">Rp ${formatRupiah(data.amount)}</span>
                    </div>

                    <div class="pt-3 border-top border-slate-200 d-flex align-items-center justify-content-between">
                        <span class="text-xs fw-bold text-slate-700 text-uppercase tracking-wider">Total Tagihan:</span>
                        <span class="fs-2 fw-extrabold text-blue-700">Rp ${formatRupiah(data.total)}</span>
                    </div>
                </div>
            `;
        }
        $("#append-transaksi").html(html);
    }

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
            title: "Konfirmasi Transaksi",
            text: "Lanjutkan pemrosesan transaksi dan cetak struk?",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#2563EB",
            cancelButtonColor: "#64748B",
            confirmButtonText: "Ya, Proses & Cetak",
            cancelButtonText: "Batal",
        }).then((result) => {
            if (result.isConfirmed) {
                // OPTIMISTIC UPDATE: Langsung kurangi sisa stok di layar saat itu juga tanpa menunggu network!
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
                    if (response.code == 400) {
                        // Rollback stok jika validasi gagal
                        updateBranchStockDisplay(prevStock);

                        $.each(response.errors, function(index, value) {
                            $("#" + index).addClass('is-invalid');
                            $(".error_" + index).html(value);
                        });
                    } else if (response.code == 401 || response.status == 'warning') {
                        // Rollback stok jika proses gagal
                        updateBranchStockDisplay(prevStock);

                        Toast.fire({
                            icon: 'warning',
                            title: response.message || 'Gagal memproses transaksi.'
                        });
                    } else {
                        // Sinkronkan sisa stok pasti dari database yang dikembalikan server
                        if (response.updated_branch_stock !== undefined && response.updated_branch_stock !== null) {
                            updateBranchStockDisplay(response.updated_branch_stock);
                        }

                        appendCustomer(null, false);
                        appendTransaction(null, false);
                        resetAmounts();
                        $("#code").val("");

                        printReceipt(response.transaction.id);

                        Toast.fire({
                            icon: "success",
                            title: response.message || "Penjualan berhasil disimpan dan stok otomatis berkurang!"
                        });
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    // Rollback stok jika request gagal
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