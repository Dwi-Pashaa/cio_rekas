<table class="table card-table table-vcenter text-nowrap datatable">
    <thead>
        <tr>
            <th class="w-1">No</th>
            <th>No Serial</th>
            <th>Nama Pelanggan</th>
            <th>NIK</th>
            <th>No Telephone</th>
            <th>Email</th>
            <th>Alamat</th>
            <th>Barang</th>
            <th>Limit</th>
            <th>Type</th>
            <th>Status</th>
            <th>Created</th>
        </tr>
    </thead>
    <tbody>
       @forelse ($customers as $item)
           <tr>
                <td>
                    {{ $loop->iteration }}
                </td>
                <td>{{ $item->code }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->nik ?? '-' }}</td>
                <td>{{ $item->telp }}</td>
                <td>{{ $item->email ?? '-' }}</td>
                <td>{{ $item->address }}</td>
                <td>{{ optional($item->product)->code }} - {{ optional($item->product)->name }}</td>
                <td>{{ $item->limit }}</td>
                <td>{{ optional($item->type)->name ?? '-' }}</td>
                <td>{{ optional($item->status)->name ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i:s') }}</td>
           </tr>
       @empty
           <tr>
                <td colspan="12" class="text-center">Tidak Ada Data</td>
           </tr>
       @endforelse
    </tbody>
</table>