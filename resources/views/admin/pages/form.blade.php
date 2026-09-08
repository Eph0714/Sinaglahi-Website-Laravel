@extends('layouts.admin')

@section('title', $page ? 'Edit Page' : 'Add Page')

@section('content')

<h1 class="h3 mb-4">{{ $page ? 'Edit Page' : 'Add Page' }}</h1>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $page ? route('admin.pages.update', $page->Id) : route('admin.pages.store') }}" method="post" enctype="multipart/form-data" novalidate>
    @csrf

    <div class="form-section">
        <h2>Content</h2>
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Title</label>
                <input name="Title" class="form-control" value="{{ old('Title', $page->Title ?? '') }}" required />
            </div>
            <div class="col-md-4">
                <label class="form-label">Slug (optional)</label>
                <input name="Slug" class="form-control" placeholder="auto-generated from title" value="{{ old('Slug', $page->Slug ?? '') }}" />
            </div>
            <div class="col-12">
                <label class="form-label">Body</label>
                <textarea name="Content" class="form-control" rows="10">{{ old('Content', $page->Content ?? '') }}</textarea>
                <p class="text-muted small mb-0">Basic HTML is allowed (headings, paragraphs, bold/italic, lists, links, images, quotes). Scripts and event handlers are stripped automatically.</p>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h2>Featured Image</h2>
        @if ($page?->FeaturedImagePath)
            <img src="{{ $page->FeaturedImagePath }}" class="mb-2 rounded" style="max-height:200px;" />
        @endif
        <input name="FeaturedImage" type="file" accept=".jpg,.jpeg,.png,.webp" class="form-control" />
    </div>

    <div class="form-section">
        <h2>SEO &amp; Publishing</h2>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">SEO Title</label>
                <input name="SeoTitle" class="form-control" value="{{ old('SeoTitle', $page->SeoTitle ?? '') }}" />
            </div>
            <div class="col-md-6">
                <label class="form-label">Display Order</label>
                <input name="DisplayOrder" type="number" class="form-control" value="{{ old('DisplayOrder', $page->DisplayOrder ?? 0) }}" />
            </div>
            <div class="col-12">
                <label class="form-label">SEO Description</label>
                <textarea name="SeoDescription" class="form-control" rows="2">{{ old('SeoDescription', $page->SeoDescription ?? '') }}</textarea>
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input name="IsPublished" type="checkbox" value="1" class="form-check-input" id="isPublished" @checked(old('IsPublished', $page->IsPublished ?? false)) />
                    <label class="form-check-label" for="isPublished">Published</label>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary-brand btn-lg">{{ $page ? 'Save Changes' : 'Create Page' }}</button>
    <a class="btn btn-outline-brand btn-lg" href="{{ route('admin.pages.index') }}">Cancel</a>
</form>

@endsection
