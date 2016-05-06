{{-- sheet.show --}}

@extends('master')

@section('content')
	<div>
		@if(!empty($clauseData->link))
			<a href="{{ $clauseData->link }}">{{ $clauseData->clause }}</a>
		@else
			{{ $clauseData->clause }}
		@endif
	</div>

	<div>
	{{ $clauseData->description or '' }}
	</div>

	<div>
	{{ $clauseData->sections->filename }}
</div>

@endsection
