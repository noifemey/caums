<table>
    <thead>
        
        <tr><td colspan="7" style="text-align:center"> <strong> Department of Social Welfre and Development </strong> </td></tr>
        <tr><td colspan="7" style="text-align:center"> <strong> Cash Unit </strong> </td></tr>
        <tr><td colspan="7" style="text-align:center"> <strong> Report of Check Issued </strong> </td></tr>
        <tr><td colspan="7" style="text-align:center"> <strong> Date of Issuance : {{date('F j, Y', strtotime($start_date))}} to {{date('F j, Y', strtotime($end_date))}} </strong> </td></tr>
        <tr>
            <td colspan="7" style="text-align:left"> <strong> Account Number: {{$account_number}} </strong> </td>
        </tr>
        <tr>
            <th>Check Date</th>
            <th>Check Number</th>
            <th>DV No./Payroll</th>
            <th>Responsibility Center Code</th>
            <th>Payee</th>
            <th>Nature of Payment</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $dt)
            <tr>
                <td>{{ date('d-M-Y', strtotime($dt['DateIssued'])) }}</td>
                <td>{{ $dt['CheckNo'] }}</td>
                <td>{{ $dt['VoucherNo'] }}</td>
                <td></td>
                <td>{{ $dt['Payee'] }}</td>
                <td>{{ $dt['Purpose'] }}</td>
                <td>{{ $dt['CkAmount'] }}</td>
            </tr>      
        @endforeach
        <tr></tr>
        <tr>
            <td colspan="5" style="text-align:center"> <strong> Grand Total for {{date('F j, Y', strtotime($start_date))}} to {{date('F j, Y', strtotime($end_date))}} </strong> </td>
            <td colspan="2" style="text-align:right"> <strong> ₱ {{ number_format($grand_total,2) }}  </strong> </td>
        </tr>
    </tbody>
</table>