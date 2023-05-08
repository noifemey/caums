<table>
    <thead>
        <tr>
            <td colspan="11"><strong>CASH DISBURSEMENT RECORD</strong></td>
        </tr>
        <tr>
            <th>Date Issued</th>
            <th>Check Number</th>
            <th>Voucher Number</th>
            <th>Obligation Number</th>
            <th>Allocation Number</th>
            <th>Reference</th>
            <th>Description</th>
            <th>Object</th>
            <th>Payee</th>
            <th>Purpose</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $dt)
            @foreach($dt['children'] as $child)
                <tr style="border: 1px solid #050505;">
                    <td>{{ date('d-M-Y', strtotime($dt['dateIssued'])) }}</td>
                    <td>{{ $child['checkNo'] }}</td>
                    <td>{{ $child['voucherNo'] }}</td>
                    <td>{{ $child['obligationNo'] }}</td>
                    <td>{{ $child['allocationNo'] }}</td>
                    <td>`{{ $child['reference'] }}</td>
                    <td>{{ $child['description'] }}</td>
                    <td>{{ $child['object'] }}</td>
                    <td>{{ $child['payee'] }}</td>
                    <td>{{ $child['purpose'] }}</td>
                    <td>{{ $child['amount'] }}</td>
                </tr>
            @endforeach        
            <tr>
                <td colspan="8" style="text-align:center" ><strong> {{ date('l M d, Y', strtotime($dt['dateIssued'])) }} </strong></td>
                <td colspan="2"> <strong>SUM</strong> </td>
                <td style="text-align:right"> <strong> {{ number_format($dt['total'],2) }} </strong> </td>
            </tr>
            <tr><td colspan="11"></td></tr>
        @endforeach
        <tr><td colspan="11"></td></tr>
        <tr>
            <td colspan="9"> <strong> Grand Total for {{$start_date}} - {{$end_date}} </strong> </td>
            <td colspan="2" style="text-align:right" > <strong> ₱ {{ number_format($grand_total,2) }}  </strong> </td>
        </tr>
    </tbody>
</table>