@extends('layouts.admin')

@section('title', 'Why Join Sinaglahi')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="h3 mb-0">"Why Join Sinaglahi Artists?" Section</h1>
    <div class="d-flex gap-2">
        <a class="btn btn-edit" href="{{ route('admin.why-join.benefits') }}"><i class="bi bi-grid-3x3-gap"></i> Manage Benefit Cards</a>
        <a class="btn btn-edit" href="{{ route('admin.why-join.photos') }}"><i class="bi bi-images"></i> Manage Photos</a>
    </div>
</div>
<p class="text-muted small">
    This content drives the public homepage's "Why Join Sinaglahi Artists?" section. Nothing here is
    hard-coded - edit the copy below, then manage the benefit cards and the photo gallery from their own pages.
</p>

@if (session('whyJoinMessage'))
    <div class="alert alert-success">{{ session('whyJoinMessage') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.why-join.update') }}" method="post" novalidate>
    @csrf

    <div class="form-section">
        <h2>Headline</h2>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Title</label>
                <input name="Title" class="form-control" value="{{ old('Title', $section->Title) }}" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Subtitle</label>
                <input name="Subtitle" class="form-control" value="{{ old('Subtitle', $section->Subtitle) }}" required />
            </div>
            <div class="col-12">
                <label class="form-label">Introduction</label>
                <textarea name="Introduction" class="form-control" rows="3" required>{{ old('Introduction', $section->Introduction) }}</textarea>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h2>Featured Statement</h2>
        <p class="text-muted small">The prominent quote-style call-to-action shown after the benefit cards.</p>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Featured Statement</label>
                <input name="FeaturedStatement" class="form-control" value="{{ old('FeaturedStatement', $section->FeaturedStatement) }}" required />
            </div>
            <div class="col-12">
                <label class="form-label">Supporting Paragraph</label>
                <textarea name="SupportingParagraph" class="form-control" rows="2" required>{{ old('SupportingParagraph', $section->SupportingParagraph) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Button Text</label>
                <input name="ButtonText" class="form-control" value="{{ old('ButtonText', $section->ButtonText) }}" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Button Destination</label>
                <input name="ButtonUrl" class="form-control" placeholder="/join" value="{{ old('ButtonUrl', $section->ButtonUrl) }}" required />
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary-brand btn-lg"><i class="bi bi-check-lg"></i> Save Section Content</button>
</form>

@endsection
