<style>
    table, th, td {
  border: 1px solid black;
}
</style>
<table>
    <thead>
	<tr>
	    @foreach($data[0] as $key => $value)
		<th>{{ ucfirst($key) }}</th>
	    @endforeach
    	</tr>
    </thead>
    <tbody>
    @foreach($data as $row)
    	<tr>
        @foreach ($row as $value)
    	    <td>{{ $value }}</td>
        @endforeach        
	</tr>
    @endforeach
    </tbody>
</table>