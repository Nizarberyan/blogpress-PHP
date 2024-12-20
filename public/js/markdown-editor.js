function insertMarkdown(type) {
    const textarea = document.getElementById('content');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    let insertion = '';
    let newCursorPos = start;

    switch(type) {
        case 'bold':
            insertion = `**${text.substring(start, end) || 'bold text'}**`;
            break;
        case 'italic':
            insertion = `*${text.substring(start, end) || 'italic text'}*`;
            break;
        case 'link':
            insertion = `[${text.substring(start, end) || 'link text'}](url)`;
            break;
        case 'image':
            insertion = `![${text.substring(start, end) || 'image alt'}](image-url)`;
            break;
        case 'code':
            insertion = `\`${text.substring(start, end) || 'code'}\``;
            break;
        case 'heading':
            insertion = `## ${text.substring(start, end) || 'heading'}`;
            break;
        case 'list':
            insertion = `\n- ${text.substring(start, end) || 'list item'}`;
            break;
    }

    textarea.value = text.substring(0, start) + insertion + text.substring(end);
    textarea.focus();
    textarea.selectionStart = start + insertion.length;
    textarea.selectionEnd = start + insertion.length;
}

// Live preview functionality
function updatePreview() {
    const content = document.getElementById('content').value;
    const previewArea = document.getElementById('preview');
    
    if (previewArea) {
        // Make an AJAX call to get the parsed markdown
        fetch('api/parse_markdown.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ content: content })
        })
        .then(response => response.json())
        .then(data => {
            previewArea.innerHTML = data.html;
        })
        .catch(error => console.error('Error:', error));
    }
} 