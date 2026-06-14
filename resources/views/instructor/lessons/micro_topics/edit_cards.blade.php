@extends('layouts.app')

@section('title', 'Edit Flashcards')
@section('page-title', 'Edit Micro-Topic & Flashcards')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold mb-0">Lesson: {{ $lesson->title }}</h2>
            <p class="text-muted">Update your micro-learning material.</p>
        </div>
    </div>

    <form action="{{ route('instructor.lessons.micro-topics.update', [$lesson, $topic]) }}" method="POST" id="flashcardForm">
        @csrf
        @method('PUT')
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
                        <input type="text" name="topic_name" value="{{ $topic->topic_name }}" required class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Estimated Minutes</label>
                        <input type="number" name="estimated_minutes" value="{{ $topic->estimated_minutes }}" required class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Topic Description (Optional)</label>
                        <textarea name="description" rows="2" class="form-control">{{ $topic->description }}</textarea>
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
                <textarea name="key_takeaway" rows="2" required class="form-control border-success">{{ $topic->key_takeaway }}</textarea>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('instructor.lessons.micro-topics.index', $lesson) }}" class="btn btn-light border">Cancel</a>
            <button type="submit" class="btn btn-primary fw-bold px-4">
                <i class="bi bi-save me-1"></i> Update Flashcards & Topic
            </button>
        </div>
    </form>
</div>

<!-- Script to Load Existing Cards and Handle Dynamic Addition -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('cardsContainer');
        const addBtn = document.getElementById('addCardBtn');
        const form = document.getElementById('flashcardForm');
        const hiddenInput = document.getElementById('conceptCardsInput');

        let existingCards = @json($topic->concept_cards ?? []);
        if (!Array.isArray(existingCards)) existingCards = [];

        let cardCount = 0;

        function addCard(data = null) {
            cardCount++;
            const actId = cardCount;
            
            const emojiVal = data ? data.emoji : '';
            const titleVal = data ? data.title : '';
            const bodyVal = data ? data.body : '';

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
                            <input type="text" class="form-control text-center card-emoji" placeholder="🚀" maxlength="2" value="${escapeHtml(emojiVal)}">
                        </div>
                        <div class="col-md-10">
                            <label class="form-label small fw-semibold">Title (Short Bold Headline)</label>
                            <input type="text" class="form-control card-title" placeholder="e.g. What is Hardware?" value="${escapeHtml(titleVal)}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Study Text</label>
                            <textarea rows="2" class="form-control card-body-text" placeholder="Explain the concept clearly and concisely.">${escapeHtml(bodyVal)}</textarea>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(div);
        }

        function escapeHtml(text) {
            return text ? text.replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;') : '';
        }

        if (existingCards.length > 0) {
            existingCards.forEach(card => addCard(card));
        } else {
            addCard();
        }

        addBtn.addEventListener('click', () => addCard());

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
