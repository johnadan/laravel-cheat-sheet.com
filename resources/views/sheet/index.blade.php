{{-- sheet.show --}}


@foreach($sheetData as $sheet)
	{{ $sheet->filename }}

	@foreach($sheet->clauses as $clause)

		<div>
		{{ $clause->clause }}
		</div>

		<div>
		{{ $clause->description }}
		</div>

		<div>
		{{ $clause->link }}
		</div>

		<div>
		{{ $clause->section }}
		</div>

	@endforeach

@endforeach


