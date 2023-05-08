<table>
    <thead>
        <tr>
            <th>Check Number</th>
            <th>Date Issued</th>
            <th>Payee</th>
            <th>Specifications</th>
            <th>Object</th>
            <th>PPA</th>
            <th>Amount</th>
            <th>Allocation</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $dt)
            <tr>
                <td>{{ $dt['CheckNo'] }}</td>
                <td>{{ date('d-M-Y', strtotime($dt['DateIssued'])) }}</td>
                <td>{{ $dt['Payee'] }}</td>
                <td>{{ $dt['Specifications'] }}</td>
                <td>{{ $dt['Object'] }}</td>
                <td>{{ $dt['PPA'] }}</td>
                <td>{{ $dt['Amount'] }}</td>
                <td>{{ $dt['Allocation'] }}</td>
            </tr>      
        @endforeach
        <tr></tr>
        <tr>
            <td colspan="6"> <strong> Grand Total for {{$pap_code}} ({{$start_date}} - {{$end_date}})  </strong> </td>
            <td colspan="2"> <strong> {{$grand_total}}  </strong> </td>
        </tr>
    </tbody>
</table>