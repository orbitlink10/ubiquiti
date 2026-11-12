@extends('admin.layout')

@php
    $testimonialToEdit = $testimonialToEdit ?? null;
    $isEditingTestimonial = $testimonialToEdit instanceof \App\Models\Testimonial;
@endphp

@section('content')
<div class="admin-shell">
    @include('admin.partials.sidebar', ['activeAdminNav' => 'testimonials'])

    <div class="admin-main admin-management-main">
        <section class="admin-page-head admin-page-head--product-create">
            <div>
                <h1 class="admin-page-title">{{ $isEditingTestimonial ? 'Edit Testimonial' : 'Add Testimonial' }}</h1>
                <p class="admin-page-copy">{{ $isEditingTestimonial ? 'Update the customer testimonial details below.' : 'Create a homepage testimonial with a quote, rating, and display status.' }}</p>
            </div>
        </section>

        <section class="panel admin-product-create-panel">
            @unless($testimonialsStorageReady)
                <div class="alert error">
                    Testimonial storage is not ready yet. Run <code>php artisan migrate</code> to create the <code>testimonials</code> table before saving testimonials.
                </div>
            @endunless

            <form class="admin-product-create-form" method="post" action="{{ $isEditingTestimonial ? route('admin.testimonials.update', $testimonialToEdit) : route('admin.testimonials.store') }}">
                @csrf
                @if($isEditingTestimonial)
                    @method('PUT')
                @endif

                <div class="admin-form-grid admin-form-grid--two">
                    <div class="admin-product-field">
                        <label class="admin-product-label" for="name">Name</label>
                        <input
                            class="admin-product-input"
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name', $testimonialToEdit?->name) }}"
                            placeholder="e.g. Mary K., Nyeri"
                            @disabled(! $testimonialsStorageReady)
                            required
                        >
                    </div>

                    <div class="admin-product-field">
                        <label class="admin-product-label" for="role">Role Label</label>
                        <input
                            class="admin-product-input"
                            id="role"
                            type="text"
                            name="role"
                            value="{{ old('role', $testimonialToEdit?->role ?? 'Customer') }}"
                            placeholder="e.g. Customer"
                            @disabled(! $testimonialsStorageReady)
                            required
                        >
                    </div>
                </div>

                <div class="admin-product-field">
                    <label class="admin-product-label" for="quote">Description</label>
                    <textarea
                        class="admin-product-input admin-product-textarea"
                        id="quote"
                        name="quote"
                        rows="6"
                        placeholder="Write the testimonial quote here"
                        @disabled(! $testimonialsStorageReady)
                        required
                    >{{ old('quote', $testimonialToEdit?->quote) }}</textarea>
                </div>

                <div class="admin-form-grid admin-form-grid--three">
                    <div class="admin-product-field">
                        <label class="admin-product-label" for="rating">Rating</label>
                        <select class="admin-product-input admin-product-select" id="rating" name="rating" @disabled(! $testimonialsStorageReady) required>
                            @for($rating = 5; $rating >= 1; $rating--)
                                <option value="{{ $rating }}" @selected((int) old('rating', $testimonialToEdit?->rating ?? 5) === $rating)>{{ $rating }} Star{{ $rating === 1 ? '' : 's' }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="admin-product-field">
                        <label class="admin-product-label" for="sort_order">Sort Order</label>
                        <input
                            class="admin-product-input"
                            id="sort_order"
                            type="number"
                            name="sort_order"
                            min="0"
                            value="{{ old('sort_order', $testimonialToEdit?->sort_order ?? 0) }}"
                            @disabled(! $testimonialsStorageReady)
                        >
                    </div>

                    <div class="admin-product-field">
                        <label class="admin-product-label" for="is_active">Visibility</label>
                        <select class="admin-product-input admin-product-select" id="is_active" name="is_active" @disabled(! $testimonialsStorageReady) required>
                            <option value="1" @selected((string) old('is_active', $testimonialToEdit ? ($testimonialToEdit->is_active ? '1' : '0') : '1') === '1')>Active</option>
                            <option value="0" @selected((string) old('is_active', $testimonialToEdit ? ($testimonialToEdit->is_active ? '1' : '0') : '1') === '0')>Hidden</option>
                        </select>
                    </div>
                </div>

                <div class="admin-product-actions">
                    <p>Use lower sort orders to show testimonials earlier on the homepage.</p>
                    <div class="admin-actions-inline">
                        <a class="admin-secondary-pill" href="{{ route('admin.testimonials.index') }}">Back</a>
                        <button type="submit" class="admin-primary-pill" @disabled(! $testimonialsStorageReady)>{{ $isEditingTestimonial ? 'Update Testimonial' : 'Save Testimonial' }}</button>
                    </div>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
