let transcripts = {};
let timers = {}; // To track inactivity per meeting

const INACTIVITY_DELAY = 10000; // 10 seconds of silence

// 1. Listen for new captions
chrome.runtime.onMessage.addListener((message, sender) => {
    if (message.type === "NEW_CAPTION") {
        const mId = message.meetingId;
        if (!mId) return;

        if (!transcripts[mId]) transcripts[mId] = [];
        transcripts[mId].push(message.data);

        console.log(`Caption added for ${mId}. Resetting silence timer...`);

        // --- INACTIVITY LOGIC ---
        // Clear any existing timer for this meeting
        if (timers[mId]) clearTimeout(timers[mId]);

        // Start a new timer
        timers[mId] = setTimeout(() => {
            console.log(`10s silence detected for ${mId}. Syncing data...`);
            sendToLaravel(mId);
        }, INACTIVITY_DELAY);
    }
});

// 2. Tab closure (Stay as backup)
chrome.tabs.onRemoved.addListener(async () => {
    for (const mId in transcripts) {
        if (transcripts[mId].length > 0) {
            console.log(`Tab removed. Final sync for ${mId}...`);
            await sendToLaravel(mId);
        }
    }
});

// 3. The Sending Logic (No changes needed, but added a timer cleanup)
async function sendToLaravel(mId) {
    if (!transcripts[mId] || transcripts[mId].length === 0) return;

    // Clear timer if this was triggered manually/by tab close
    if (timers[mId]) {
        clearTimeout(timers[mId]);
        delete timers[mId];
    }

    const config = await chrome.storage.sync.get(["serverHost", "serverEndpoint", "apiKey"]);
    if (!config.serverHost || !config.serverEndpoint) {
        console.error("Abort: Configuration missing in storage.");
        return;
    }

    const url = `${config.serverHost.replace(/\/$/, "")}/${config.serverEndpoint.replace(/^\/|\/$/g, "")}/${mId}`;

    try {
        const response = await fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${config.apiKey}`
            },
            body: JSON.stringify({
                transcript: transcripts[mId],
                endedAt: new Date().toISOString(),
                source: "google-meet-extension-bg"
            })
        });

        if (response.ok) {
            console.log(`Successfully synced ${transcripts[mId].length} lines for ${mId}`);
            // We clear the array so we don't send duplicates if someone speaks again
            transcripts[mId] = [];
        } else {
            console.error(`Server error: ${response.status}`);
        }
    } catch (err) {
        console.error(`Network error:`, err);
    }
}