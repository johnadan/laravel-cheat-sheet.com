{{-- sheet.index --}}

@extends('master')

@section('content')

@foreach($sheetData as $sheet)
	<h3>{{ $sheet->filename }}</h3>

	@foreach($sheet->clauses as $clause)

		<div>
		{{-- @parent display incorrectly --}}
		<a href="{{ $clause->link }}">@if ($clause->clause !== '@parent')
			{{ $clause->clause }}
		@else
			{{ '&#64;parent' }}	 
		@endif</a>
		</div>

		<div class="desc">
		{!! $clause->description === 'NA' ? '' : $clause->description !!}
		</div>

	@endforeach

@endforeach

@endsection