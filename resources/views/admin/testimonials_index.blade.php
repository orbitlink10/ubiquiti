@extends('admin.layout')

@section('content')
<div class="admin-shell">
    @include('admin.partials.sidebar', ['activeAdminNav' => 'testimonials'])

    <div class="admin-main admin-management-main">
        <section class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Testimonials</h1>
                <p class="admin-page-copy">Manage and view all testimonials displayed on the homepage.</p>
            </div>
            <a
                class="admin-primary-pill @unless($testimonialsStorageReady) is-disabled @endunless"
                href="{{ $testimonialsStorageReady ? route('admin.testimonials.create') : '#' }}"
                @unless($testimonialsStorageReady) aria-disabled="true" @endunless
            >+ Add Testimonial</a>
        </section>

        <section class="panel admin-settings-panel">
            <div class="admin-settings-panel-bar">Homepage Testimonial Settings</div>

            @unless($homepageContentStorageReady)
                <div class="alert error">
                    Homepage content storage is not ready yet. Run <code>php artisan migrate</code> to create the <code>homepage_contents</code> table before saving testimonial section settings.
                </div>
            @endunless

            <form class="admin-settings-form" method="post" action="{{ route('admin.testimonials.settings.update') }}">
                @csrf

                <div class="admin-settings-subgrid">
                    <div class="admin-settings-field">
                        <label class="admin-settings-label" for="testimonials_badge">Badge</label>
                        <input
                            class="admin-settings-input"
                            id="testimonials_badge"
                            type="text"
                            name="testimonials_badge"
                            value="{{ old('testimonials_badge', $homepageContent->testimonialsBadge()) }}"
                            @disabled(! $homepageContentStorageReady)
                        >
                    </div>

                    <div class="admin-settings-field">
                        <label class="admin-settings-label" for="testimonials_title">Section Title</label>
                        <input
                            class="admin-settings-input"
                            id="testimonials_title"
                            type="text"
                            name="testimonials_title"
                            value="{{ old('testimonials_title', $homepageContent->testimonialsTitle()) }}"
                            @disabled(! $homepageContentStorageReady)
                            required
                        >
                    </div>
                </div>

                <div class="admin-settings-field">
                    <label class="admin-settings-label" for="testimonials_intro">Intro Text</label>
                    <textarea
                        class="admin-settings-textarea"
                        id="testimonials_intro"
                        name="testimonials_intro"
                        rows="3"
                        @disabled(! $homepageContentStorageReady)
                    >{{ old('testimonials_intro', $homepageContent->testimonialsIntro()) }}</textarea>
                </div>

                <div class="admin-settings-actions">
                    <button type="submit" class="admin-secondary-pill" @disabled(! $homepageContentStorageReady)>Save Section Settings</button>
                </div>
            </form>
        </section>

        <section class="panel admin-list-panel">
            <div class="admin-list-panel-head">
                <h2>Testimonial List</h2>
            </div>

            @unless($testimonialsStorageReady)
                <div class="alert error">
                    Testimonial storage is not ready yet. Run <code>php artisan migrate</code> to create the <code>testimonials</code> table before managing testimonials.
                </div>
            @endunless

            <div class="table-wrap">
                <table class="admin-data-table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($testimonials as $testimonial)
                        <tr>
                            <td>{{ $testimonials->firstItem() + $loop->index }}</td>
                            <td>{{ $testimonial->name }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($testimonial->quote, 110) }}</td>
                            <td>{{ $testimonial->rating }}</td>
                            <td>{{ $testimonial->is_active ? 'Active' : 'Hidden' }}</td>
                            <td>
                                <div class="admin-action-stack">
                                    <a class="admin-outline-action tone-info" href="{{ route('admin.testimonials.show', $testimonial) }}">View</a>
                                    <a class="admin-outline-action tone-primary" href="{{ route('admin.testimonials.edit', $testimonial) }}">Update</a>
                                    <form method="post" action="{{ route('admin.testimonials.destroy', $testimonial) }}" onsubmit="return confirm('Delete this testimonial?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-outline-action tone-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="admin-empty-cell">No testimonials found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($testimonials->hasPages())
                <div class="pager">
                    @if($testimonials->onFirstPage())
                        <span class="pager-link disabled">Previous</span>
                    @else
                        <a class="pager-link" href="{{ $testimonials->previousPageUrl() }}">Previous</a>
                    @endif
                    <span>Page {{ $testimonials->currentPage() }} of {{ $testimonials->lastPage() }}</span>
                    @if($testimonials->hasMorePages())
                        <a class="pager-link" href="{{ $testimonials->nextPageUrl() }}">Next</a>
                    @else
                        <span class="pager-link disabled">Next</span>
                    @endif
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
