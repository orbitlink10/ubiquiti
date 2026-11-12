@extends('admin.layout')

@section('content')
<div class="admin-shell">
    @include('admin.partials.sidebar', ['activeAdminNav' => 'categories'])

    <div class="admin-main admin-management-main">
        <section class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Categories</h1>
            </div>
            <a class="admin-primary-pill" href="{{ route('admin.categories.create') }}">Create New Category</a>
        </section>

        <section class="panel admin-list-panel">
            <div class="table-wrap">
                <table class="admin-data-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Photo</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->slug }}</td>
                            <td>
                                @if($category->image_url)
                                    <img class="admin-thumb admin-thumb--page" src="{{ $category->image_url }}" alt="{{ $category->name }}">
                                @else
                                    <div class="admin-thumb admin-thumb--page admin-thumb--placeholder">No Image</div>
                                @endif
                            </td>
                            <td>
                                <div class="admin-action-row">
                                    <a
                                        class="admin-row-action tone-info"
                                        href="{{ route('category.show', $category) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >Preview</a>
                                    <a class="admin-row-action tone-warning" href="{{ route('admin.categories.edit', $category) }}">Update</a>
                                    <form method="post" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-row-action tone-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="admin-empty-cell">No categories yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
