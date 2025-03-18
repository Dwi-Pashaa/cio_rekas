<table>
    <thead>
        <tr>
            <th>No Serial</th>
            <th>Nama Penjual</th>
            <th>No Telephone</th>
            <th>Alamat</th>
            <th>Barang</th>
            <th>Jumlah</th>
            <th>Nominal</th>
            <th>Type</th>
            <th>Status</th>
            <th>Created</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($transaction as $item)
            <tr>
                <td>{{ $item->customer->code }}</td>
                <td>{{ $item->customer->name }}</td>
                <td>{{ $item->customer->telp }}</td>
                <td>{{ $item->customer->address }}</td>
                <td>{{ $item->product->code }} - {{ $item->product->name }}</td>
                <td>{{ $item->qty }}</td>
                <td>Rp. {{ number_format($item->total) }}</td>
                <td>{{ $item->customer->type }}</td>
                <td>{{ $item->customer->status }}</td>
                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i:s') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center">Tidak Ada Data</td>
            </tr>
        @endforelse
    </tbody>
</table>
