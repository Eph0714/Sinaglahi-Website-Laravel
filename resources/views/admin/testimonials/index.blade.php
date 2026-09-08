@extends('layouts.admin')

@section('title', 'Testimonials')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Testimonials</h1>
    <a class="btn btn-primary-brand" href="{{ route('admin.testimonials.create') }}">Add Testimonial</a>
</div>

@if (session('testimonialMessage'))
    <div class="alert alert-success">{{ session('testimonialMessage') }}</div>
@endif

@if ($testimonials->isEmpty())
    <div class="empty-state"><h3>No testimonials yet</h3></div>
@else
    <div class="table-responsive">
        <table class="dash-table">
            <thead><tr><th></th><th>Name</th><th>Quote</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach ($testimonials as $t)
                    <tr>
                        <td><img class="thumb" style="border-radius:50%;" src="{{ $t->ImagePath ?? placeholder_image(60, 60, $t->Name) }}" alt="{{ $t->Name }}" /></td>
                        <td>{{ $t->Name }}<div class="text-muted small">{{ $t->Position }}</div></td>
                        <td class="small">{{ \Illuminate\Support\Str::limit($t->Quote, 80) }}</td>
                        <td>{{ $t->DisplayOrder }}</td>
                        <td><span class="status-badge {{ $t->IsPublished ? 'status-published' : 'status-draft' }}">{{ $t->IsPublished ? 'Published' : 'Draft' }}</span></td>
                        <td class="row-actions">
                            <a class="btn btn-sm btn-outline-brand" href="{{ route('admin.testimonials.edit', $t->Id) }}">Edit</a>
                            <form action="{{ route('admin.testimonials.toggle', $t->Id) }}" method="post">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary">{{ $t->IsPublished ? 'Unpublish' : 'Publish' }}</button>
                            </form>
                            <form action="{{ route('admin.testimonials.destroy', $t->Id) }}" method="post" onsubmit="return confirm('This action will permanently delete this record. This action cannot be undone. Are you sure you want to continue?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@endsection
