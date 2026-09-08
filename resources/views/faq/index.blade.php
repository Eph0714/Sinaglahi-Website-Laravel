@extends('layouts.app')

@section('title', 'Frequently Asked Questions')

@section('content')

<section class="page-hero page-hero-compact">
    <div class="container text-center">
        <h1>Frequently Asked Questions</h1>
        <p class="lead-muted">Answers to common questions about Sinaglahi Artists Group Nueva Vizcaya Inc.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                @if ($faqs->isEmpty())
                    <div class="empty-state"><h3>No FAQs published yet</h3></div>
                @else
                    <div class="accordion" id="faqAccordion">
                        @foreach ($faqs as $i => $faq)
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button {{ $i === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $faq->Id }}">
                                        {{ $faq->Question }}
                                    </button>
                                </h2>
                                <div id="faq{{ $faq->Id }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-muted">{{ $faq->Answer }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

@endsection
