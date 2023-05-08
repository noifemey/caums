<table>
    <thead>
        <tr><td colspan="15" style="text-align:center"> <strong> Department of Social Welfre and Development </strong> </td></tr>
        <tr><td colspan="15" style="text-align:center"> <strong> Cash Unit </strong> </td></tr>
        <tr><td colspan="15" style="text-align:center"> <strong> Statement of Utilization as per Program/Activity/Project for Fiscal Year {{date('Y', strtotime($end_date))}} </strong> </td></tr>
        <tr><td colspan="15" style="text-align:center"> <strong> As Of: {{date('F j, Y', strtotime($end_date))}} </strong> </td></tr>
        <tr>
            <td colspan="15" style="text-align:left"> <strong> Account Number: {{$account_number}} </strong> </td>
        </tr>
        <tr>
            <th colspan = "2">Particulars</th>
            <th>January</th>
            <th>February</th>
            <th>March</th>
            <th>April</th>
            <th>May</th>
            <th>June</th>
            <th>July</th>
            <th>August</th>
            <th>September</th>
            <th>October</th>
            <th>November</th>
            <th>December</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $dt)
            <tr>
                <td>'{{ $dt['pap'] }}</td>
                <td>{{ $dt['pap_title'] }}</td>
                <td>{{ $dt['January'] }}</td>
                <td>{{ $dt['February'] }}</td>
                <td>{{ $dt['March'] }}</td>
                <td>{{ $dt['April'] }}</td>
                <td>{{ $dt['May'] }}</td>
                <td>{{ $dt['June'] }}</td>
                <td>{{ $dt['July'] }}</td>
                <td>{{ $dt['August'] }}</td>
                <td>{{ $dt['September'] }}</td>
                <td>{{ $dt['October'] }}</td>
                <td>{{ $dt['November'] }}</td>
                <td>{{ $dt['December'] }}</td>
                <td>{{ $dt['Total'] }}</td>
            </tr>      
        @endforeach
        <tr>
            <td colspan="2"> <strong> Grand Total for {{$start_date}} - {{$end_date}} </strong> </td>
            <td> <strong> ₱ {{ number_format($all_total['January'],2) }}  </strong> </td>
            <td> <strong> ₱ {{ number_format($all_total['February'],2) }}  </strong> </td>
            <td> <strong> ₱ {{ number_format($all_total['March'],2) }}  </strong> </td>
            <td> <strong> ₱ {{ number_format($all_total['April'],2) }}  </strong> </td>
            <td> <strong> ₱ {{ number_format($all_total['May'],2) }}  </strong> </td>
            <td> <strong> ₱ {{ number_format($all_total['June'],2) }}  </strong> </td>
            <td> <strong> ₱ {{ number_format($all_total['July'],2) }}  </strong> </td>
            <td> <strong> ₱ {{ number_format($all_total['August'],2) }}  </strong> </td>
            <td> <strong> ₱ {{ number_format($all_total['September'],2) }}  </strong> </td>
            <td> <strong> ₱ {{ number_format($all_total['October'],2) }}  </strong> </td>
            <td> <strong> ₱ {{ number_format($all_total['November'],2) }}  </strong> </td>
            <td> <strong> ₱ {{ number_format($all_total['December'],2) }}  </strong> </td>
            <td> <strong> ₱ {{ number_format($all_total['Total'],2) }}  </strong> </td>
        </tr>
    </tbody>
</table>