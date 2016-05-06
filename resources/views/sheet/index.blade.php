{{-- sheet.show --}}

@extends('master')

@section('content')

@foreach($sheetData as $sheet)
	<h1>{{ $sheet->filename }}</h1>

	@foreach($sheet->clauses as $clause)

		<div>
		<a href="{{ $clause->link }}">{{ $clause->clause }}</a>
		</div>

		<div>
		{{ $clause->description === 'NA' ? '' : $clause->description }}
		</div>

		<div>
		<br />
		</div>

	@endforeach

@endforeach

@endsection