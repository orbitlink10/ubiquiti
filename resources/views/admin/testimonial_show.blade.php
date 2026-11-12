@extends('admin.layout')

@section('content')
<div class="admin-shell">
    @include('admin.partials.sidebar', ['activeAdminNav' => 'testimonials'])

    <div class="admin-main admin-management-main">
        <section class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Testimonial Details</h1>
                <p class="admin-page-copy">Review the testimonial content and homepage visibility details.</p>
            </div>
        </section>

        <section class="panel admin-detail-card">
            <div class="admin-detail-grid">
                <div>
                    <p class="admin-detail-label">Name</p>
                    <h2 class="admin-detail-title">{{ $testimonial->name }}</h2>
                </div>
                <div>
                    <p class="admin-detail-label">Role</p>
                    <p class="admin-detail-copy">{{ $testimonial->role }}</p>
                </div>
                <div>
                    <p class="admin-detail-label">Rating</p>
                    <p class="admin-rating-pill">{{ str_repeat('★', max(1, min(5, (int) $testimonial->rating))) }} <span>{{ $testimonial->rating }}/5</span></p>
                </div>
                <div>
                    <p class="admin-detail-label">Status</p>
                    <p class="admin-detail-copy">{{ $testimonial->is_active ? 'Active on homepage' : 'Hidden from homepage' }}</p>
                </div>
            </div>

            <div class="admin-product-field">
                <p class="admin-detail-label">Description</p>
                <blockquote class="admin-testimonial-quote">{{ $testimonial->quote }}</blockquote>
            </div>

            <div class="admin-product-actions">
                <p>Sort order: {{ $testimonial->sort_order }}</p>
                <div class="admin-actions-inline">
                    <a class="admin-secondary-pill" href="{{ route('admin.testimonials.index') }}">Back</a>
                    <a class="admin-primary-pill" href="{{ route('admin.testimonials.edit', $testimonial) }}">Update</a>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
