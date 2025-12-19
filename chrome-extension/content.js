(() => {
    // Extract meeting ID (e.g., abc-defg-hij)
    const meetingId = location.pathname.split('/')[1];

    const attachCaptionsObserver = (region) => {
        let lastText = "";
        const observer = new MutationObserver(() => {
            const nodes = region.querySelectorAll(".ygicle.VbkSUe");
            nodes.forEach(el => {
                const text = el.innerText?.trim();
                if (!text || text === lastText) return;
                lastText = text;

                // Send to background with the meetingId
                chrome.runtime.sendMessage({
                    type: "NEW_CAPTION",
                    meetingId: meetingId,
                    data: { timestamp: new Date().toISOString(), text }
                });
            });
        });
        observer.observe(region, { childList: true, subtree: true, characterData: true });
    };

    const watcher = new MutationObserver(() => {
        const region = document.querySelector('[role="region"][aria-label="Captions"]');
        if (region) {
            attachCaptionsObserver(region);
            watcher.disconnect();
        }
    });
    watcher.observe(document.body, { childList: true, subtree: true });
})();