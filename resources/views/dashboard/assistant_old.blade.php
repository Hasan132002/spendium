@extends('backend.layouts.app')

@section('title', 'AI Assistant')

@section('admin-content')
<meta name="csrf-token" content="{{ csrf_token() }}" />

<style>
    .chat-container {
        max-height: 500px;
        overflow-y: auto;
        padding: 1rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
    }
    .chat-message {
        max-width: 70%;
        margin-bottom: 1rem;
        padding: 0.75rem 1rem;
        border-radius: 1rem;
        position: relative;
    }
    .user-message {
        margin-left: auto;
        background-color: #2563eb;
        color: white;
    }
    .ai-message {
        margin-right: auto;
        background-color: #e2e8f0;
        color: #1f2937;
    }
    .chat-timestamp {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.25rem;
        text-align: right;
    }
    .typing-indicator {
        font-style: italic;
        color: #6b7280;
    }
</style>

<div class="max-w-3xl mx-auto p-6">
    <h2 class="text-2xl font-semibold mb-4">AI Chat Assistant</h2>

    <div class="chat-container mb-6" id="chat-box">
        @foreach($pastChats->reverse() as $chat)
            <div class="chat-message user-message">
                <p><strong>You:</strong> {{ $chat->question }}</p>
                <div class="chat-timestamp">{{ $chat->created_at->diffForHumans() }}</div>
            </div>
            @if($chat->answer)
                <div class="chat-message ai-message">
                    <p><strong>AI:</strong> {{ $chat->answer }}</p>
                    <div class="chat-timestamp">{{ $chat->created_at->diffForHumans() }}</div>
                </div>
            @endif
        @endforeach
    </div>

    <form id="chat-form">
        <div class="flex gap-2">
            <textarea name="question" id="question" class="flex-grow p-3 border rounded-lg" rows="2" placeholder="Type your message..." required></textarea>
            <button type="submit" class="btn btn-primary px-4 py-2">Send</button>
        </div>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const chatBox = document.getElementById("chat-box");
    const form = document.getElementById("chat-form");
    const questionInput = document.getElementById("question");

    // Scroll to bottom on load
    chatBox.scrollTop = chatBox.scrollHeight;

    form.addEventListener("submit", function(e) {
        e.preventDefault();

        const question = questionInput.value.trim();
        if (!question) return;

        // Add user message to UI
        const userMessage = `
            <div class="chat-message user-message">
                <p><strong>You:</strong> ${question}</p>
                <div class="chat-timestamp">Just now</div>
            </div>
        `;
        chatBox.insertAdjacentHTML("beforeend", userMessage);

        // Typing indicator
        const typingIndicator = `
            <div id="typing" class="chat-message ai-message typing-indicator">
                <p><strong>AI:</strong> Typing...</p>
            </div>
        `;
        chatBox.insertAdjacentHTML("beforeend", typingIndicator);
        chatBox.scrollTop = chatBox.scrollHeight;

        // Clear input
        questionInput.value = "";

        // Send AJAX request
        fetch("/admin/ai/ask", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
            body: JSON.stringify({ question: question })
        })
        .then(res => res.json())
        .then(data => {
            // Remove typing
            document.getElementById("typing").remove();

            // Add AI response
            const aiMessage = `
                <div class="chat-message ai-message">
                    <p><strong>AI:</strong> ${data.answer}</p>
                    <div class="chat-timestamp">Just now</div>
                </div>
            `;
            chatBox.insertAdjacentHTML("beforeend", aiMessage);
            chatBox.scrollTop = chatBox.scrollHeight;
        })
        .catch(err => {
            document.getElementById("typing").remove();
            alert("Something went wrong.");
            console.error(err);
        });
    });
});
</script>
@endsection
