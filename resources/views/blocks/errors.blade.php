@if (session('error') || $errors->any())
	<div class="bg-red-700 p-2 text-white">
		{{ session('error') }}
		@foreach ($errors->all() as $error)
			{{ $error }}
		@endforeach
	</div>
@endif

@if (session('warnings'))
	<div class="bg-yellow-700 p-2 text-white">
		@foreach (session('warnings') as $warning)
			<p class="my-0">{{ $warning }}</p>
		@endforeach
	</div>
@endif

@if (session('success'))
	<div class="bg-green-700 p-2">
		{{ session('success') }}
	</div>
@endif
