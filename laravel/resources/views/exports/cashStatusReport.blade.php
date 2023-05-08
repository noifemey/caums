<table>
    <thead>
        <tr><td colspan="6" style="text-align:center"> <strong> Department of Social Welfre and Development </strong> </td></tr>
        <tr><td colspan="6" style="text-align:center"> <strong> Cash Unit </strong> </td></tr>
        <tr><td colspan="6" style="text-align:center"> <strong> Status of Cash Allocations, Utilized and Balances </strong> </td></tr>
        <tr><td colspan="6" style="text-align:center"> <strong> Period Covered of Disbursements is from : {{date('F j, Y', strtotime($start_date))}} to {{date('F j, Y', strtotime($end_date))}} </strong> </td></tr>
        <tr>
            <td colspan="6" style="text-align:left"> <strong> Account Number: {{$account_number}} </strong> </td>
        </tr>
        <tr>
            <th>Allocation Number</th>
            <th>Reference</th>
            <th>Purpose</th>
            <th>Cash Received</th>
            <th>Utilization</th>
            <th>Balance</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $dt)
            <tr>
                <td><strong> {{ $dt['allocation_no'] }} </strong></td>
                <td>{{ $dt['reference'] }}</td>
                <td>{{ $dt['purpose'] }}</td>
                <td>{{ $dt['cash_received'] }}</td>
                <td>{{ $dt['total'] }}</td>
                <td>{{ $dt['balance'] }}</td>
            </tr>      
        @endforeach
        <tr><td colspan="6"></td></tr>
        <tr>
            <td colspan="2"> <strong> Total Cash Allocation Received from {{date('F j, Y', strtotime($start_date))}} to {{date('F j, Y', strtotime($end_date))}} </strong> </td>
            <td> <strong> ₱ {{ number_format($all_allocation,2) }} </strong> </td>
            <td></td>
            <td><strong> ₱ {{ number_format($utilization_total,2) }} </strong></td>
            <td> <strong> ₱ {{ number_format($all_balance,2) }} </strong> </td>
        </tr>
    </tbody>
</table>