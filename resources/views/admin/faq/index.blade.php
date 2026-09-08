@extends('layouts.admin')

@section('title', 'FAQ Management')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">FAQ Management</h1>
    <button class="btn btn-primary-brand" data-bs-toggle="modal" data-bs-target="#addFaqModal">Add FAQ</button>
</div>

@if (session('faqMessage'))
    <div class="alert alert-success">{{ session('faqMessage') }}</div>
@endif

@if ($faqs->isEmpty())
    <div class="empty-state"><h3>No FAQs yet</h3></div>
@else
    <div class="table-responsive">
        <table class="dash-table">
            <thead><tr><th>Question</th><th>Category</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach ($faqs as $faq)
                    <tr>
                        <td style="max-width:360px;">{{ $faq->Question }}</td>
                        <td class="small text-muted">{{ $faq->Category }}</td>
                        <td>{{ $faq->DisplayOrder }}</td>
                        <td><span class="status-badge {{ $faq->IsPublished ? 'status-published' : 'status-draft' }}">{{ $faq->IsPublished ? 'Published' : 'Draft' }}</span></td>
                        <td class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-brand" data-bs-toggle="modal" data-bs-target="#editFaqModal-{{ $faq->Id }}">Edit</button>
                            <form action="{{ route('admin.faq.toggle', $faq->Id) }}" method="post">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary">{{ $faq->IsPublished ? 'Unpublish' : 'Publish' }}</button>
                            </form>
                            <form action="{{ route('admin.faq.destroy', $faq->Id) }}" method="post" onsubmit="return confirm('This action will permanently delete this record. This action cannot be undone. Are you sure you want to continue?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editFaqModal-{{ $faq->Id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form action="{{ route('admin.faq.update') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="Id" value="{{ $faq->Id }}" />
                                    <div class="modal-header"><h5 class="modal-title">Edit FAQ</h5></div>
                                    <div class="modal-body">
                                        <div class="mb-2"><label class="form-label">Question</label><input name="Question" class="form-control" value="{{ $faq->Question }}" required maxlength="300" /></div>
                                        <div class="mb-2"><label class="form-label">Answer</label><textarea name="Answer" class="form-control" rows="4" required maxlength="4000">{{ $faq->Answer }}</textarea></div>
                                        <div class="mb-2"><label class="form-label">Category</label><input name="Category" class="form-control" value="{{ $faq->Category }}" maxlength="100" /></div>
                                        <div class="mb-2"><label class="form-label">Display Order</label><input type="number" name="DisplayOrder" class="form-control" value="{{ $faq->DisplayOrder }}" /></div>
                                        <div class="form-check"><input type="checkbox" name="IsPublished" value="1" class="form-check-input" @checked($faq->IsPublished) /><label class="form-check-label">Published</label></div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary-brand">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<div class="modal fade" id="addFaqModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.faq.store') }}" method="post">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Add FAQ</h5></div>
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label">Question</label><input name="Question" class="form-control" required maxlength="300" /></div>
                    <div class="mb-2"><label class="form-label">Answer</label><textarea name="Answer" class="form-control" rows="4" required maxlength="4000"></textarea></div>
                    <div class="mb-2"><label class="form-label">Category</label><input name="Category" class="form-control" maxlength="100" /></div>
                    <div class="mb-2"><label class="form-label">Display Order</label><input type="number" name="DisplayOrder" class="form-control" value="0" /></div>
                    <div class="form-check"><input type="checkbox" name="IsPublished" value="1" class="form-check-input" checked /><label class="form-check-label">Published</label></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-brand">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
