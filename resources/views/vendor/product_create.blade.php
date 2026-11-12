@extends('layouts.app')

@push('head')
    <script src="{{ asset('assets/product-editor.js') }}" defer></script>
@endpush

@section('content')
<section class="panel">
    <h1>Add Product</h1>
    <form class="form-grid" method="post" action="{{ route('vendor.products.store') }}">
        @csrf
        <label>
            Product Name
            <input type="text" name="name" value="{{ old('name') }}" required>
        </label>
        <label>
            Category
            <select name="category_id" required>
                <option value="">Select category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </label>
        <label>
            Price
            <input type="number" name="price" min="0.01" step="0.01" value="{{ old('price') }}" placeholder="Leave blank to show contact for price">
        </label>
        <label>
            Stock
            <input type="number" name="stock" min="0" value="{{ old('stock', 0) }}" required>
        </label>
        <label>
            Image URL
            <input type="url" name="image_url" value="{{ old('image_url') }}">
        </label>
        <label>
            Meta Description
            <textarea name="meta_description" rows="3">{{ old('meta_description') }}</textarea>
        </label>
        <div class="rich-editor-field">
            <span class="field-label">Description</span>
            <div class="editor-shell" data-rich-editor>
                <div class="editor-toolbar">
                    <button type="button" data-command="undo">Undo</button>
                    <button type="button" data-command="redo">Redo</button>
                    <button type="button" data-command="bold"><strong>B</strong></button>
                    <button type="button" data-command="italic"><em>I</em></button>
                    <button type="button" data-command="insertUnorderedList">Bullets</button>
                    <button type="button" data-command="insertOrderedList">Numbers</button>
                    <button type="button" data-command="justifyLeft">Left</button>
                    <button type="button" data-command="justifyCenter">Center</button>
                    <button type="button" data-command="justifyRight">Right</button>
                    <button type="button" data-action="link">Link</button>
                    <button type="button" data-action="clear">Clear</button>
                </div>
                <div class="editor-surface" data-editor-surface data-placeholder="Write the product description here..." contenteditable="true"></div>
                <textarea class="rich-editor-input" name="description" hidden>{{ old('description') }}</textarea>
            </div>
        </div>
        <button type="submit">Save Product</button>
    </form>
</section>
@endsection
