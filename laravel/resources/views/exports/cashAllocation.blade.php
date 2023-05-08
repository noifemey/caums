<table>
    <thead style="border: 1px solid #050505;">
    
        <tr><td colspan="6" style="text-align:center"> <strong> Department of Social Welfare and Development </strong> </td></tr>
        <tr><td colspan="6" style="text-align:center"> <strong> Cash Unit </strong> </td></tr>
        <tr><td colspan="6" style="text-align:center"> <strong> Summary List of N.C.A and N.T.A Received</strong> </td></tr>
        <tr><td colspan="6" style="text-align:center"> <strong> for Fiscal Year : {{ $year }}</strong> </td></tr>
        <tr>
            <td colspan="3" style="text-align:left"> <strong> Account Number: {{ $account_number }} </strong> </td>
            <td colspan="3" style="text-align:right"> <strong> Account Number: {{ $account_name }} </strong> </td>
        </tr>
        <tr>
            <th>Calendar Year</th>
            <th>Allocation Number</th>
            <th>Date of Issuance</th>
            <th>Reference</th>
            <th>Purpose of Payment</th>
            <th>Allocation Received</th>
        </tr>
    </thead>
    <tbody style="border: 1px solid #050505;">
    @foreach($data as $dt)
        @foreach($dt['data'] as $child)
            <tr style="border: 1px solid #050505;">
                @if ($loop->index == 0)
                    <td><strong> {{ $dt['month_name'] }} </strong></td>
                @else
                    <td></td>
                @endif
                <td>{{ $child['AllocationNo'] }}</td>
                <td>{{ date('d-M-Y', strtotime($child['Date'])) }}</td>
                <td>'{{ $child['Reference'] }}</td>
                <td>{{ $child['Purpose'] }}</td>
                <td>{{ $child['CAReceived'] }}</td>
            </tr>
        @endforeach        
        <tr>

            <td colspan="5"> <strong> 
                @if ($dt['month_name'] != 'Grand Total') Summary for the month of @endif {{ $dt['month_name'] }} </strong> </td>
            <td> <strong> {{ $dt['received_summary'] }} </strong> </td>
        </tr>
    @endforeach
    </tbody>
</table>