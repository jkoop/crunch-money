<form action="{{ route('set-period') }}" method="post">
	@csrf
	<select name="period_id" onchange="this.form.submit()">
		@foreach ($periods as $period)
			<option value="{{ $period->id }}" {{ $period->id == $currentPeriod->id ? 'selected' : '' }}>
				{{ $period->start->format(true) }} - {{ $period->end->format(true) }}</option>
		@endforeach
	</select>
</form>
