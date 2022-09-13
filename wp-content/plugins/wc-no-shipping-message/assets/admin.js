(function() {

    const selects = document.querySelectorAll('.wnsm-replace-select');
    const textareas = document.querySelectorAll('.wnsm-replace-textarea');

    // One-time init
    {
        for (let i = 0; i < selects.length; i++) {

            const select = selects[i];
            const row = select.closest('tr');

            const screenshot = select.dataset.screenshot;
            if (screenshot) {
                const tip = row.querySelector('.woocommerce-help-tip')
                if (tip) {
                    tip.classList.add('wnsm-tip-with-screenshot');
                    tip.addEventListener("click", function (e) {
                        e.preventDefault();
                        window.open(screenshot, '_blank');
                    });
                }
            }
        }

        textareas.forEach(t => t.closest('tr').classList.add('wnsm-replace-textarea-row'));
    }

    if (!selects.length || selects.length !== textareas.length) {
        console.error('Not all expected elements found.');
        return;
    }

    const updateTextareaVisibility = function(select, textarea) {
        const row = textarea.closest('tr');
        row.style.display = (select.value === 'noop') ? 'none' : null;
    };

    for (let i = 0; i < selects.length; i++) {

        const select = selects[i];
        const textarea = textareas[i];

        updateTextareaVisibility(select, textarea);
        select.addEventListener("change", function() {
            updateTextareaVisibility(select, textarea);
        });
    }
})();