@if (session('error') || $errors->any())
	<div class="mb-4 bg-red-500 p-2 text-white">
		{{ session('error') }}
		@foreach ($errors->all() as $error)
			{{ $error }}
		@endforeach
	</div>
@endif

@if (session('warnings'))
	<div class="mb-4 bg-yellow-500 p-2 text-white">
		@foreach (session('warnings') as $warning)
			{{ $warning }}
		@endforeach
	</div>
@endif

@if (session('success'))
	<div class="mb-4 bg-green-500 p-2">
		{{ session('success') }}
	</div>
@endif
