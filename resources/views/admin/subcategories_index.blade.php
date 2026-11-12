@extends('admin.layout')

@section('content')
<div class="admin-shell">
    @include('admin.partials.sidebar', ['activeAdminNav' => 'subcategories'])

    <div class="admin-main admin-management-main">
        <section class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Sub Categories</h1>
            </div>
            <a class="admin-primary-pill" href="{{ route('admin.categories.create') }}">Create New Sub Category</a>
        </section>

        <section class="panel admin-list-panel">
            <div class="table-wrap">
                <table class="admin-data-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Parent</th>
                        <th>Slug</th>
                        <th>Photo</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($subcategories as $subcategory)
                        <tr>
                            <td>{{ $subcategory->id }}</td>
                            <td>{{ $subcategory->name }}</td>
                            <td>{{ $subcategory->parent?->name ?? 'Unassigned' }}</td>
                            <td>{{ $subcategory->slug }}</td>
                            <td>
                                @if($subcategory->image_url)
                                    <img class="admin-thumb" src="{{ $subcategory->image_url }}" alt="{{ $subcategory->name }}">
                                @else
                                    <div class="admin-thumb admin-thumb--placeholder">No Image</div>
                                @endif
                            </td>
                            <td>
                                <div class="admin-action-row">
                                    <a
                                        class="admin-row-action tone-info"
                                        href="{{ route('category.show', $subcategory) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >Preview</a>
                                    <a class="admin-row-action tone-warning" href="{{ route('admin.categories.edit', $subcategory) }}">Update</a>
                                    <form method="post" action="{{ route('admin.categories.destroy', $subcategory) }}" onsubmit="return confirm('Delete this sub category?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-row-action tone-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="admin-empty-cell">No sub categories yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
