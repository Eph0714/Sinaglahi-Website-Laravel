@extends('layouts.app')

@section('title', 'Submit Your Artworks')

@section('content')

<section class="section">
    <div class="container">
        <div class="join-steps mb-4">
            <div class="join-step join-step-done"><span class="join-step-dot">✓</span> <span class="join-step-label">Personal Info</span></div>
            <div class="join-step join-step-done"><span class="join-step-dot">✓</span> <span class="join-step-label">Art Background</span></div>
            <div class="join-step join-step-current"><span class="join-step-dot">3</span> <span class="join-step-label">Artworks</span></div>
            <div class="join-step"><span class="join-step-dot">4</span> <span class="join-step-label">Review &amp; Submit</span></div>
        </div>

        <div class="col-lg-8 mx-auto">
            <div class="artwork-progress {{ $existingArtworks->count() >= $minArtworks ? 'complete' : '' }}">
                <span>{{ $existingArtworks->count() }} of {{ $minArtworks }}-{{ $maxArtworks }} artworks uploaded</span>
                @if ($existingArtworks->count() >= $minArtworks)
                    <a href="{{ route('join.consent') }}" class="btn btn-sm btn-primary-brand">Continue to Review &rarr;</a>
                @endif
            </div>

            @if (session('artworkAdded'))
                <div class="alert alert-success mt-3">{{ session('artworkAdded') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger mt-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($existingArtworks->isNotEmpty())
                <div class="form-section mt-3">
                    <h2>Your Artworks</h2>
                    @foreach ($existingArtworks as $art)
                        <div class="artwork-row">
                            <img src="{{ route('join.artwork-image', $art->Id) }}" alt="{{ $art->Title }}" />
                            <div class="artwork-row-info">
                                <div class="title">{{ $art->Title }}</div>
                                <div class="meta">{{ $art->Medium }} @if($art->Year) &middot; {{ $art->Year }} @endif @if($art->Size) &middot; {{ $art->Size }} @endif</div>
                            </div>
                            <form action="{{ route('join.remove-artwork', $art->Id) }}" method="post" onsubmit="return confirm('Remove this artwork?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($existingArtworks->count() < $maxArtworks)
                <div class="form-section mt-3">
                    <h2>Add an Artwork</h2>
                    <form action="{{ route('join.add-artwork') }}" method="post" enctype="multipart/form-data" novalidate>
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Image</label>
                                <input name="Image" type="file" accept=".jpg,.jpeg,.png,.webp" class="form-control" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Title</label>
                                <input name="Title" class="form-control" required />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Medium</label>
                                <select name="MediumId" class="form-select" id="mediumSelect" required>
                                    <option value="">-- Select --</option>
                                    @foreach ($mediumOptions as $m)
                                        <option value="{{ $m->Id }}" data-other="{{ $m->IsOtherOption ? 'true' : 'false' }}">{{ $m->Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3" id="customMediumWrap" hidden>
                                <label class="form-label">Specify Medium</label>
                                <input name="CustomMedium" class="form-control" />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Year</label>
                                <input name="Year" type="number" class="form-control" />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Size</label>
                                <input name="Size" class="form-control" />
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="Description" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary-brand mt-3">Add Artwork</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    (function () {
        var select = document.getElementById('mediumSelect');
        var wrap = document.getElementById('customMediumWrap');
        if (!select || !wrap) return;
        function sync() {
            var opt = select.options[select.selectedIndex];
            wrap.hidden = !(opt && opt.getAttribute('data-other') === 'true');
        }
        select.addEventListener('change', sync);
        sync();
    })();
</script>
@endpush
