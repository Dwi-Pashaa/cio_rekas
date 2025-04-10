@extends('layouts.app')

@section('title')
    Buat Transaksi
@endsection

@push('css')
    
@endpush

@section('content')
<div class="container d-flex justify-content-center align-items-center">
    <div class="row w-100">
        {{-- search customer by serial number --}}
        <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
            <div class="card mb-3">
                <div class="card-header">
                    <b>Cari Pelanggan</b>
                </div>
                <div class="card-body">
                    <div id="reader" style="width: 100%"></div>
                    <div class="form-group mt-3">
                        <label for="" class="mb-2">Pilih Kamera</label>
                        <select class="form-control" id="cameraSelect"></select>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <b>Detail Pelanggan</b>
                </div>
                <div class="card-body" id="append-customer">
                    
                </div>
            </div>
        </div>
        {{-- transaction customer --}}
        <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
            <div class="card mb-3">
                <div class="card-header">
                    <b>Detail Transaksi</b>
                </div>
                <div class="card-body" id="append-transaksi">
                    
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label for="" class="mb-2">Pembayaran</label>
                        <input type="text" name="payment" id="payment" class="form-control">
                        <span class="invalid-feedback error_payment"></span>
                    </div>
                    <div class="form-group mb-3">
                        <label for="" class="mb-2">Kembalian</label>
                        <input type="text" name="return" id="return" class="form-control" disabled>
                        <span class="invalid-feedback error_return"></span>
                    </div>

                    <input type="hidden" name="customers_id" id="customers_id">
                    <input type="hidden" name="products_id" id="products_id">
                    <input type="hidden" name="qty" id="qty">
                    <input type="hidden" name="total" id="total">
                    <button id="btnSave" type="button" class="btn btn-primary float-end">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
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
            if (!angka) return "";
            
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

        let typingTimer;
        let doneTypingInterval = 500;
        
        appendCustomer( data = null, status = false);
        appendTransaction( data = null, status = false);
        $("#btnSave").attr("disabled", "disabled");

        // $("#code").on("keyup", function() {
        //     clearTimeout(typingTimer);

        //     typingTimer = setTimeout(function() {
        //         let code = $("#code").val().trim();

        //         if (code !== "") {
        //             $.ajax({
        //                 url: BASE + '/getCustomerBySerialNumber',
        //                 method: "POST",
        //                 data: { code: code },
        //                 dataType: "json",
        //             }).done(function(response) {
        //                 if (response.status == false) {
        //                     Toast.fire({
        //                         icon: 'warning',
        //                         title: response.message
        //                     });
        //                     appendCustomer( data = null, status = false);
        //                     appendTransaction( data = null, status = false);
        //                     $("#btnSave").attr("disabled", "disabled");
        //                 } else {
        //                     appendCustomer(data = response.data, status = true);
        //                     appendTransaction( data = response.data, status = true);
        //                     // $("#btnSave").removeAttr("disabled", "disabled");
        //                     $("#total").val(formatRupiah(response.data.total))
        //                     $("#customers_id").val(response.data.id);
        //                     $("#products_id").val(response.data.product.id);
        //                     $("#qty").val(response.data.limit);
        //                 }
        //             }).fail(function(jqXHR, textStatus, errorThrown) {
        //                 console.log("Error:", textStatus, errorThrown);
        //             });
        //         } else {
        //             appendCustomer( data = null, status = false);
        //             appendTransaction( data = null, status = false);
        //             $("#btnSave").attr("disabled", "disabled");
        //         }
        //     }, doneTypingInterval);
        // });

        let qrScanner;
        let currentCameraId = null;

        function startQRScanner(cameraId) {
            const qrCodeRegionId = "reader";

            const onScanSuccess = (decodedText) => {
                $.ajax({
                    url: BASE + '/getCustomerBySerialNumber',
                    method: "POST",
                    data: { code: decodedText },
                    dataType: "json",
                }).done(function(response) {
                    if (response.status == false) {
                        Toast.fire({
                            icon: 'warning',
                            title: response.message
                        });
                        appendCustomer( data = null, status = false);
                        appendTransaction( data = null, status = false);
                        $("#btnSave").attr("disabled", "disabled");
                    } else {
                        appendCustomer(data = response.data, status = true);
                        appendTransaction( data = response.data, status = true);
                        // $("#btnSave").removeAttr("disabled", "disabled");
                        $("#total").val(formatRupiah(response.data.total))
                        $("#customers_id").val(response.data.id);
                        $("#products_id").val(response.data.product.id);
                        $("#qty").val(response.data.limit);
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.log("Error:", textStatus, errorThrown);
                });

                qrScanner.stop().then(() => {
                    document.getElementById(qrCodeRegionId).innerHTML = "";
                });
            };

            if (qrScanner) {
                qrScanner.stop().then(() => {
                    document.getElementById(qrCodeRegionId).innerHTML = "";
                    qrScanner = new Html5Qrcode(qrCodeRegionId);
                    qrScanner.start(cameraId, { fps: 10, qrbox: 300 }, onScanSuccess);
                });
            } else {
                qrScanner = new Html5Qrcode(qrCodeRegionId);
                qrScanner.start(cameraId, { fps: 10, qrbox: 300 }, onScanSuccess);
            }
        }

        function loadCameras() {
            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length) {
                    const select = document.getElementById("cameraSelect");
                    select.innerHTML = "";
                    devices.forEach(device => {
                        const option = document.createElement("option");
                        option.value = device.id;
                        option.text = device.label || `Camera ${select.length + 1}`;
                        select.appendChild(option);
                    });

                    currentCameraId = devices[0].id;
                    startQRScanner(currentCameraId);

                    select.addEventListener("change", function () {
                        currentCameraId = this.value;
                        startQRScanner(currentCameraId);
                    });
                }
            }).catch(err => {
                console.error("Camera access error: ", err);
            });
        }

        window.addEventListener("load", loadCameras);

        function appendCustomer(data, status) {
            let html = ``;

            if (status == false) {
                html += `<table class="table">
                            <tr>
                                <td class="text-center border-0">Tidak Ada Pelanggan</td>
                            </tr>
                        </table>`;
            } else {
                html += `<table class="table table-bordered">
                            <tr>
                                <th style="width: 30%">Nama</th>
                                <th class="text-center" style="width: 5%">:</th>
                                <th>${data.name}</th>
                            </tr>
                            <tr>
                                <th style="width: 30%">No Telephone</th>
                                <th class="text-center" style="width: 5%">:</th>
                                <th>${data.telp}</th>
                            </tr>
                            <tr>
                                <th style="width: 30%">Alamat</th>
                                <th class="text-center" style="width: 5%">:</th>
                                <th>${data.address}</th>
                            </tr>
                            <tr>
                                <th style="width: 30%">Limit</th>
                                <th class="text-center" style="width: 5%">:</th>
                                <th>${data.limit}</th>
                            </tr>
                            <tr>
                                <th style="width: 30%">Type</th>
                                <th class="text-center" style="width: 5%">:</th>
                                <th>${data.type}</th>
                            </tr>
                            <tr>
                                <th style="width: 30%">Status</th>
                                <th class="text-center" style="width: 5%">:</th>
                                <th>${data.status}</th>
                            </tr>
                        </table>`;
            }
            $("#append-customer").html(html);
        }

        function appendTransaction(data, status) {
            let html = ``;

            if (status == false) {
                html += `<table class="table">
                            <tr>
                                <td class="text-center border-0">Tidak Ada Transaksi</td>
                            </tr>
                        </table>`;
            } else {
                html += `<div class="d-flex justify-content-between">
                            <div>
                                <h4>Barang</h4>
                            </div>
                            <div>
                                <h4>${data.product.code} - ${data.product.name}</h4>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>Jumlah</h4>
                            </div>
                            <div>
                                <h4>${data.limit}</h4>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>Harga</h4>
                            </div>
                            <div>
                                <h4>${formatRupiah(data.amount)}</h4>
                            </div>
                        </div>
                        <hr class="divide mt-0 mb-3">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3>Total</h3>
                            </div>
                            <div>
                                <h3>Rp. ${formatRupiah(data.total)}</h3>
                            </div>
                        </div>`;
            }
            $("#append-transaksi").html(html);
            // $("#btnSave").attr("disabled", "disabled");
        }

        $("#payment").keyup(function() {
            clearTimeout(typingTimer); 

            let inputElement = $(this);

            typingTimer = setTimeout(function() { 
                let value = inputElement.val().replace(/\D/g, "");
                let total = $("#total").val().replace(/\D/g, "");

                let paymentAmount = value ? parseInt(value) : 0;
                let totalAmount = total ? parseInt(total) : 0;

                let returnAmount = paymentAmount - totalAmount;

                if (value === "") {
                    $("#return").val("0");
                } else {
                    $("#return").val(returnAmount <= 0 ? "- " + formatRupiah(Math.abs(returnAmount)) : formatRupiah(returnAmount));
                }

                $("#payment").val(formatRupiah(paymentAmount));

                if (paymentAmount < totalAmount) {
                    $("#return").addClass("text-danger");
                    if (!$("#payment").data("toast-shown")) {
                        Toast.fire({
                            icon: 'warning',
                            title: 'Jumlah uang pembayaran kurang dari total pembayaran.'
                        });
                        $("#payment").data("toast-shown", true);
                    }
                    $("#btnSave").attr("disabled", "disabled");
                } else {
                    $("#return").removeClass("text-danger");
                    $("#btnSave").removeAttr("disabled", "disabled");
                    $("#payment").removeData("toast-shown");
                }
            }, doneTypingInterval);
        });

        $("#btnSave").click(function() {
            let customers_id = $("#customers_id").val();
            let products_id = $("#products_id").val();
            let qty = $("#qty").val();
            let total = $("#total").val();
            let payment = $("#payment").val();

            Swal.fire({
                title: "Info !",
                text: "Anda yakin ingin melanjutkan penjualan?",
                icon: "info",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Lanjut",
                cancelButtonText: "Tidak",
            }).then((result) => {
                if (result.isConfirmed) {
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
                            $.each(response.errors, function(index, value) {
                                $("#" + index).addClass('is-invalid');
                                $(".error_" + index).html(value)
                            })
                        } else if(response.code == 401) {
                            Toast.fire({
                                icon: 'warning',
                                title: response.message
                            });
                        } else {
                            appendCustomer( data = null, status = false);
                            appendTransaction( data = null, status = false);
                            $("#btnSave").attr("disabled", "disabled");

                            $("#code").val("");
                            $("#payment").val("");
                            $("#return").val("");

                            printReceipt(response.transaction.id);

                            Toast.fire({
                                icon: "success",
                                title: "Berhasil Melakukan Penjualan."
                            });
                        }

                    }).fail(function(jqXHR, textStatus, errorThrown) {
                        console.log("Error:", textStatus, errorThrown);
                    });
                }
            });
        })

        function printReceipt(transactionId) {
            let receiptUrl = BASE + "/" + transactionId + "/receipt";

            let screenWidth = window.screen.width;
            let screenHeight = window.screen.height;

            let popupWidth = 1000;
            let popupHeight = 600;

            let left = (screenWidth - popupWidth) / 2;
            let top = (screenHeight - popupHeight) / 2;

            let printWindow = window.open(
                receiptUrl,
                "_blank",
                `width=${popupWidth},height=${popupHeight},top=${top},left=${left}`
            );

            if (printWindow) {
                printWindow.focus();
            } else {
                alert("Izinkan popup di browser untuk mencetak struk.");
            }
        }
    </script>
@endpush