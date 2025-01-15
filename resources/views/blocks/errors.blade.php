@if (Session::has('error') || $errors->any())
	<div class="bg-red-700 p-2 text-white">
		{{ Session::get('error') }}
		@foreach ($errors->all() as $error)
			{{ $error }}
		@endforeach
	</div>
@endif

@if (Session::has('warnings') && count(Session::get('warnings')) > 0)
	<div class="bg-yellow-700 p-2 text-white">
		@foreach (Session::get('warnings') as $warning)
			<p class="my-0">{{ $warning }}</p>
		@endforeach
	</div>
@endif

@if (Session::has('success'))
	<div class="bg-green-700 p-2">
		{{ Session::get('success') }}
	</div>
@endif

@php
	Session::forget('warnings');
	Session::forget('success');
@endphp
