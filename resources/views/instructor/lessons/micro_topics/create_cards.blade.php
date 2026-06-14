@extends('layouts.app')

@section('title', 'Create Flashcards')
@section('page-title', 'Create Micro-Topic Flashcards')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold mb-0">Lesson: {{ $lesson->title }}</h2>
            <p class="text-muted">Fill out the details below to generate swipable concept cards.</p>
        </div>
    </div>

    <form action="{{ route('instructor.lessons.micro-topics.store', $lesson) }}" method="POST" id="flashcardForm">
        @csrf
        <input type="hidden" name="concept_cards" id="conceptCardsInput">

        <!-- Basic Topic Info -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-white pb-0 border-bottom-0">
                <h4 class="mb-0 fw-bold">Topic Details</h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Topic Title</label>
                        <input type="text" name="topic_name" required class="form-control" placeholder="e.g. Introduction to Hardware">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Estimated Minutes</label>
                        <input type="number" name="estimated_minutes" required class="form-control" placeholder="e.g. 5" value="5">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Topic Description (Optional)</label>
                        <textarea name="description" rows="2" class="form-control" placeholder="Brief summary for the teacher..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Concept Cards Builder -->
        <div class="card mb-4 shadow-sm border-0 border-top border-primary border-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold">Concept Cards Builder 🃏</h4>
                    <small class="text-muted">Modify the knowledge chunks that students will swipe through.</small>
                </div>
                <button type="button" id="addCardBtn" class="btn btn-primary btn-sm fw-bold">
                    <i class="bi bi-plus-lg"></i> Add Flashcard
                </button>
            </div>
            <div class="card-body bg-light">
                <div id="cardsContainer" class="d-flex flex-column gap-3">
                    <!-- Cards injected via JS -->
                </div>
            </div>
        </div>

        <!-- Key Takeaway -->
        <div class="card mb-4 shadow-sm border-0 border-top border-success border-4 bg-success bg-opacity-10">
            <div class="card-body">
                <h4 class="fw-bold text-success mb-2">Key Takeaway 🎯</h4>
                <p class="text-muted small mb-3">This appears at the very end of the flashcards right before the final Quiz.</p>
                <textarea name="key_takeaway" rows="2" required placeholder="e.g. In summary, Hardware is..." class="form-control border-success"></textarea>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('instructor.lessons.micro-topics.index', $lesson) }}" class="btn btn-light border">Cancel</a>
            <button type="submit" class="btn btn-success fw-bold px-4">
                <i class="bi bi-save me-1"></i> Save Flashcards
            </button>
        </div>
    </form>
</div>

<!-- Script to Handle Dynamic Addition -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('cardsContainer');
        const addBtn = document.getElementById('addCardBtn');
        const form = document.getElementById('flashcardForm');
        const hiddenInput = document.getElementById('conceptCardsInput');

        let cardCount = 0;

        function addCard() {
            cardCount++;
            const actId = cardCount;
            const div = document.createElement('div');
            div.className = 'card border bg-white shadow-sm position-relative card-item';
            div.innerHTML = `
                <div class="card-body">
                    <button type="button" class="btn btn-outline-danger btn-sm position-absolute top-0 end-0 m-2" onclick="this.closest('.card-item').remove()">
                        <i class="bi bi-trash"></i>
                    </button>
                    <h6 class="fw-bold mb-3 text-secondary">Flashcard #${actId}</h6>
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Emoji</label>
                            <input type="text" class="form-control text-center card-emoji" placeholder="🚀" maxlength="2">
                        </div>
                        <div class="col-md-10">
                            <label class="form-label small fw-semibold">Title (Short Bold Headline)</label>
                            <input type="text" class="form-control card-title" placeholder="e.g. What is Hardware?">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Study Text</label>
                            <textarea rows="2" class="form-control card-body-text" placeholder="Explain the concept clearly and concisely."></textarea>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(div);
        }

        // Add initial card
        addCard();

        addBtn.addEventListener('click', addCard);

        form.addEventListener('submit', function(e) {
            const cardsJSON = [];
            document.querySelectorAll('.card-item').forEach(function(item) {
                cardsJSON.push({
                    emoji: item.querySelector('.card-emoji').value,
                    title: item.querySelector('.card-title').value,
                    body: item.querySelector('.card-body-text').value
                });
            });
            hiddenInput.value = JSON.stringify(cardsJSON);
        });
    });
</script>
@endsection
