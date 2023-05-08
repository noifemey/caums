<!DOCTYPE html>
<html>
<head>
    <title>Allocations</title>
    <link href='{{public_path()."/css/app.css"}}' rel="stylesheet">
</head>
<body>
  <style>
    #header,
    #footer {
      position: fixed;
      left: 0;
      right: 0;
      color: #aaa;
      font-size: 0.9em;
    }
    #header {
      top: 0;
      border-bottom: 0.1pt solid #aaa;
    }
    #footer {
      bottom: 0;
      border-top: 0.1pt solid #aaa;
    }
    .page-number:before {
      content: "Page " counter(page);
    }
  </style>
  <div id="footer">
    <div class="page-number"></div>
  </div>

  <div class = "header">
      <h1>{{ $heading}}</h1>
  </div>
  <div>
  <table class="table">
    <thead class="thead-default">
      <tr>
        <th>#</th>
        <th>Allocation Number</th>
        <th>Period Coverage</th>
        <th>Account Number</th>
        <th>Date Issued</th>
        <th>Reference</th>
        <th>Purpose</th>
        <th>Amount Issued</th>
        <th>Amount Received</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($allocations as $allocation)
        <tr>
          <th>{{$loop->iteration}}</th>
          <td>{{$allocation->AllocationNo}}</td>
          <td>{{$allocation->MonthYear}}</td>
          <td>{{$allocation->AccountNo}}</td>
          <td>{{$allocation->Date}}</td>
          <td>{{$allocation->Reference}}</td>
          <td>{{$allocation->Purpose}}</td>
          <td>{{$allocation->CAIssued}}</td>
          <td>{{$allocation->CAReceived}}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
  </div>
  <script src="/js/app.js"></script>
</body>
</html>