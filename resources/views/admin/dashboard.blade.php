@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
	<ul class="list-none ps-0">
		@foreach ($stats as $subject => $details)
			<li>
				<b>{{ ucfirst($subject) }}:</b>
				@foreach ($details as $name => $value)
					<span class="number">{{ $value }}</span> {{ $name }}{{ $loop->last ? '' : ',' }}
				@endforeach
			</li>
		@endforeach
	</ul>
@endsection
